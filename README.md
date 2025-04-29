# Product Categories Menu Generator

A WordPress plugin that automatically generates navigation menus from your WooCommerce product categories.

![Product Categories Menu Generator](assets/images/banner.png)

## Description

Product Categories Menu Generator allows you to quickly create WordPress navigation menus that include all your WooCommerce product categories in a hierarchical structure. The plugin provides an easy-to-use interface in the WordPress admin area for creating, updating, and managing category-based menus.

### Key Features

- **One-Click Menu Creation**: Generate complete navigation menus from your product categories with a single click
- **Category Depth Control**: Choose how many levels of subcategories to include in your menus
- **Empty Category Filtering**: Option to skip categories that don't contain any products
- **Bulk Actions**: Manage multiple menus at once with bulk delete functionality
- **AJAX-Powered**: Fast, smooth user experience with no page reloads
- **Developer Friendly**: Well-documented hooks allow for customization and extension

## Installation

1. Upload the `product-categories-menu-generator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce → Product Categories Menu to access the menu generator

## Usage

### Creating a Menu

1. Navigate to WooCommerce → Product Categories Menu in your WordPress admin
2. Enter a name for your new menu
3. Choose your preferred options:
   - **Menu Depth**: Controls how many levels of subcategories to include
   - **Skip Empty Categories**: When checked, categories with no products will be excluded
4. Click "Generate Menu"

### Managing Menus

- **Update Menu**: Refreshes the menu with the latest category structure while preserving the menu ID
- **Delete Menu**: Removes the menu and all its items
- **Bulk Delete**: Select multiple menus and delete them at once

### Using Your Menu

After creating a menu, you can assign it to a menu location through:
1. Appearance → Menus
2. Select your generated menu from the dropdown
3. Choose a display location
4. Save changes

## FAQ

**Q: Does this plugin work with any WooCommerce theme?**  
A: Yes, the plugin generates standard WordPress navigation menus that will work with any theme that supports WordPress menus.

**Q: Can I customize the menu after it's generated?**  
A: Yes, you can edit the generated menu through the standard WordPress menu editor (Appearance → Menus).

**Q: Will the menu update automatically when I add new categories?**  
A: No, but you can easily update the menu by clicking the "Update" button for that menu in the plugin interface.

**Q: Can I include other items in my category menu?**  
A: Yes, after generating the menu, you can add additional items through the WordPress menu editor.

## Screenshots

1. Menu Generator Interface
2. Generated Menu Example
3. Bulk Actions

## Changelog

### 1.0.0
* Initial release

## Support

If you encounter any issues or have feature suggestions, please visit the [support forum](https://wordpress.org/support/plugin/product-categories-menu-generator/) or [GitHub repository](https://github.com/xariklios/Product-Categories-Menu-Generator).

## Credits

Developed by [Charis Valtzis](https://vforvaltzis.dev) 