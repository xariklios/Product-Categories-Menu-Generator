<?php

/**
 * Plugin Name: Product Categories Menu Generator
 * Description: A plugin to generate a new WordPress navigation menu with all product categories as menu items.
 * Version: 1.0.0
 * Author: Charis Valtzis
 * Text Domain: product-categories-menu-generator
 * Domain Path: /languages/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * Icon: assets/icon.png
 */

// Load plugin text domain
function pcmg_load_textdomain()
{
    load_plugin_textdomain('product-categories-menu-generator', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'pcmg_load_textdomain');

/**
 * Enqueue scripts
 * @since    1.0.0
 */
function pcmg_enqueue_scripts()
{
    wp_enqueue_style('main-styles', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0.0', 'all');

    /* Scripts */

    $ajax_data = [
        'ajaxurl' => plugin_dir_url(__FILE__) . 'pcmg-ajax.php',
        'nonce' => wp_create_nonce('pcmg-script-nonce'),
        'error_messages' => [
            'general' => esc_html__('An error occurred. Please try again.', 'Product-Categories-Menu-Generator'),
            'invalid_response' => esc_html__('Invalid response from server. Please try again.', 'Product-Categories-Menu-Generator'),
            'no_action' => esc_html__('Please select an action to perform.', 'Product-Categories-Menu-Generator'),
            'no_selection' => esc_html__('Please select at least one menu to perform this action.', 'Product-Categories-Menu-Generator'),
            'empty_name' => esc_html__('Please enter a menu name.', 'Product-Categories-Menu-Generator'),
            'delete' => esc_html__('An error occurred while deleting. Please try again.', 'Product-Categories-Menu-Generator'),
            'update' => esc_html__('An error occurred during update. Please try again.', 'Product-Categories-Menu-Generator'),
            'generate' => esc_html__('An error occurred during menu generation. Please try again.', 'Product-Categories-Menu-Generator'),
            'bulk_delete' => esc_html__('An error occurred during bulk delete. Please try again.', 'Product-Categories-Menu-Generator')
        ],
        'confirm_messages' => [
            'delete' => esc_html__('Are you sure you want to delete this menu? This action cannot be undone.', 'Product-Categories-Menu-Generator'),
            'update' => esc_html__('Are you sure you want to update this menu? This will regenerate all menu items.', 'Product-Categories-Menu-Generator'),
            'bulk_delete' => esc_html__('Are you sure you want to delete the selected menus? This action cannot be undone.', 'Product-Categories-Menu-Generator')
        ]
    ];

    // Allow filtering of ajax data
    $ajax_data = apply_filters('pcmg_ajax_data', $ajax_data);

    wp_register_script('pcmg_script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), time(), true);
    wp_localize_script('pcmg_script', 'pcmg_ajax', $ajax_data);

    wp_enqueue_script('pcmg_script');
}


/**
 *
 * @since    1.0.0
 */
function product_categories_menu_generator_register_menu_page()
{
    add_submenu_page(
        'woocommerce',
        esc_html__('Woo Menu Generator', 'Product-Categories-Menu-Generator'),
        esc_html__('Woo Menu Generator', 'Product-Categories-Menu-Generator'),
        'manage_options',
        'product-categories-menu-generator',
        'pcmg_render_menu_page'
    );
}

/**
 *
 * @since    1.0.0
 */
function pcmg_render_menu_page()
{
    $my_menus = get_option('woo_registered_menus_from_pcmg', array());

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'Product-Categories-Menu-Generator'));
    }

    // Calculate statistics
    $total_menus = count($my_menus);
    $total_menu_items = 0;
    $total_categories = count(get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false)));

    if (!empty($my_menus)) {
        foreach ($my_menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu['id']);
            $total_menu_items += $menu_items ? count($menu_items) : 0;
        }
    }

    // Allow filtering of statistics
    $stats = apply_filters('pcmg_statistics', array(
        'total_menus' => $total_menus,
        'total_menu_items' => $total_menu_items,
        'total_categories' => $total_categories
    ));

