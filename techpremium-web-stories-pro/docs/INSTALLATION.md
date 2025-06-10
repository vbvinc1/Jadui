# Installation Guide

## System Requirements

Before installing TechPremium Web Stories Pro, ensure your system meets these requirements:

### Minimum Requirements
- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher (or MariaDB 10.1+)
- **Memory**: 128MB (256MB recommended)
- **Disk Space**: 50MB free space

### Recommended Requirements
- **WordPress**: 6.0 or higher
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Memory**: 512MB or higher
- **Disk Space**: 500MB for uploads and cache

### Required Plugins
- **Google Web Stories**: Latest version (recommended but not required)

## Installation Methods

### Method 1: WordPress Admin Upload

1. Download the plugin ZIP file
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins** → **Add New**
4. Click **Upload Plugin**
5. Choose the downloaded ZIP file
6. Click **Install Now**
7. After installation, click **Activate Plugin**

### Method 2: FTP Upload

1. Download and extract the plugin ZIP file
2. Connect to your website via FTP
3. Navigate to `/wp-content/plugins/`
4. Upload the `techpremium-web-stories-pro` folder
5. Log in to WordPress admin
6. Navigate to **Plugins** → **Installed Plugins**
7. Find "TechPremium Web Stories Pro" and click **Activate**

### Method 3: WP-CLI Installation

```bash
# Download and extract
wp plugin install techpremium-web-stories-pro.zip

# Activate the plugin
wp plugin activate techpremium-web-stories-pro
```

## Post-Installation Setup

### 1. Initial Configuration

After activation, you'll see a welcome screen with setup options:

1. Navigate to **WS Pro** in your admin menu
2. Complete the initial setup wizard
3. Configure basic settings
4. Set up your branding (logo, colors)

### 2. Database Setup

The plugin will automatically create necessary database tables:
- `wp_techpremium_stories`
- `wp_techpremium_story_templates`
- `wp_techpremium_story_analytics`

### 3. File Permissions

Ensure proper file permissions for uploads:
```bash
chmod 755 /wp-content/uploads/techpremium-stories/
```

### 4. Install Google Web Stories (Recommended)

For full functionality, install the Google Web Stories plugin:
1. Go to **Plugins** → **Add New**
2. Search for "Web Stories"
3. Install and activate the official Google plugin

## Configuration

### Basic Settings

1. **Navigate to Settings**
   - Go to **WS Pro** → **Settings**

2. **Configure API Settings**
   - Set up any required API keys
   - Configure external service integrations

3. **SEO Integration**
   - Go to **WS Pro** → **SEO Settings**
   - Enable Yoast/RankMath integration
   - Configure schema markup options

4. **Analytics Setup**
   - Go to **WS Pro** → **Analytics**
   - Connect Google Analytics (optional)
   - Enable tracking features

### Advanced Configuration

#### Upload Limits

If you need to upload large HTML files, increase PHP limits:

```php
// Add to wp-config.php
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_execution_time', 300);
```

#### Memory Limits

For complex stories, increase memory limit:

```php
// Add to wp-config.php
ini_set('memory_limit', '512M');
```

#### Debug Mode

Enable debug mode for troubleshooting:

```php
// Add to wp-config.php
define('TECHPREMIUM_WS_PRO_DEBUG', true);
```

## Verification

### Check Installation Success

1. **Admin Menu**: Verify "WS Pro" appears in admin menu
2. **Database**: Check if tables were created successfully
3. **Uploads**: Ensure upload directory exists and is writable
4. **Dependencies**: Verify all required dependencies are met

### Test Basic Functionality

1. **Create Test Story**:
   - Go to **WS Pro** → **Add New**
   - Try uploading a simple HTML file
   - Verify conversion works

2. **Check Templates**:
   - Go to **WS Pro** → **Templates**
   - Verify default templates are available

3. **Test SEO Features**:
   - Create a story and check SEO analysis
   - Verify schema markup in page source

## Troubleshooting Installation

### Common Issues

#### Plugin Doesn't Activate
- Check PHP version compatibility
- Verify file permissions
- Check for conflicting plugins
- Review error logs

#### Database Tables Not Created
- Check database permissions
- Verify MySQL version
- Check for database connection issues
- Review WordPress debug logs

#### Upload Directory Issues
- Verify directory permissions (755)
- Check available disk space
- Ensure parent directory exists
- Review server security settings

#### Missing Dependencies
- Install Google Web Stories plugin
- Update WordPress to minimum version
- Check PHP extensions (GD, cURL, etc.)

### Getting Help

If you encounter issues:

1. **Check Documentation**: Review the troubleshooting section
2. **Enable Debug Mode**: Get detailed error information
3. **Check Logs**: Review WordPress and server error logs
4. **Contact Support**: Reach out with specific error details

### Support Resources

- **Documentation**: https://docs.techpremium.me/web-stories-pro
- **Support Forum**: https://techpremium.me/support
- **Email Support**: support@techpremium.me
- **Knowledge Base**: https://kb.techpremium.me

## Next Steps

After successful installation:

1. **Read the User Guide**: Familiarize yourself with features
2. **Create Your First Story**: Follow the getting started tutorial
3. **Explore Templates**: Browse available design templates
4. **Configure SEO**: Set up optimization features
5. **Set Up Analytics**: Enable tracking and reporting

Congratulations! TechPremium Web Stories Pro is now ready to use.
