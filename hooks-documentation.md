# Product Categories Menu Generator - Hooks Documentation

This document provides a comprehensive list of all the hooks (actions and filters) available in the Product Categories Menu Generator plugin, organized by category.

## Action Hooks

Action hooks allow you to execute custom code at specific points during the plugin's execution.

### AJAX Actions

These actions are triggered when AJAX requests are processed:

| Hook                          | Description                                             | Parameters | File Location |
| ----------------------------- | ------------------------------------------------------- | ---------- | ------------- |
| `pcmg_ajax_delete_menu`       | Triggered when a menu deletion request is received      | None       | pcmg-ajax.php  |
| `pcmg_ajax_update_menu`       | Triggered when a menu update request is received        | None       | pcmg-ajax.php  |
| `pcmg_ajax_generate_menu`     | Triggered when a menu generation request is received    | None       | pcmg-ajax.php  |
| `pcmg_ajax_bulk_delete_menus` | Triggered when a bulk menu deletion request is received | None       | pcmg-ajax.php  |

### UI Hooks

These actions allow you to add custom content to the admin interface:

| Hook                           | Description                             | Parameters       | File Location                         |
| ------------------------------ | --------------------------------------- | ---------------- | ------------------------------------- |
| `pcmg_before_statistics`       | Executes before the Statistics section  | None             | product-categories-menu-generator.php |
| `pcmg_after_statistics`        | Executes after the Statistics section   | None             | product-categories-menu-generator.php |
| `pcmg_statistics_extra_cards`  | Allows adding extra statistic cards     | None             | product-categories-menu-generator.php |
| `pcmg_before_menus_table`      | Executes before the menus table         | None             | product-categories-menu-generator.php |
| `pcmg_after_instructions_list` | Executes after the instructions list    | None             | product-categories-menu-generator.php |
| `pcmg_before_generator_form`   | Executes before the menu generator form | None             | product-categories-menu-generator.php |
| `pcmg_after_generator_form`    | Executes after the menu generator form  | None             | product-categories-menu-generator.php |
| `pcmg_form_fields`             | Allows adding custom fields to the form | None             | product-categories-menu-generator.php |
| `pcmg_before_menu_actions`     | Executes before menu actions buttons    | `$menu` (object) | product-categories-menu-generator.php |
| `pcmg_after_menu_actions`      | Executes after menu actions buttons     | `$menu` (object) | product-categories-menu-generator.php |

## Filter Hooks

Filter hooks allow you to modify data during the plugin's execution.

### Data Filters

| Hook                       | Description                                   | Parameters                          | Default                | File Location                         |
| -------------------------- | --------------------------------------------- | ----------------------------------- | ---------------------- | ------------------------------------- |
| `pcmg_ajax_data`           | Modifies the AJAX data passed to JavaScript   | `$ajax_data` (array)                | Array of AJAX settings | product-categories-menu-generator.php |
| `pcmg_statistics`          | Modifies the statistics displayed in the UI   | `$stats` (array)                    | Array of statistics    | product-categories-menu-generator.php |
| `pcmg_instructions_intro`  | Modifies the instructions intro text          | `$text` (string)                    | Default intro text     | product-categories-menu-generator.php |
| `pcmg_instructions_steps`  | Modifies the instructions steps               | `$steps` (array)                    | Array of instructions  | product-categories-menu-generator.php |
| `pcmg_form_options`        | Modifies the form options                     | `$options` (array)                  | Array of form options  | product-categories-menu-generator.php |
| `pcmg_bulk_actions`        | Modifies the available bulk actions           | `$actions` (array)                  | Array of bulk actions  | product-categories-menu-generator.php |
| `pcmg_table_headers`       | Modifies the table headers                    | `$headers` (array)                  | Array of table headers | product-categories-menu-generator.php |
| `pcmg_should_display_menu` | Determines whether a menu should be displayed | `$display` (bool), `$menu` (object) | true                   | product-categories-menu-generator.php |

## Usage Examples

### Adding a Custom Action Button

```php
// Add a custom action button to each menu
function my_custom_menu_action($menu) {
    ?>
    <a href="javascript:;" data-menu-id="<?php echo esc_attr($menu->id); ?>" class="pcmg-action-button my-custom-action">
        <?php _e('Custom Action', 'my-plugin'); ?>
    </a>
    <?php
}
add_action('pcmg_after_menu_actions', 'my_custom_menu_action');
```

### Adding Custom Statistics

```php
// Add custom statistics to the statistics section
function my_custom_statistics($stats) {
    $stats['custom_stat'] = 42; // Add your custom statistic
    return $stats;
}
add_filter('pcmg_statistics', 'my_custom_statistics');

// Display the custom statistic
function my_custom_statistic_card() {
    $stats = apply_filters('pcmg_statistics', array());
    ?>
    <div class="pcmg-stat-card">
        <div class="pcmg-stat-number"><?php echo esc_html($stats['custom_stat']); ?></div>
        <div class="pcmg-stat-label"><?php _e('Custom Stat', 'my-plugin'); ?></div>
    </div>
    <?php
}
add_action('pcmg_statistics_extra_cards', 'my_custom_statistic_card');
```

### Adding Custom Form Fields

```php
// Add a custom field to the menu generator form
function my_custom_form_field() {
    ?>
    <div class="pcmg-field-row">
        <label for="my-custom-field" class="pcmg-label">
            <?php _e('Custom Field:', 'my-plugin'); ?>
        </label>
        <div class="pcmg-field">
            <input type="text" id="my-custom-field" name="my-custom-field" class="pcmg-input">
        </div>
    </div>
    <?php
}
add_action('pcmg_form_fields', 'my_custom_form_field');
```

### Filtering Form Options

```php
// Add a custom option to the form
function my_custom_form_option($options) {
    $options['my_option'] = array(
        'id' => 'my-option',
        'name' => 'my-option',
        'label' => __('My custom option', 'my-plugin'),
        'description' => __('This is a custom option added by my plugin.', 'my-plugin')
    );
    return $options;
}
add_filter('pcmg_form_options', 'my_custom_form_option');
```

### Modifying AJAX Data

```php
// Add custom data to the AJAX configuration
function my_custom_ajax_data($ajax_data) {
    $ajax_data['custom_data'] = array(
        'key1' => 'value1',
        'key2' => 'value2'
    );
    return $ajax_data;
}
add_filter('pcmg_ajax_data', 'my_custom_ajax_data');
```

### JavaScript Integration with Custom Form Data

```javascript
// Define a custom form data function to extend the form data
function pcmg_custom_form_data($form) {
  return {
    custom_field: $("#my-custom-field").val(),
  };
}
```