?>
    <div class="wrap pcmg-container">
        <!-- Simplified Loading Overlay -->
        <div id="pcmg-loading-overlay" class="pcmg-loading-overlay">
            <div class="pcmg-loading-spinner"></div>
            <div class="pcmg-loading-text"><?php esc_html_e('Working Magic', 'Product-Categories-Menu-Generator'); ?></div>
            <div class="pcmg-loading-subtext"><?php esc_html_e('Creating menus in progress...', 'Product-Categories-Menu-Generator'); ?></div>
        </div>

        <div class="pcmg-header">
            <h1 class="pcmg-heading"><?php echo esc_html(get_admin_page_title()); ?> </h1>
            <div class="pcmg-docs-link">
                <a href="<?php echo esc_url(plugin_dir_url(__FILE__) . 'hooks-documentation.html'); ?>" target="_blank" class="pcmg-link">
                    <?php esc_html_e('Developer Documentation', 'Product-Categories-Menu-Generator'); ?>
                    <span class="dashicons dashicons-external"></span>
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div id="pcmg-success-message" class="pcmg-message pcmg-success-message"></div>
        <div id="pcmg-error-message" class="pcmg-message pcmg-error-message"></div>

        <?php do_action('pcmg_before_statistics'); ?>

        <!-- Stats Section -->
        <div class="pcmg-stats">
            <h2 class="pcmg-stats-heading"><?php esc_html_e('Menu Statistics', 'Product-Categories-Menu-Generator'); ?></h2>
            <div class="pcmg-stats-grid">
                <div class="pcmg-stat-card">
                    <div class="pcmg-stat-number"><?php echo esc_html($stats['total_menus']); ?></div>
                    <div class="pcmg-stat-label"><?php esc_html_e('Generated Menus', 'Product-Categories-Menu-Generator'); ?></div>
                </div>
                <div class="pcmg-stat-card">
                    <div class="pcmg-stat-number"><?php echo esc_html($stats['total_menu_items']); ?></div>
                    <div class="pcmg-stat-label"><?php esc_html_e('Menu Items', 'Product-Categories-Menu-Generator'); ?></div>
                </div>
                <div class="pcmg-stat-card">
                    <div class="pcmg-stat-number"><?php echo esc_html($stats['total_categories']); ?></div>
                    <div class="pcmg-stat-label"><?php esc_html_e('Product Categories', 'Product-Categories-Menu-Generator'); ?></div>
                </div>
                <?php do_action('pcmg_statistics_extra_cards'); ?>
            </div>
        </div>

        <?php do_action('pcmg_after_statistics'); ?>

        <!-- Get started fast - Form first for new users -->
        <?php if (empty($my_menus)): ?>
            <div class="pcmg-card">
                <h2 class="pcmg-card-heading"><?php esc_html_e('Create Your First Menu', 'Product-Categories-Menu-Generator'); ?></h2>
                <form id="menu-generator-form" class="pcmg-form">
                    <div class="pcmg-field-row">
                        <label for="menu-name" class="pcmg-label"><?php esc_html_e('Menu Name:', 'Product-Categories-Menu-Generator'); ?></label>
                        <input type="text" id="menu-name" name="menu-name" class="pcmg-input" placeholder="<?php esc_attr_e('Enter menu name', 'Product-Categories-Menu-Generator'); ?>" required>
                    </div>

                    <div class="pcmg-field-row">
                        <label for="menu-depth" class="pcmg-label"><?php esc_html_e('Menu Depth:', 'Product-Categories-Menu-Generator'); ?></label>
                        <select id="menu-depth" name="menu-depth" class="pcmg-input">
                            <option value="0"><?php esc_html_e('All levels (unlimited depth)', 'Product-Categories-Menu-Generator'); ?></option>
                            <option value="1"><?php esc_html_e('Top level categories only', 'Product-Categories-Menu-Generator'); ?></option>
                            <option value="2"><?php esc_html_e('Top level + one subcategory level', 'Product-Categories-Menu-Generator'); ?></option>
                            <option value="3"><?php esc_html_e('Top level + two subcategory levels', 'Product-Categories-Menu-Generator'); ?></option>
                        </select>
                        <p class="pcmg-field-description"><?php esc_html_e('Select how many levels of subcategories to include in the menu.', 'Product-Categories-Menu-Generator'); ?></p>
                    </div>

                    <?php
                    // Allow plugins to add custom fields
                    do_action('pcmg_form_fields');

                    // Generate options for the form using a filter
                    $form_options = apply_filters('pcmg_form_options', array(
                        'skip_empty' => array(
                            'id' => 'skip-empty',
                            'name' => 'skip-empty',
                            'label' => __('Skip empty categories', 'Product-Categories-Menu-Generator'),
                            'description' => __('Categories with no products will be excluded from the menu.', 'Product-Categories-Menu-Generator')
                        )
                    ));

                    foreach ($form_options as $option):
                    ?>
                        <div class="pcmg-field-row">
                            <label for="<?php echo esc_attr($option['id']); ?>" class="pcmg-checkbox-label">
                                <input type="checkbox" id="<?php echo esc_attr($option['id']); ?>" name="<?php echo esc_attr($option['name']); ?>" class="pcmg-checkbox">
                                <?php echo esc_html($option['label']); ?>
                            </label>
                            <?php if (!empty($option['description'])): ?>
                                <p class="pcmg-field-description"><?php echo esc_html($option['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="pcmg-submit-row">
                        <button type="submit" id="generate-menu" class="button button-primary"><?php esc_html_e('Generate Menu', 'Product-Categories-Menu-Generator'); ?></button>
                        <div id="loading-new" class="pcmg-loading"></div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Instructions Section -->
        <div class="pcmg-card pcmg-instructions">
            <h2 class="pcmg-card-heading"><?php esc_html_e('How to Use', 'Product-Categories-Menu-Generator'); ?></h2>
            <div class="pcmg-notice">
                <?php echo wp_kses_post(apply_filters('pcmg_instructions_intro', __('This plugin helps you automatically generate WordPress navigation menus from your WooCommerce product categories.', 'Product-Categories-Menu-Generator'))); ?>
            </div>
            <?php
            $instructions = apply_filters('pcmg_instructions_steps', array(
                __('Enter a name for your new menu in the form below.', 'Product-Categories-Menu-Generator'),
                __('Check "Skip empty categories" if you want to exclude categories with no products.', 'Product-Categories-Menu-Generator'),
                __('Click "Generate Menu" to create your menu.', 'Product-Categories-Menu-Generator'),
                __('Your new menu will appear in the table below and will be available in Appearance > Menus.', 'Product-Categories-Menu-Generator'),
                __('You can update or delete your menus using the action buttons.', 'Product-Categories-Menu-Generator')
            ));
            ?>
            <ol>
                <?php foreach ($instructions as $step): ?>
                    <li><?php echo wp_kses_post($step); ?></li>
                <?php endforeach; ?>
            </ol>
            <?php do_action('pcmg_after_instructions_list'); ?>
        </div>

        <?php do_action('pcmg_before_menus_table'); ?>

        <?php if (!empty($my_menus)): ?>
            <!-- Existing Menus Section -->
            <div class="pcmg-card">
                <h2 class="pcmg-card-heading"><?php esc_html_e('Your Generated Menus', 'Product-Categories-Menu-Generator'); ?></h2>

                <div class="pcmg-bulk-actions">
                    <select id="pcmg-bulk-action">
                        <option value=""><?php esc_html_e('Bulk Actions', 'Product-Categories-Menu-Generator'); ?></option>
                        <?php
                        $bulk_actions = apply_filters('pcmg_bulk_actions', array(
                            'delete' => __('Delete', 'Product-Categories-Menu-Generator')
                        ));

                        foreach ($bulk_actions as $action => $label):
                        ?>
                            <option value="<?php echo esc_attr($action); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button id="pcmg-bulk-apply" class="button pcmg-action-button"><?php esc_html_e('Apply', 'Product-Categories-Menu-Generator'); ?></button>
                    <span id="pcmg-bulk-loading" class="pcmg-loading"></span>
                    <div class="pcmg-select-all-wrap">
                        <label>
                            <input type="checkbox" id="pcmg-select-all" />
                            <span><?php esc_html_e('Select All', 'Product-Categories-Menu-Generator'); ?></span>
                        </label>
                    </div>
                </div>

                <table class="pcmg-menus-table">
                    <thead>
                        <tr>
                            <th class="pcmg-checkbox-column"><span class="screen-reader-text"><?php esc_html_e('Select', 'Product-Categories-Menu-Generator'); ?></span></th>
                            <?php
                            $table_headers = apply_filters('pcmg_table_headers', array(
                                'id' => __('ID', 'Product-Categories-Menu-Generator'),
                                'name' => __('Name', 'Product-Categories-Menu-Generator'),
                                'items' => __('Items', 'Product-Categories-Menu-Generator'),
                                'actions' => __('Actions', 'Product-Categories-Menu-Generator')
                            ));

                            foreach ($table_headers as $id => $header):
                            ?>
                                <th class="pcmg-column-<?php echo esc_attr($id); ?>"><?php echo esc_html($header); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($my_menus as $menu):
                            $menu = (object)$menu;
                            $menu_items_count = wp_get_nav_menu_items($menu->id) ? count(wp_get_nav_menu_items($menu->id)) : 0;

                            // Allow plugins to skip showing certain menus
                            if (apply_filters('pcmg_should_display_menu', true, $menu)) :
                        ?>
                                <tr>
                                    <td><input type="checkbox" class="pcmg-menu-checkbox" value="<?php echo esc_attr($menu->id); ?>" /></td>
                                    <td><?php echo esc_html($menu->id); ?></td>
                                    <td><?php echo esc_html($menu->name); ?></td>
                                    <td><?php echo esc_html($menu_items_count); ?></td>
                                    <td>
                                        <div class="pcmg-actions">
                                            <?php
                                            // Allow plugins to add custom actions
                                            do_action('pcmg_before_menu_actions', $menu);
                                            ?>
                                            <a href="javascript:;" data-menu-id="<?php echo esc_attr($menu->id); ?>" class="pcmg-action-button pcmg-update-button update-menu">
                                                <span class="dashicons dashicons-update"></span> <?php esc_html_e('Update', 'Product-Categories-Menu-Generator'); ?>
                                                <span id="loading-<?php echo esc_attr($menu->id); ?>" class="pcmg-loading"></span>
                                            </a>
                                            <a href="javascript:;" data-menu-id="<?php echo esc_attr($menu->id); ?>" class="pcmg-action-button pcmg-delete-button delete-menu">
                                                <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'Product-Categories-Menu-Generator'); ?>
                                            </a>
                                            <?php
                                            // Allow plugins to add custom actions
                                            do_action('pcmg_after_menu_actions', $menu);
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php do_action('pcmg_before_generator_form'); ?>

        <?php if (!empty($my_menus)): ?>
            <!-- Menu Generator Form -->
            <div class="pcmg-card">
                <h2 class="pcmg-card-heading"><?php esc_html_e('Generate New Menu', 'Product-Categories-Menu-Generator'); ?></h2>
                <form id="menu-generator-form" class="pcmg-form">
                    <div class="pcmg-field-row">
                        <label for="menu-name" class="pcmg-label"><?php esc_html_e('Menu Name:', 'Product-Categories-Menu-Generator'); ?></label>
                        <input type="text" id="menu-name" name="menu-name" class="pcmg-input" placeholder="<?php esc_attr_e('Enter menu name', 'Product-Categories-Menu-Generator'); ?>" required>
                    </div>

                    <div class="pcmg-field-row">
                        <label for="menu-depth" class="pcmg-label"><?php esc_html_e('Menu Depth:', 'Product-Categories-Menu-Generator'); ?></label>
                        <select id="menu-depth" name="menu-depth" class="pcmg-input">
                            <option value="0"><?php esc_html_e('All levels (unlimited depth)', 'Product-Categories-Menu-Generator'); ?></option>
                            <option value="1"><?php esc_html_e('Top level categories only', 'Product-Categories-Menu-Generator'); ?></option>
                            <option value="2"><?php esc_html_e('Top level + one subcategory level', 'Product-Categories-Menu-Generator'); ?></option>
                            <option value="3"><?php esc_html_e('Top level + two subcategory levels', 'Product-Categories-Menu-Generator'); ?></option>
                        </select>
                        <p class="pcmg-field-description"><?php esc_html_e('Select how many levels of subcategories to include in the menu.', 'Product-Categories-Menu-Generator'); ?></p>
                    </div>

                    <?php
                    // Allow plugins to add custom fields
                    do_action('pcmg_form_fields');

                    // Generate options for the form using a filter
                    $form_options = apply_filters('pcmg_form_options', array(
                        'skip_empty' => array(
                            'id' => 'skip-empty',
                            'name' => 'skip-empty',
                            'label' => __('Skip empty categories', 'Product-Categories-Menu-Generator'),
                            'description' => __('Categories with no products will be excluded from the menu.', 'Product-Categories-Menu-Generator')
                        )
                    ));

                    foreach ($form_options as $option):
                    ?>
                        <div class="pcmg-field-row">
                            <label for="<?php echo esc_attr($option['id']); ?>" class="pcmg-checkbox-label">
                                <input type="checkbox" id="<?php echo esc_attr($option['id']); ?>" name="<?php echo esc_attr($option['name']); ?>" class="pcmg-checkbox">
                                <?php echo esc_html($option['label']); ?>
                            </label>
                            <?php if (!empty($option['description'])): ?>
                                <p class="pcmg-field-description"><?php echo esc_html($option['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="pcmg-submit-row">
                        <button type="submit" id="generate-menu" class="button button-primary"><?php esc_html_e('Generate Menu', 'Product-Categories-Menu-Generator'); ?></button>
                        <div id="loading-new" class="pcmg-loading"></div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php do_action('pcmg_after_generator_form'); ?>
    </div>
<?php
}

/**
 * Sanitize plugin settings
 *
 * @param array $input The raw settings input.
 * @return array Sanitized settings.
 * @since    1.0.0
 */
function pcmg_sanitize_settings($input)
{
    $sanitized_input = array();

    // If we have settings to sanitize
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized_input[$key] = map_deep($value, 'sanitize_text_field');
            } else {
                $sanitized_input[$key] = sanitize_text_field($value);
            }
        }
    }

    return $sanitized_input;
}

