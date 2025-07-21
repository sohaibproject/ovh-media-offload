<?php
/**
 * Uninstall script for OVH Media Offload
 * 
 * This file is executed when the plugin is deleted from WordPress.
 * It cleans up any data the plugin has stored.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options if any (currently the plugin doesn't store options in database)
// delete_option('ovh_media_offload_settings');

// Note: This plugin stores configuration in wp-config.php constants
// Those need to be manually removed by the user
