<?php
/**
 * Plugin Name: OVH Media Offload
 * Plugin URI: https://github.com/sohaibproject/ovh-media-offload
 * Description: Automatically uploads WordPress media files to OVH Object Storage (S3-compatible) and serves them from there, reducing server load and improving performance.
 * Version: 1.0.0
 * Author: Sohaib Boukraa
 * Author URI: https://sohaibboukraa.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ovh-media-offload
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * OVH Media Offload is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OVH Media Offload is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OVH Media Offload. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

require_once __DIR__ . '/aws-autoloader.php';

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Initialize plugin
add_action('plugins_loaded', 'ovh_media_offload_init');

function ovh_media_offload_init() {
    // Load plugin textdomain for translations
    load_plugin_textdomain('ovh-media-offload', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Add admin menu
add_action('admin_menu', 'ovh_media_offload_admin_menu');

function ovh_media_offload_admin_menu() {
    add_options_page(
        __('OVH Media Offload Settings', 'ovh-media-offload'),
        __('OVH Media Offload', 'ovh-media-offload'),
        'manage_options',
        'ovh-media-offload',
        'ovh_media_offload_settings_page'
    );
}

// Settings page
function ovh_media_offload_settings_page() {
    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('ovh_media_offload_settings', 'ovh_media_offload_nonce')) {
        $config_content = ovh_generate_config_content($_POST);
        echo '<div class="notice notice-success"><p>' . __('Configuration generated! Please add the following to your wp-config.php file:', 'ovh-media-offload') . '</p>';
        echo '<textarea readonly style="width:100%;height:200px;font-family:monospace;">' . esc_textarea($config_content) . '</textarea></div>';
    }

    $config_status = ovh_check_config_status();
    ?>
    <div class="wrap">
        <h1>OVH Media Offload</h1>
        
        <div class="card" style="max-width: 800px;">
            <h2>Plugin Information</h2>
            <p><strong>OVH Media Offload</strong> automatically uploads your WordPress media files to OVH Object Storage (S3-compatible) and serves them from there, reducing server load and improving performance.</p>
            
            <h3>Features:</h3>
            <ul>
                <li>✅ Automatic upload of media files to OVH Object Storage</li>
                <li>✅ Replaces local URLs with S3 URLs</li>
                <li>✅ Handles multiple image sizes</li>
                <li>✅ Automatic deletion from S3 when attachments are deleted</li>
            </ul>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Configuration Status</h2>
            <?php if ($config_status['all_configured']): ?>
                <div class="notice notice-success inline">
                    <p><strong>✅ All configurations are properly set!</strong> Your plugin is ready to use.</p>
                </div>
                
                <table class="form-table">
                    <tr>
                        <th>S3 Endpoint</th>
                        <td><?php echo esc_html(defined('OVH_S3_ENDPOINT') ? OVH_S3_ENDPOINT : 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <th>S3 Region</th>
                        <td><?php echo esc_html(defined('OVH_S3_REGION') ? OVH_S3_REGION : 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <th>S3 Bucket</th>
                        <td><?php echo esc_html(defined('OVH_S3_BUCKET') ? OVH_S3_BUCKET : 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <th>S3 Access Key</th>
                        <td><?php echo defined('OVH_S3_KEY') && OVH_S3_KEY ? '✅ Configured' : '❌ Not set'; ?></td>
                    </tr>
                    <tr>
                        <th>S3 Secret Key</th>
                        <td><?php echo defined('OVH_S3_SECRET') && OVH_S3_SECRET ? '✅ Configured' : '❌ Not set'; ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <div class="notice notice-error inline">
                    <p><strong>⚠️ Configuration Required!</strong> Please configure all S3 settings before using the plugin.</p>
                </div>
                
                <h3>Missing configurations:</h3>
                <ul>
                    <?php foreach ($config_status['missing'] as $missing): ?>
                        <li>❌ <?php echo esc_html($missing); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!$config_status['all_configured']): ?>
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('S3 Configuration', 'ovh-media-offload'); ?></h2>
            <p><?php _e('Fill in your OVH Object Storage credentials below to generate the configuration code for your wp-config.php file.', 'ovh-media-offload'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('ovh_media_offload_settings', 'ovh_media_offload_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="s3_endpoint"><?php _e('S3 Endpoint', 'ovh-media-offload'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="s3_endpoint" id="s3_endpoint" value="<?php echo esc_attr(defined('OVH_S3_ENDPOINT') ? OVH_S3_ENDPOINT : ''); ?>" class="regular-text" required />
                            <p class="description"><?php _e('Example: https://s3.gra.cloud.ovh.net', 'ovh-media-offload'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="s3_region">S3 Region</label>
                        </th>
                        <td>
                            <input type="text" name="s3_region" id="s3_region" value="<?php echo esc_attr(defined('OVH_S3_REGION') ? OVH_S3_REGION : ''); ?>" class="regular-text" required />
                            <p class="description">Example: gra</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="s3_bucket">S3 Bucket Name</label>
                        </th>
                        <td>
                            <input type="text" name="s3_bucket" id="s3_bucket" value="<?php echo esc_attr(defined('OVH_S3_BUCKET') ? OVH_S3_BUCKET : ''); ?>" class="regular-text" required />
                            <p class="description">Your OVH Object Storage bucket name</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="s3_key">Access Key ID</label>
                        </th>
                        <td>
                            <input type="text" name="s3_key" id="s3_key" value="<?php echo esc_attr(defined('OVH_S3_KEY') ? OVH_S3_KEY : ''); ?>" class="regular-text" required />
                            <p class="description">Your OVH S3 access key</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="s3_secret">Secret Access Key</label>
                        </th>
                        <td>
                            <input type="password" name="s3_secret" id="s3_secret" value="<?php echo esc_attr(defined('OVH_S3_SECRET') ? OVH_S3_SECRET : ''); ?>" class="regular-text" required />
                            <p class="description">Your OVH S3 secret key</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Generate Configuration'); ?>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>How to Configure</h2>
            <ol>
                <li>Fill in the form above with your OVH Object Storage credentials</li>
                <li>Click "Generate Configuration" to get the configuration code</li>
                <li>Copy the generated code and add it to your <strong>wp-config.php</strong> file before the line <code>/* That's all, stop editing! */</code></li>
                <li>Save the wp-config.php file</li>
                <li>Refresh this page to verify the configuration</li>
            </ol>
        </div>
    </div>
    <?php
}

