<?php
add_menu_page(
    'General - Ronik Base', // page <title>Title</title>
    'Ronik Media Cleaner', // link text
    'manage_options', // user capabilities
    'options-ronik-base-mediacleaner', // page slug
    'ronikbase_support_general', // this function prints the page content
    'dashicons-visibility', // icon (from Dashicons for example)
    6 // menu position
);


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
    'Media Cleaner',
    'Media Cleaner',
    'manage_options',
    'options-ronik-base_media_cleaner', //
    'ronikbase_media_cleaner_callback',
    4 // menu position
);

function ronikbase_media_cleaner_callback(){ ?> 
    <!-- The main container  --> 
    <div id="ronik-base_media_cleaner" data-plugin-name="<?= plugin_basename( plugin_dir_path(  dirname( __FILE__ , 3 ) )  ); ?>">Media Cleaner</div>
    <canvas></canvas>
    <?php
}


function ronikbase_support_settings_media_cleaner(){
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
