<?php
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
if (!$this->beta_mode_state) {
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
    echo '<div id="ronik-base_settings"></div>';
    if ($_POST['media_cleaner_state'] == 'valid') {
        echo '
            <div id="ronik-base_settings-media-cleaner" data-file-backup="' . $f_file_import . '" data-file-size="' . $rbp_media_cleaner_file_size . '">Media Cleaner</div>
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