// Generate configuration content for wp-config.php
function ovh_generate_config_content($form_data) {
    $content = "\n// OVH Media Offload Configuration\n";
    $content .= "define('OVH_S3_ENDPOINT', '" . addslashes($form_data['s3_endpoint']) . "');\n";
    $content .= "define('OVH_S3_REGION', '" . addslashes($form_data['s3_region']) . "');\n";
    $content .= "define('OVH_S3_BUCKET', '" . addslashes($form_data['s3_bucket']) . "');\n";
    $content .= "define('OVH_S3_KEY', '" . addslashes($form_data['s3_key']) . "');\n";
    $content .= "define('OVH_S3_SECRET', '" . addslashes($form_data['s3_secret']) . "');\n";
    
    return $content;
}

// Check configuration status
function ovh_check_config_status() {
    $required_constants = [
        'OVH_S3_ENDPOINT' => 'S3 Endpoint',
        'OVH_S3_REGION' => 'S3 Region', 
        'OVH_S3_BUCKET' => 'S3 Bucket',
        'OVH_S3_KEY' => 'S3 Access Key',
        'OVH_S3_SECRET' => 'S3 Secret Key'
    ];
    
    $missing = [];
    $all_configured = true;
    
    foreach ($required_constants as $constant => $label) {
        if (!defined($constant) || empty(constant($constant))) {
            $missing[] = $label;
            $all_configured = false;
        }
    }
    
    return [
        'all_configured' => $all_configured,
        'missing' => $missing
    ];
}

// Add admin notice if configuration is missing
add_action('admin_notices', 'ovh_media_offload_admin_notice');

function ovh_media_offload_admin_notice() {
    $config_status = ovh_check_config_status();
    
    if (!$config_status['all_configured']) {
        $screen = get_current_screen();
        if ($screen && $screen->id !== 'settings_page_ovh-media-offload') {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong><?php _e('OVH Media Offload:', 'ovh-media-offload'); ?></strong> <?php _e('Plugin configuration is incomplete.', 'ovh-media-offload'); ?> <a href="<?php echo admin_url('options-general.php?page=ovh-media-offload'); ?>"><?php _e('Configure now', 'ovh-media-offload'); ?></a></p>
            </div>
            <?php
        }
    }
}





