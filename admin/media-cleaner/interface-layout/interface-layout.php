<?php
// Add Media Cleaner page.
add_submenu_page(
    'options-ronik-base', // parent page slug
    'Media Cleaner',
    'Media Cleaner',
    'manage_options',
    'options-ronik-base_media_cleaner', //
    'ronikbase_media_cleaner_callback',
    4 // menu position
);

function ronikbase_media_cleaner_callback(){ 
?> 
    <!-- The main container  --> 
    <div id="ronik-base_media_cleaner" data-plugin-name="<?= plugin_basename( plugin_dir_path(  dirname( __FILE__ , 3 ) )  ); ?>">Media Cleaner</div>
    <canvas></canvas>
    <?php
}