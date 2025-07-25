=== OVH Media Offload ===
Contributors: Sohaib Boukraa
Donate link: https://paypal.me/sohaibboukraa
Tags: ovh, object storage, s3, media, offload, cloud storage, cdn, performance
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically uploads WordPress media files to OVH Object Storage (S3-compatible) and serves them from there, reducing server load and improving performance.

== Description ==

**OVH Media Offload** is a powerful WordPress plugin that seamlessly integrates your WordPress media library with OVH Object Storage, providing a robust cloud storage solution for your website's media files.

= Key Features =

* **Automatic Upload**: All media files are automatically uploaded to OVH Object Storage upon upload
* **URL Replacement**: Media URLs are automatically replaced with S3 URLs for faster loading
* **Multiple Image Sizes**: Handles all WordPress-generated image sizes (thumbnails, medium, large, etc.)
* **Automatic Deletion**: Files are automatically deleted from S3 when removed from WordPress
* **Easy Configuration**: Simple admin dashboard for configuration
* **Configuration Validation**: Built-in checks ensure proper setup before activation
* **Performance Boost**: Reduces server storage usage and improves loading times
* **Cost Effective**: Leverage OVH's competitive Object Storage pricing

= Why Use OVH Object Storage? =

* **High Performance**: Fast global content delivery
* **Cost Effective**: Competitive pricing for cloud storage
* **Reliable**: 99.9% availability SLA
* **S3 Compatible**: Uses standard S3 API for seamless integration
* **Scalable**: Automatically scales with your needs

= How It Works =

1. **Install & Activate**: Install the plugin and activate it
2. **Configure**: Enter your OVH Object Storage credentials in the settings
3. **Automatic Operation**: All new media uploads are automatically stored in OVH Object Storage
4. **Serve from Cloud**: Media files are served directly from OVH's fast network

= Configuration Requirements =

* OVH Object Storage account
* S3 API credentials (Access Key & Secret Key)
* WordPress 5.0 or higher
* PHP 7.4 or higher

= Getting Started =

1. Create an OVH Object Storage container
2. Generate S3 API credentials in your OVH control panel
3. Install and activate this plugin
4. Go to Settings → OVH Media Offload
5. Enter your credentials and save
6. Start uploading media - it's automatically stored in OVH Object Storage!

= Developer Friendly =

The plugin is built with developers in mind:
* Clean, well-documented code
* WordPress coding standards compliant
* Secure credential handling via wp-config.php
* Comprehensive error logging
* Extensible architecture

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "OVH Media Offload"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Extract and upload the `ovh-media-offload` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin panel

= Configuration =

1. Go to Settings → OVH Media Offload
2. Fill in your OVH Object Storage credentials:
   * S3 Endpoint (e.g., https://s3.gra.cloud.ovh.net)
   * S3 Region (e.g., gra)
   * Bucket Name
   * Access Key ID
   * Secret Access Key
3. Click "Generate Configuration"
4. Copy the generated code to your wp-config.php file
5. Save and refresh the settings page to verify configuration

== Frequently Asked Questions ==

= What is OVH Object Storage? =

OVH Object Storage is a scalable cloud storage service compatible with Amazon S3 API. It provides fast, reliable, and cost-effective storage for your files.

= Do I need an OVH account? =

Yes, you need an OVH account and an Object Storage container. You can sign up at ovh.com.

= Will this work with existing media files? =

The plugin works with new uploads. For existing files, you'll need to manually migrate them or use additional tools.

= What happens to local files? =

By default, files are kept locally and also stored in OVH Object Storage. You can modify the plugin to delete local files if desired.

= Is it compatible with CDN services? =

Yes, OVH Object Storage can work as a CDN, serving your media files from multiple global locations.

= What if I deactivate the plugin? =

Media URLs will revert to local URLs. Files remain in your OVH Object Storage unless manually deleted.

= How much does OVH Object Storage cost? =

OVH offers competitive pricing for Object Storage. Check their website for current pricing details.

= Is my data secure? =

Yes, OVH Object Storage provides enterprise-grade security with encryption and access controls.

== Screenshots ==

1. Plugin settings page showing configuration status
2. Easy configuration form for OVH credentials
3. Generated wp-config.php code for easy setup
4. Configuration status dashboard with validation

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic media upload to OVH Object Storage
* URL replacement for S3 delivery
* Support for multiple image sizes
* Automatic deletion from S3
* Admin configuration dashboard
* Configuration validation and status checking
* Internationalization support

== Upgrade Notice ==

= 1.0.0 =
Initial release of OVH Media Offload. Configure your OVH Object Storage credentials to get started!

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data. All configuration is stored locally in your WordPress database and wp-config.php file.

== Support ==

For support, please visit the plugin's support forum on WordPress.org or create an issue on GitHub.

== Contributing ==

Contributions are welcome! Please visit our GitHub repository to contribute code, report issues, or suggest features.