/**
 *
 * @since    1.0.0
 */
function pcmg_init_settings()
{
    register_setting(
        'product_categories_menu_generator_settings',
        'product_categories_menu_generator_settings',
        array(
            'sanitize_callback' => 'pcmg_sanitize_settings',
            'default' => array()
        )
    );

    add_settings_section(
        'product_categories_menu_generator_section',
        __('Menu Settings', 'Product-Categories-Menu-Generator'),
        'pcmg_render_settings_section',
        'Product-Categories-Menu-Generator'
    );
}

/**
 *
 * @since    1.0.0
 */
function pcmg_render_settings_section()
{
    echo '<p>' . esc_html__('Enter the settings for your new menu.', 'Product-Categories-Menu-Generator') . '</p>';
}

/**
 * Ajax Generate Menu
 * @since    1.0.0
 */
function pcmg_generate_menu()
{

    if (!isset($_POST['nonce_ajax']) || !wp_verify_nonce($_POST['nonce_ajax'], 'pcmg-script-nonce')):
        wp_die('Unauthorized request. Go away!');
    endif;
    $menu_name = isset($_POST['menu_name']) ? sanitize_text_field($_POST['menu_name']) : '';
    $skip_empty = $_POST['skip_empty'] == 'true';
    $menu_depth = isset($_POST['menu_depth']) ? intval($_POST['menu_depth']) : 0; // Get menu depth parameter


    if (!empty($menu_name)):

        $registered_menus = get_option('woo_registered_menus_from_pcmg');

        if (!empty($registered_menus)):
            foreach ($registered_menus as $index => $registered_menu):
                if ($registered_menu['name'] == $menu_name):
                    add_settings_error('product_categories_menu_generator_settings', 'error', __('Menu already exists.', 'Product-Categories-Menu-Generator'), 'error');
                    wp_send_json_error('Menu already exists.', 400);
                endif;
            endforeach;
        endif;

        $menu_id = wp_create_nav_menu($menu_name);

        // add menu to list
        if (empty($registered_menus)):
            $registered_menus = [];
        endif;

        $registered_menus[] = [
            'id' => $menu_id,
            'name' => $menu_name,
            'skip_empty' => $skip_empty,
            'menu_depth' => $menu_depth
        ];

        update_option('woo_registered_menus_from_pcmg', $registered_menus);

        pcmg_add_items_to_menu($menu_id, $skip_empty, $menu_depth); // Pass the menu depth

        /* translators: %s: name of the menu that was created */
        $msg = sprintf(__('Menu "%s" has been successfully created.', 'Product-Categories-Menu-Generator'), $menu_name);
        wp_send_json_success($msg, 200);
    else:
        wp_send_json_error('Enter a menu name', 400);
    endif;
}

