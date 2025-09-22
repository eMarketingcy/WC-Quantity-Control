# WooCommerce Quantity Control Plugin

A modern WooCommerce plugin that allows administrators to set minimum and maximum order quantities globally or per product, with real-time frontend validation and a beautiful admin interface.

## Features

### 🎯 Core Functionality
- **Global Quantity Limits**: Set minimum and maximum quantities that apply to all products
- **Product-Specific Overrides**: Override global settings for individual products
- **Real-Time Validation**: Prevent users from entering invalid quantities with instant feedback
- **Cart Validation**: Validate quantities when items are added to cart or cart is updated
- **Checkout Protection**: Ensure invalid quantities cannot proceed to checkout

### 🎨 Modern Admin Interface
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Apple-Level Design**: Clean, modern interface with smooth animations and micro-interactions
- **AJAX-Powered**: Save settings without page reloads
- **Visual Feedback**: Loading states, success animations, and error handling
- **Accessibility**: Full keyboard navigation and screen reader support

### 🚀 User Experience
- **Auto-Correction**: Automatically adjusts invalid quantities to nearest valid value
- **Visual Indicators**: Clear error messages and validation states
- **Customizable Messages**: Configure quantity limit messages shown to customers
- **Mobile-Friendly**: Optimized for mobile shopping experience

## Installation

1. Upload the `wc-quantity-control` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **WooCommerce → Quantity Control** to configure settings

## Configuration

### Global Settings

1. Go to **WooCommerce → Quantity Control**
2. Configure global minimum and maximum quantities
3. Enable/disable quantity limit messages
4. Customize the message text (use `{min}` and `{max}` as placeholders)

### Product-Specific Settings

1. Edit any product in **Products → All Products**
2. Go to the **Inventory** tab
3. Check **"Override Global Limits"**
4. Set custom minimum and maximum quantities for that product

## Technical Specifications

### Requirements
- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+

### File Structure
```
wc-quantity-control/
├── wc-quantity-control.php          # Main plugin file
├── includes/
│   ├── class-admin.php              # Admin functionality
│   ├── class-frontend.php           # Frontend functionality
│   └── class-validator.php          # Validation logic
├── assets/
│   ├── css/
│   │   └── admin-style.css          # Admin interface styles
│   └── js/
│       ├── admin-script.js          # Admin JavaScript
│       └── frontend-script.js       # Frontend validation
├── templates/
│   └── admin-page.php               # Admin page template
└── README.md                        # Documentation
```

### Database Schema

#### Options Table (wp_options)
- `wc_qc_global_min_quantity`: Global minimum quantity (default: 1)
- `wc_qc_global_max_quantity`: Global maximum quantity (default: 999)
- `wc_qc_enable_global_limits`: Enable/disable global limits (yes/no)
- `wc_qc_show_quantity_message`: Show quantity messages (yes/no)
- `wc_qc_quantity_message`: Custom message template

#### Post Meta (wp_postmeta)
- `_wc_qc_override_global`: Override global settings (yes/no)
- `_wc_qc_min_quantity`: Product-specific minimum quantity
- `_wc_qc_max_quantity`: Product-specific maximum quantity

## Hooks and Filters

### Actions
- `woocommerce_add_to_cart_validation`: Validates quantities when adding to cart
- `woocommerce_check_cart_items`: Validates cart items
- `woocommerce_update_cart_validation`: Validates cart updates

### Filters
- `woocommerce_quantity_input_args`: Modifies quantity input arguments

## Customization

### Custom CSS
Add custom styles to your theme's CSS:

```css
/* Customize quantity message appearance */
.wc-qc-quantity-message {
    background: #your-color;
    border-color: #your-border-color;
}

/* Customize validation error styles */
.wc-qc-invalid {
    border-color: #your-error-color !important;
}
```

### Custom JavaScript
Extend functionality with custom JavaScript:

```javascript
// Custom validation logic
$(document).on('wc_qc_quantity_validated', function(e, input, isValid) {
    // Your custom logic here
});
```

## Troubleshooting

### Common Issues

**Q: Quantity limits are not working**
A: Ensure WooCommerce is active and the plugin is properly activated. Check that global limits are enabled in settings.

**Q: Product-specific limits not overriding global settings**
A: Make sure "Override Global Limits" is checked in the product's Inventory tab.

**Q: Frontend validation not working**
A: Check browser console for JavaScript errors. Ensure jQuery is loaded properly.

### Debug Mode
Add this to your `wp-config.php` for debugging:
```php
define('WC_QC_DEBUG', true);
```

## Support

For support, feature requests, or bug reports:
1. Check the documentation above
2. Search existing issues
3. Create a new support ticket with detailed information

## Changelog

### Version 1.0.0
- Initial release
- Global quantity limits
- Product-specific overrides
- Modern admin interface
- Real-time frontend validation
- Mobile-responsive design

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed with ❤️ for the WooCommerce community.