<?php
/**
 * Plugin Name: OVH Media Offload
 * Description: Uploads WordPress media to OVH Object Storage (S3-compatible) and replaces URLs with S3 links.
 */

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

require_once __DIR__ . '/aws-autoloader.php';





function ovh_s3_client() {
    return new S3Client([
        'version' => 'latest',
        'region' => OVH_S3_REGION,
        'endpoint' => OVH_S3_ENDPOINT,
        'use_path_style_endpoint' => true,
        'credentials' => [
            'key'    => OVH_S3_KEY,
            'secret' => OVH_S3_SECRET,
        ],
    ]);
}

add_filter('wp_handle_upload', 'ovh_upload_original_to_s3');
function ovh_upload_original_to_s3($upload) {
    $file_path = $upload['file'];
    $upload_dir = wp_upload_dir();
    $relative_path = str_replace(trailingslashit($upload_dir['basedir']), '', $file_path);
    $company_code = get_option('company_code', ''); 

    $s3_key = $company_code . '/uploads/' . $relative_path;

    try {
        $s3 = ovh_s3_client();
        $s3->putObject([
            'Bucket' => OVH_S3_BUCKET,
            'Key' => $s3_key,
            'SourceFile' => $file_path,
            'ACL' => 'public-read',
        ]);

        $upload['url'] = OVH_S3_ENDPOINT . '/' . OVH_S3_BUCKET . '/' . $s3_key;
        error_log("OVH Upload Success (original): {$upload['url']}");

    } catch (Exception $e) {
        error_log('OVH Upload Error (original): ' . $e->getMessage());
    }

    return $upload;
}


add_filter('wp_generate_attachment_metadata', 'ovh_upload_attachment_sizes_to_s3', 10, 2);
function ovh_upload_attachment_sizes_to_s3($metadata, $attachment_id) {
    $upload_dir = wp_upload_dir();
    $base_path = $upload_dir['basedir'];
    $s3 = ovh_s3_client();

    $original_file = $base_path . '/' . $metadata['file'];
    // $s3_key = 'uploads/' . $metadata['file'];

    $company_code = get_option('company_code', ''); 

    $s3_key = $company_code . '/uploads/' . $metadata['file'];
    try {

        $s3->putObject([
            'Bucket' => OVH_S3_BUCKET,
            'Key' => $s3_key,
            'SourceFile' => $original_file,
            'ACL' => 'public-read',
        ]);
        error_log("OVH Upload Success (main): $s3_key");

        if (isset($metadata['sizes'])) {
            $relative_dir = dirname($metadata['file']);
            foreach ($metadata['sizes'] as $size) {
                $filename = $size['file'];
                $full_path = $base_path . '/' . $relative_dir . '/' . $filename;
                $key = $company_code . '/uploads/' . $relative_dir . '/' . $filename;

                if (file_exists($full_path)) {
                    $s3->putObject([
                        'Bucket' => OVH_S3_BUCKET,
                        'Key' => $key,
                        'SourceFile' => $full_path,
                        'ACL' => 'public-read',
                    ]);
                    error_log("OVH Upload Success (size): $key");
                }
            }
        }

    } catch (Exception $e) {
        error_log('OVH Upload Error (sizes): ' . $e->getMessage());
    }

    return $metadata;
}


add_filter('wp_get_attachment_url', 'ovh_filter_attachment_url');
function ovh_filter_attachment_url($url) {
    $upload_dir = wp_upload_dir();
    $baseurl = $upload_dir['baseurl'];
    $company_code = get_option('company_code', ''); 



    $normalized_url = preg_replace('/^http:/i', 'https:', $url);
    $normalized_baseurl = preg_replace('/^http:/i', 'https:', $baseurl);

    $relative_path = str_replace($normalized_baseurl, '', $normalized_url);
    $s3_base = OVH_S3_ENDPOINT . '/' . OVH_S3_BUCKET . '/' . $company_code . '/uploads';

    return $s3_base . $relative_path;
}


    add_action('delete_attachment', 'ovh_delete_from_s3');

function ovh_delete_from_s3($attachment_id)
{
    $meta = wp_get_attachment_metadata($attachment_id);
    $file = get_post_meta($attachment_id, '_wp_attached_file', true);

    if (!$file) return;

    $s3 = ovh_s3_client();
    $bucket = 'ecombridge';
    $company_code = get_option('company_code', '');
    $base_key = $company_code . '/uploads/' . $file;
    $keys_to_delete = [];

    $keys_to_delete[] = ['Key' => $base_key];

    if (!empty($meta['sizes'])) {
        $file_dir = dirname($file);


        foreach ($meta['sizes'] as $size) {
            $resized_filename = $size['file'];
            $resized_key = $company_code . '/uploads/' . $file_dir . '/' . $resized_filename;
            $keys_to_delete[] = ['Key' => $resized_key];
        }
    }

    try {
        $s3->deleteObjects([
            'Bucket'  => $bucket,
            'Delete'  => ['Objects' => $keys_to_delete],
        ]);


        error_log("OVH S3 Delete Success: " );

         
        

    } catch (AwsException $e) {
        error_log("OVH S3 Delete Failed: " . $e->getMessage());
    }

}