/**
 * @param $menu_id
 * @param false $skip
 * @param int $depth Maximum depth of subcategories to include (0 = unlimited)
 */
function pcmg_add_items_to_menu($menu_id, $skip = false, $depth = 0)
{
    // add menu items
    $categories = $skip ?
        get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        )) :
        get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
    // Loop through the categories and add them as menu items
    foreach ($categories as $category) {
        if ($category->parent == 0) {
            $menu_item_data = array(
                'menu-item-object-id' => $category->term_id,
                'menu-item-object' => 'product_cat',
                'menu-item-type' => 'taxonomy',
                'menu-item-title' => $category->name,
                'menu-item-status' => 'publish'
            );
            $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);

            // Only add subcategories if depth is 0 (unlimited) or greater than 1
            if ($depth === 0 || $depth > 1) {
                add_subcategories_to_menu($categories, $menu_id, $menu_item_id, $category->term_id, $depth, 2);
            }
        }
    }

    return true;
}


/**
 * @param $categories
 * @param $menu_id
 * @param $parent_menu_item_id
 * @param $parent_category_id
 * @param int $max_depth Maximum depth to include (0 = unlimited)
 * @param int $current_depth Current depth level (starts at 2 for first level of subcategories)
 *
 * Recursive add subcategories
 */
