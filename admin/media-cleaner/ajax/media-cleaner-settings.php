<?php

use Ronik\Base\RbpHelper;
use Ronik\Base\RmcDataGathering;

$RmcDataGathering = new RmcDataGathering;

$rbpHelper = new RbpHelper;
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9a, Media Cleaner Settings. ', 'low', 'rbp_media_cleaner');

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9ass, Media Cleaner Settings. ', 'critical', 'rbp_media_cleaner');

if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9b, Media Cleaner Settings. Security check failed', 'low', 'rbp_media_cleaner');
	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9c, Media Cleaner Settings. is_user_logged_in', 'low', 'rbp_media_cleaner');
	return;
}

if ($_POST['file_size_selector'] == 'changed' && isset($_POST['file_size_selection'])) {
    $new_file_size_mb = floatval($_POST['file_size_selection']);
    $current_file_size_bytes = get_option('rbp_media_cleaner_file_size', 0);
    $current_file_size_mb = $current_file_size_bytes / 1048576;
    
    // Only update if the value has actually changed
    if ($new_file_size_mb != $current_file_size_mb) {
        // Save in bytes: 0 MB = 0 bytes, any other value = MB * 1048576
        $new_file_size_bytes = ($new_file_size_mb == 0) ? 0 : ($new_file_size_mb * 1048576);
        update_option('rbp_media_cleaner_file_size', $new_file_size_bytes);
        // RESET EVERYTHING
        $RmcDataGathering->rmc_reset_alldata(); 
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9d, Media Cleaner Settings. '. $new_file_size_mb . ' MB (' . $new_file_size_bytes . ' bytes)', 'low', 'rbp_media_cleaner');
        // Send sucess message!
        wp_send_json_success('Done');
    }
}

if( isset( $_POST['file_import_selection'])  ){
    if ($_POST['file_import_selection'] !== 'invalid'){
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Media Cleaner Settings. '. $_POST['file_import_selection'] , 'low', 'rbp_media_cleaner');
        update_option('rbp_media_cleaner_file_import', $_POST['file_import_selection']);
        // Send sucess message!
        wp_send_json_success('Done');
    }
}

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9f, Media Cleaner Settings. Security check failed' , 'low', 'rbp_media_cleaner');

wp_send_json_error('Security check failed', '400');
wp_die();