function ovh_s3_client() {
    // Check if all required constants are defined
    if (!ovh_check_config_status()['all_configured']) {
        error_log('OVH Media Offload: Configuration incomplete. Please configure all S3 settings.');
        return false;
    }
    
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
    // Check if configuration is complete
    if (!ovh_check_config_status()['all_configured']) {
        error_log('OVH Media Offload: Skipping upload - configuration incomplete.');
        return $upload;
    }
    
    $file_path = $upload['file'];
    $upload_dir = wp_upload_dir();
    $relative_path = str_replace(trailingslashit($upload_dir['basedir']), '', $file_path);

    $s3_key =  '/uploads/' . $relative_path;

    try {
        $s3 = ovh_s3_client();
        if (!$s3) {
            return $upload;
        }
        
        $s3->putObject([
            'Bucket' => OVH_S3_BUCKET,
            'Key' => $s3_key,
            'SourceFile' => $file_path,
            'ACL' => 'public-read',
        ]);

        $upload['url'] = OVH_S3_ENDPOINT . '/' . OVH_S3_BUCKET . '/' . $s3_key;
        unlink($file_path); // Optionally delete the local file after upload
        error_log("OVH Upload Success (original): {$upload['url']}");

    } catch (Exception $e) {
        error_log('OVH Upload Error (original): ' . $e->getMessage());
    }

    return $upload;
}


add_filter('wp_generate_attachment_metadata', 'ovh_upload_attachment_sizes_to_s3', 10, 2);
function ovh_upload_attachment_sizes_to_s3($metadata, $attachment_id) {
    // Check if configuration is complete
    if (!ovh_check_config_status()['all_configured']) {
        error_log('OVH Media Offload: Skipping attachment sizes upload - configuration incomplete.');
        return $metadata;
    }
    
    $upload_dir = wp_upload_dir();
    $base_path = $upload_dir['basedir'];
    $s3 = ovh_s3_client();
    
    if (!$s3) {
        return $metadata;
    }

    $original_file = $base_path . '/' . $metadata['file'];
    // $s3_key = 'uploads/' . $metadata['file'];

   

    $s3_key = '/uploads/' . $metadata['file'];
    try {

        $s3->putObject([
            'Bucket' => OVH_S3_BUCKET,
            'Key' => $s3_key,
            'SourceFile' => $original_file,
            'ACL' => 'public-read',
        ]);
        unlink($original_file);
        error_log("OVH Upload Success (main): $s3_key");

        if (isset($metadata['sizes'])) {
            $relative_dir = dirname($metadata['file']);
            foreach ($metadata['sizes'] as $size) {
                $filename = $size['file'];
                $full_path = $base_path . '/' . $relative_dir . '/' . $filename;
                $key =  '/uploads/' . $relative_dir . '/' . $filename;

                if (file_exists($full_path)) {
                    $s3->putObject([
                        'Bucket' => OVH_S3_BUCKET,
                        'Key' => $key,
                        'SourceFile' => $full_path,
                        'ACL' => 'public-read',
                    ]);
                    error_log("OVH Upload Success (size): $key");
                    unlink($full_path); // Optionally delete the local resized file after upload
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
    // Check if configuration is complete
    if (!ovh_check_config_status()['all_configured']) {
        return $url; // Return original URL if not configured
    }
    
    $upload_dir = wp_upload_dir();
    $baseurl = $upload_dir['baseurl'];
   



    $normalized_url = preg_replace('/^http:/i', 'https:', $url);
    $normalized_baseurl = preg_replace('/^http:/i', 'https:', $baseurl);

    $relative_path = str_replace($normalized_baseurl, '', $normalized_url);
    $s3_base = OVH_S3_ENDPOINT . '/' . OVH_S3_BUCKET . '/' . '/uploads';

    return $s3_base . $relative_path;
}


    add_action('delete_attachment', 'ovh_delete_from_s3');

function ovh_delete_from_s3($attachment_id)
{
    // Check if configuration is complete
    if (!ovh_check_config_status()['all_configured']) {
        error_log('OVH Media Offload: Skipping delete - configuration incomplete.');
        return;
    }
    
    $meta = wp_get_attachment_metadata($attachment_id);
    $file = get_post_meta($attachment_id, '_wp_attached_file', true);

    if (!$file) return;

    $s3 = ovh_s3_client();
    if (!$s3) {
        return;
    }
    
    $bucket = OVH_S3_BUCKET; // Use the constant instead of hardcoded value
   
    $base_key = '/uploads/' . $file;
    $keys_to_delete = [];

    $keys_to_delete[] = ['Key' => $base_key];

    if (!empty($meta['sizes'])) {
        $file_dir = dirname($file);


        foreach ($meta['sizes'] as $size) {
            $resized_filename = $size['file'];
            $resized_key = '/uploads/' . $file_dir . '/' . $resized_filename;
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