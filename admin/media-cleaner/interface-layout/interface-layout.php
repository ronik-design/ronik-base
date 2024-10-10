<?php
add_menu_page(
    'General - Ronik Base', // page <title>Title</title>
    'Media Harmony', // link text
    'manage_options', // user capabilities
    'options-ronik-base-mediacleaner', // page slug
    'ronikbase_support_general', // this function prints the page content
    'dashicons-visibility', // icon (from Dashicons for example)
    6 // menu position
);

// if(!$this->beta_mode_stat && false){
    // Add Settings page.
    add_submenu_page(
        'options-ronik-base-mediacleaner', // parent page slug
        'Ronik Base Settings',
        'Settings',
        'manage_options',
        'options-ronik-base_settings_media_cleaner', //
        'ronikbase_support_settings_media_cleaner',
        1 // menu position
    );
// }
// Add Support page.
add_submenu_page(
    'options-ronik-base-mediacleaner', // parent page slug
    'Support',
    'Support',
    'manage_options',
    'options-ronik-base_support_media_cleaner', //
    'ronikbase_support_callback_media_cleaner',
    3 // menu position
);


        
// Add Media Cleaner page.
add_submenu_page(
    'options-ronik-base-mediacleaner', // parent page slug
    'Media Harmony Dashboard',
    'Media Harmony Dashboard',
    'manage_options',
    'options-ronik-base_media_cleaner', //
    'ronikbase_media_cleaner_callback',
    4 // menu position
);


// Add Media Cleaner page.
add_submenu_page(
    'options-ronik-base-mediacleaner', // parent page slug
    'Preserved Media',
    'Preserved Media',
    'manage_options',
    'options-ronik-base_preserved', //
    'ronikbase_media_cleaner_preserved_callback',
    4 // menu position
);


function ronikbase_media_cleaner_callback(){ 
    $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
    $rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running', '');

    if(!$progress){
        $is_running = 'invalid';
    } else {
        // Use ternary operator to check $progress and assign the appropriate message
        $is_running = ($progress === 'COMPLETED' || $progress === 'SEMI_SUCCESS' || $progress === 'NOT_RUNNING' || $progress === 'DONE') 
            ? 'invalid' 
            : 'valid';
    }
    if($rbp_media_cleaner_sync_running === 'not-running'){
        $is_running = 'invalid';
    }
?>

    <!-- The main container  --> 
    <div id="ronik-base_media_cleaner" data-sync="<?= $is_running; ?>" data-plugin-name="<?= plugin_basename( plugin_dir_path(  dirname( __FILE__ , 3 ) )  ); ?>">Media Cleaner</div>
    <canvas></canvas>
    <?php
}


function ronikbase_media_cleaner_preserved_callback(){ 
    $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
    $rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running', '');

    if(!$progress){
        $is_running = 'invalid';
    } else {
        // Use ternary operator to check $progress and assign the appropriate message
        $is_running = ($progress === 'COMPLETED' || $progress === 'SEMI_SUCCESS' || $progress === 'NOT_RUNNING' || $progress === 'DONE') 
            ? 'invalid' 
            : 'valid';
    }
    if($rbp_media_cleaner_sync_running === 'not-running'){
        $is_running = 'invalid';
    }
?>

    <!-- The main container  --> 
    <div id="ronik-base_media_cleaner_preserved" data-sync="<?= $is_running; ?>" data-plugin-name="<?= plugin_basename( plugin_dir_path(  dirname( __FILE__ , 3 ) )  ); ?>">Media Cleaner</div>
    <canvas></canvas>
    <?php
}





function ronikbase_support_settings_media_cleaner(){
    function isScientificNotation($value) {
        return preg_match('/^-?\d+(\.\d+)?[eE][-+]?\d+$/', $value);
    }
    $rbp_media_cleaner_file_size = get_option('rbp_media_cleaner_file_size') ? get_option('rbp_media_cleaner_file_size')/1048576 : 0;
    $f_file_import = get_option( 'rbp_media_cleaner_file_import' ) ? get_option( 'rbp_media_cleaner_file_import' ) : 'off'; 
    echo '<div id="ronik-base_settings"></div>';
    if($_POST['media_cleaner_state'] == 'valid'){
        echo '
            <div id="ronik-base_settings-media-cleaner" data-file-backup="'.$f_file_import .'" data-file-size="'.$rbp_media_cleaner_file_size.'">Media Cleaner</div>
        ';
    }
}


function ronikbase_support_callback_media_cleaner(){
    echo '
        <div id="ronik-base_support-media-cleaner"></div>
    ';
}
