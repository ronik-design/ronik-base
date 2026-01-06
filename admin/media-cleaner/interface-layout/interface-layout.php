<?php

use Ronik\Base\RmcDataGathering;

$capability = current_user_can('manage_media_cleaner') ? 'manage_media_cleaner' : 'manage_options';


add_menu_page(
    'General - Ronik Base', // page <title>Title</title>
    'Media Harmony', // link text
    $capability, // user capabilities
    'options-ronik-base-mediacleaner', // page slug
    'ronikbase_support_general', // this function prints the page content
    'dashicons-visibility', // icon (from Dashicons for example)
    6 // menu position
);

// Add Integrations page.
if ($this->beta_mode_state) {
    add_submenu_page(
        'options-ronik-base-mediacleaner', // parent page slug
        'Integrations',
        'Integrations',
        $capability,
        'options-ronik-base_integrations',
        'ronikbase_integrations_callback_media_cleaner',
        2 // menu position
    );
}

if ($this->media_cleaner_state) {

    // Add Media Cleaner page.
    add_submenu_page(
        'options-ronik-base-mediacleaner', // parent page slug
        'Media Harmony Dashboard',
        'Dashboard',
        $capability,
        'options-ronik-base_media_cleaner', //
        'ronikbase_media_cleaner_callback',
        3 // menu position
    );
    // Add Media Cleaner page.
    add_submenu_page(
        'options-ronik-base-mediacleaner', // parent page slug
        'Preserved Media',
        'Preserved Media',
        $capability,
        'options-ronik-base_preserved', //
        'ronikbase_media_cleaner_preserved_callback',
        4 // menu position
    );

    // Add Media Cleaner page.
    add_submenu_page(
        'options-ronik-base-mediacleaner', // parent page slug
        'About',
        'About',
        $capability,
        'options-ronik-base_media_cleaner_about', //
        'ronikbase_media_cleaner_about_callback',
        4 // menu position
    );


    // Add Settings page.
    if ($this->beta_mode_state) {
        add_submenu_page(
            'options-ronik-base-mediacleaner', // parent page slug
            'Ronik Base Settings',
            'Settings',
            $capability,
            'options-ronik-base_settings_media_cleaner', //
            'ronikbase_support_settings_media_cleaner',
            5 // menu position
        );
    }

        
    // Add Support page.
    add_submenu_page(
        'options-ronik-base-mediacleaner', // parent page slug
        'Support',
        'Support',
        $capability,
        'options-ronik-base_support_media_cleaner', //
        'ronikbase_support_callback_media_cleaner',
        6 // menu position
    );
}

function ronikbase_media_cleaner_callback()
{
    $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
    $rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running', '');

    if (!$progress) {
        $is_running = 'invalid';
    } else {
        // Use ternary operator to check $progress and assign the appropriate message
        $is_running = ($progress === 'COMPLETED' || $progress === 'SEMI_SUCCESS' || $progress === 'NOT_RUNNING' || $progress === 'DONE')
            ? 'invalid'
            : 'valid';
    }
    if ($rbp_media_cleaner_sync_running === 'not-running') {
        $is_running = 'invalid';
    }
?>
    <!-- The main container  -->
    <div id="ronik-base_media_cleaner" data-sync="<?= $is_running; ?>" data-plugin-name="<?= plugin_basename(plugin_dir_path(dirname(__FILE__, 3))); ?>">Media Cleaner</div>
    <canvas></canvas>



<?php
}


function ronikbase_media_cleaner_preserved_callback()
{
    $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
    $rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running', '');

    if (!$progress) {
        $is_running = 'invalid';
    } else {
        // Use ternary operator to check $progress and assign the appropriate message
        $is_running = ($progress === 'COMPLETED' || $progress === 'SEMI_SUCCESS' || $progress === 'NOT_RUNNING' || $progress === 'DONE')
            ? 'invalid'
            : 'valid';
    }
    if ($rbp_media_cleaner_sync_running === 'not-running') {
        $is_running = 'invalid';
    }
?>

    <!-- The main container  -->
    <div id="ronik-base_media_cleaner_preserved" data-sync="<?= $is_running; ?>" data-plugin-name="<?= plugin_basename(plugin_dir_path(dirname(__FILE__, 3))); ?>">Media Cleaner</div>
    <canvas></canvas>
<?php
}





function ronikbase_support_settings_media_cleaner()
{
    function isScientificNotation($value)
    {
        return preg_match('/^-?\d+(\.\d+)?[eE][-+]?\d+$/', $value);
    }
    $rbp_media_cleaner_file_size = get_option('rbp_media_cleaner_file_size') ? get_option('rbp_media_cleaner_file_size') / 1048576 : 0;
    $f_file_import = get_option('rbp_media_cleaner_file_import') ? get_option('rbp_media_cleaner_file_import') : 'off';
    
    // Get all available post types directly (not from saved option) for the UI
    $post_types = get_post_types(array(), 'names', 'and');
    // Remove default types that shouldn't be included
    $all_post_types = array_diff(
        $post_types,
        array(
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'acf-post-type',
            'acf-taxonomy',
            'acf-field-group',
            'acf-field',
            'acf-ui-options-page',
            'wp_font_family',
            'wp_font_face',
            'ame_ac_changeset'
        )
    );
    $all_post_types = array_values($all_post_types); // Re-index array
    
    // Get selected post types from saved option
    $selected_post_types = get_option('rbp_media_cleaner_post_types');
    if (!is_array($selected_post_types) || empty($selected_post_types)) {
        // Default to all if not set or empty
        $selected_post_types = $all_post_types;
    }
    
    // Encode as JSON for data attribute
    $all_post_types_json = htmlspecialchars(json_encode($all_post_types), ENT_QUOTES, 'UTF-8');
    $selected_post_types_json = htmlspecialchars(json_encode($selected_post_types), ENT_QUOTES, 'UTF-8');
    
    echo '<div id="ronik-base_settings"></div>';
    if ($_POST['media_cleaner_state'] == 'valid') {
        echo '
            <div id="ronik-base_settings-media-cleaner" data-file-backup="' . $f_file_import . '" data-file-size="' . $rbp_media_cleaner_file_size . '" data-post-types="' . $all_post_types_json . '" data-selected-post-types="' . $selected_post_types_json . '">Media Cleaner</div>
        ';
    }
}


function ronikbase_support_callback_media_cleaner()
{
    echo '
        <div id="ronik-base_support-media-cleaner"></div>
    ';
}


function ronikbase_integrations_callback_media_cleaner()
{
    $rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key') ? get_option('rbp_media_cleaner_api_key') : "";
    $rbp_optimization_api_key = get_option('rbp_optimization_api_key') ? get_option('rbp_optimization_api_key') : "";
    $rbp_media_cleaner_validation = get_option('rbp_media_cleaner_api_key_validation') ? get_option('rbp_media_cleaner_api_key_validation') : "invalid";
    $rbp_optimization_validation = get_option('rbp_optimization_api_key_validation') ? get_option('rbp_optimization_api_key_validation') : "invalid";
    // This is critical we setup some variables that will help with js & php communication.
    echo '
        <div id="ronik-base_integrations"></div>
        <div id="ronik_media_cleaner_api_key" data-api=' . $rbp_media_cleaner_api_key . '></div>
        <div id="ronik_optimization_api_key" data-api=' . $rbp_optimization_api_key . '></div>
        <div id="ronik_media_cleaner_api_key_validation" data-api-validation=' . $rbp_media_cleaner_validation . '></div>
        <div id="ronik_optimization_api_key_validation" data-api-validation=' . $rbp_optimization_validation . '></div>
    ';
}
