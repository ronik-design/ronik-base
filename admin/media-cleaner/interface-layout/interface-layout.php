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

function ronikbase_media_cleaner_callback(){ ?>
    <!-- The main container  -->
    <div id="ronik-base_media_cleaner">Media Cleaner</div>
    <canvas></canvas>
    <?php
}