function add_subcategories_to_menu($categories, $menu_id, $parent_menu_item_id, $parent_category_id, $max_depth = 0, $current_depth = 2)
{
    // If we've reached the maximum depth, stop recursion
    if ($max_depth > 0 && $current_depth > $max_depth) {
        return;
    }

    foreach ($categories as $subcategory) {
        if ($subcategory->parent == $parent_category_id) {
            $submenu_item_data = array(
                'menu-item-object-id' => $subcategory->term_id,
                'menu-item-object' => 'product_cat',
                'menu-item-type' => 'taxonomy',
                'menu-item-title' => $subcategory->name,
                'menu-item-parent-id' => $parent_menu_item_id,
                'menu-item-status' => 'publish'
            );
            $submenu_item_id = wp_update_nav_menu_item($menu_id, 0, $submenu_item_data);

            // Continue recursion for next level if depth allows
            if ($max_depth === 0 || $current_depth < $max_depth) {
                add_subcategories_to_menu($categories, $menu_id, $submenu_item_id, $subcategory->term_id, $max_depth, $current_depth + 1);
            }
        }
    }
}

/**
 * Delete Menu
 */
function pcmg_delete_menu()
{
    if (!isset($_POST['nonce_ajax']) || !wp_verify_nonce($_POST['nonce_ajax'], 'pcmg-script-nonce')) {
        wp_die('Unauthorized request. Go away!');
    }

    // Initialize to false before we try to delete
    $deleted = false;
    $deleted_menu_name = '';

    if (isset($_POST['menu_id'])) {
        $menu_id = intval($_POST['menu_id']);

        // Check if the menu exists before attempting to delete
        $menu_object = wp_get_nav_menu_object($menu_id);
        if (!$menu_object) {
            wp_send_json_error('Menu does not exist or has already been deleted.', 400);
            return;
        }

        $deleted = wp_delete_nav_menu($menu_id);
        $registered_menus = get_option('woo_registered_menus_from_pcmg', array());

        foreach ($registered_menus as $index => $registered_menu) {
            if ($registered_menu['id'] == $menu_id) {
                unset($registered_menus[$index]);
                $deleted_menu_name = $registered_menu['name'];
                break;
            }
        }

        // Update the registered menus option
        update_option('woo_registered_menus_from_pcmg', array_values($registered_menus));
    } else {
        wp_send_json_error('No menu ID specified.', 400);
        return;
    }

    if ($deleted) {
        /* translators: %s: name of the menu that was deleted */
        $msg = sprintf(__('Menu "%s" has been successfully deleted.', 'Product-Categories-Menu-Generator'), $deleted_menu_name);
        wp_send_json_success($msg, 200);
    } else {
        wp_send_json_error('Failed to delete menu. It may have already been deleted or does not exist.', 400);
    }
}


