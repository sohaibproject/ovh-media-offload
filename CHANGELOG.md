# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-XX

### Added
- Initial release of OVH Media Offload plugin
- Automatic upload of WordPress media files to OVH Object Storage
- URL replacement to serve media files from S3
- Support for multiple WordPress image sizes (thumbnails, medium, large, etc.)
- Automatic deletion of files from S3 when deleted from WordPress
- Admin dashboard for easy configuration
- Configuration status validation and checking
- Admin notices for incomplete configuration
- Internationalization support (translation ready)
- Comprehensive error logging
- Security checks and validation
- WordPress coding standards compliance

### Features
- **Easy Setup**: Simple configuration form in WordPress admin
- **Automatic Operation**: No manual intervention required after setup
- **Performance**: Reduces server storage and improves loading times
- **Reliability**: Built-in error handling and logging
- **Security**: Secure credential storage in wp-config.php
- **Developer Friendly**: Clean, well-documented code

### Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- OVH Object Storage account with S3 API credentials

### Technical Details
- Uses AWS SDK for S3 compatibility
- Implements WordPress hooks and filters
- Follows WordPress plugin development best practices
- Includes proper sanitization and validation
- Uses WordPress nonces for security
- Implements proper error handling

## [Unreleased]

### Planned Features
- Bulk upload tool for existing media
- Advanced configuration options
- Integration with WordPress multisite
- Performance analytics dashboard
- Automatic backup options
- Support for additional cloud providers
