# TechPremium Web Stories Pro

A comprehensive WordPress plugin for creating, managing, and optimizing Google Web Stories with advanced features and seamless integration.

## Features

### Core Features
- **HTML Upload & Conversion**: Upload existing HTML/AMP files and convert them to Google Web Stories format
- **Google Web Stories Integration**: Full compatibility with the official Google Web Stories plugin
- **Advanced Story Builder**: Drag-and-drop interface for creating stories from scratch
- **Template Library**: Pre-designed templates with premium options
- **Bulk Management**: Handle multiple stories with bulk actions
- **Export Options**: Export stories in various formats (HTML, AMP, JSON)

### SEO & Optimization
- **SEO Integration**: Compatible with Yoast SEO and RankMath
- **Schema Markup**: Auto-generated structured data
- **Open Graph & Twitter Cards**: Social media optimization
- **Google Discover Optimization**: Enhanced visibility in Google feeds
- **Performance Optimization**: Image compression and lazy loading

### Analytics & Insights
- **Built-in Analytics**: Track views, engagement, and performance
- **A/B Testing**: Test different versions of your stories
- **Custom Reports**: Detailed analytics dashboard
- **Google Analytics Integration**: Enhanced tracking capabilities

### Advanced Features
- **Brand Management**: Custom logos, colors, and fonts
- **Animation Controls**: Advanced animation and transition options
- **Multi-format Support**: Support for various content types
- **API Integration**: RESTful API for external integrations
- **Shortcode Support**: Easy embedding with shortcodes

## Installation

1. Download the plugin files
2. Upload to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin
4. Configure settings under **WS Pro** in your admin menu

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Google Web Stories plugin (recommended)
- MySQL 5.6+ or MariaDB 10.1+

## Usage

### Creating Your First Story

1. Navigate to **WS Pro** → **Add New**
2. Choose your creation method:
   - Upload HTML file
   - Use a template
   - Start with the builder
3. Customize your story
4. Convert to Web Stories format
5. Publish and share

### Uploading HTML Stories

1. Go to **Add New Story**
2. Select "Upload HTML File"
3. Drag and drop your HTML file
4. Configure import options
5. Click "Upload & Convert Story"

### Using Templates

1. Visit **WS Pro** → **Templates**
2. Browse available templates
3. Click "Use Template" on your preferred design
4. Customize content and styling
5. Save and publish

### SEO Optimization

1. Navigate to **WS Pro** → **SEO Settings**
2. Enable desired integrations (Yoast, RankMath)
3. Configure schema markup options
4. Set up social media optimization
5. Use the SEO analyzer for story optimization

## Shortcodes

### Single Story Embed
```php
[techpremium_story id="123" width="360" height="640"]
```

### Story Grid
```php
[techpremium_story_grid columns="3" limit="6" category="technology"]
```

### Story Carousel
```php
[techpremium_story_carousel limit="5" autoplay="true" arrows="true"]
```

## API Endpoints

The plugin provides RESTful API endpoints for external integrations:

- `GET /wp-json/techpremium-ws-pro/v1/stories` - Get all stories
- `POST /wp-json/techpremium-ws-pro/v1/stories` - Create new story
- `GET /wp-json/techpremium-ws-pro/v1/stories/{id}` - Get specific story
- `PUT /wp-json/techpremium-ws-pro/v1/stories/{id}` - Update story
- `DELETE /wp-json/techpremium-ws-pro/v1/stories/{id}` - Delete story

## Hooks & Filters

### Actions
- `techpremium_ws_pro_story_created` - Fired when a story is created
- `techpremium_ws_pro_story_converted` - Fired when HTML is converted
- `techpremium_ws_pro_template_used` - Fired when a template is used

### Filters
- `techpremium_ws_pro_story_schema` - Modify story schema markup
- `techpremium_ws_pro_upload_path` - Customize upload directory
- `techpremium_ws_pro_supported_formats` - Add supported file formats

## Customization

### Adding Custom Templates

```php
function add_custom_template($templates) {
    $templates[] = array(
        'name' => 'My Custom Template',
        'description' => 'A custom template for my needs',
        'category' => 'custom',
        'template_data' => array(/* template structure */),
        'is_premium' => false
    );
    return $templates;
}
add_filter('techpremium_ws_pro_templates', 'add_custom_template');
```

### Custom Analytics Tracking

```javascript
jQuery(document).on('techpremium_story_view', function(e, storyId) {
    // Custom tracking code
    gtag('event', 'story_view', {
        'story_id': storyId
    });
});
```

## Troubleshooting

### Common Issues

**HTML Upload Fails**
- Check file size (max 10MB)
- Ensure file is valid HTML
- Verify server permissions

**Stories Don't Display**
- Ensure Google Web Stories plugin is active
- Check story status (should be "published")
- Verify shortcode parameters

**SEO Features Not Working**
- Confirm SEO plugin compatibility
- Check integration settings
- Verify schema markup in page source

### Debug Mode

Enable debug mode by adding to wp-config.php:
```php
define('TECHPREMIUM_WS_PRO_DEBUG', true);
```

## Support

For support and documentation:
- Visit: https://techpremium.me/support
- Email: support@techpremium.me
- Documentation: https://docs.techpremium.me/web-stories-pro

## Changelog

### 1.0.0
- Initial release
- HTML upload and conversion
- Template system
- SEO integration
- Analytics dashboard
- REST API
- Shortcode support

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by TechPremium
https://techpremium.me

Built with ❤️ for the WordPress community.