function pcmg_update_menu()
{
    if (!isset($_POST['nonce_ajax']) || !wp_verify_nonce($_POST['nonce_ajax'], 'pcmg-script-nonce')) :
        wp_die('Unauthorized request. Go away!');
    endif;

    if (isset($_POST['menu_id'])):
        $menu_id = intval($_POST['menu_id']);

        // Find menu settings from the stored options
        $registered_menus = get_option('woo_registered_menus_from_pcmg', array());
        $skip_empty = false;
        $menu_depth = 0;

        // Look for this menu's settings in stored options
        $menu_settings_found = false;
        foreach ($registered_menus as $registered_menu) {
            if ($registered_menu['id'] == $menu_id) {
                // Use stored settings if available, or fall back to defaults
                $skip_empty = isset($registered_menu['skip_empty']) ? $registered_menu['skip_empty'] : false;
                $menu_depth = isset($registered_menu['menu_depth']) ? $registered_menu['menu_depth'] : 0;
                $menu_settings_found = true;
                break;
            }
        }

        // If settings weren't found, use what was passed in the POST request as fallback
        if (!$menu_settings_found) {
            $skip_empty = isset($_POST['skip_empty']) ? $_POST['skip_empty'] == 'true' : false;
            $menu_depth = isset($_POST['menu_depth']) ? intval($_POST['menu_depth']) : 0;
        }

        // Check if the menu exists before attempting to update
        $menu = wp_get_nav_menu_object($menu_id);
        if (!$menu) {
            wp_send_json_error('Menu does not exist or has already been deleted.', 400);
            return;
        }

        // Get all the menu items
        $menu_items = wp_get_nav_menu_items($menu->name);

        // Loop through the menu items and delete them
        foreach ($menu_items as $menu_item) {
            wp_delete_post($menu_item->ID, true);
        }

        pcmg_add_items_to_menu($menu_id, $skip_empty, $menu_depth);
        /* translators: %s: name of the menu that was updated */
        $msg = sprintf(__('Menu "%s" has been successfully updated.', 'Product-Categories-Menu-Generator'), $menu->name);
        wp_send_json_success($msg, 200);
    endif;

    wp_send_json_error('Update terminated unsuccessfully', 400);
}

/**
 * Bulk Delete Menus
 * @since    1.0.0
 */
function pcmg_bulk_delete_menus()
{
    if (!isset($_POST['nonce_ajax']) || !wp_verify_nonce($_POST['nonce_ajax'], 'pcmg-script-nonce')) {
        wp_die('Unauthorized request. Go away!');
    }

    if (!isset($_POST['menu_ids']) || !is_array($_POST['menu_ids'])) {
        wp_send_json_error('No menus selected for deletion.', 400);
        return;
    }

    $menu_ids = array_map('intval', $_POST['menu_ids']);
    $registered_menus = get_option('woo_registered_menus_from_pcmg', array());
    $deleted_count = 0;
    $failed_count = 0;
    $deleted_menu_names = array();

    foreach ($menu_ids as $menu_id) {
        $deleted = wp_delete_nav_menu($menu_id);

        if ($deleted) {
            $deleted_count++;

            // Remove from our registered menus
            foreach ($registered_menus as $index => $registered_menu) {
                if ($registered_menu['id'] == $menu_id) {
                    $deleted_menu_names[] = $registered_menu['name'];
                    unset($registered_menus[$index]);
                    break;
                }
            }
        } else {
            $failed_count++;
        }
    }

    // Update the registered menus option
    update_option('woo_registered_menus_from_pcmg', array_values($registered_menus));

    if ($deleted_count > 0) {
        // translators: %d: number of menus that were successfully deleted
        $message = sprintf(
            _n(
                '%d menu has been deleted successfully.',
                '%d menus have been deleted successfully.',
                $deleted_count,
                'Product-Categories-Menu-Generator'
            ),
            $deleted_count
        );

        if ($failed_count > 0) {
            // translators: %d: number of menus that failed to delete
            $message .= ' ' . sprintf(
                _n(
                    '%d menu could not be deleted.',
                    '%d menus could not be deleted.',
                    $failed_count,
                    'Product-Categories-Menu-Generator'
                ),
                $failed_count
            );
        }

        wp_send_json_success($message, 200);
    } else {
        wp_send_json_error('No menus were deleted. Please try again.', 400);
    }
}

/**
 * Run Plugin function
 */
function run()
{
    add_action('admin_menu', 'product_categories_menu_generator_register_menu_page');
    add_action('admin_enqueue_scripts', 'pcmg_enqueue_scripts');
    add_action('pcmg_ajax_delete_menu', 'pcmg_delete_menu');
    add_action('pcmg_ajax_update_menu', 'pcmg_update_menu');
    add_action('pcmg_ajax_generate_menu', 'pcmg_generate_menu');
    add_action('pcmg_ajax_bulk_delete_menus', 'pcmg_bulk_delete_menus');
}

run();
