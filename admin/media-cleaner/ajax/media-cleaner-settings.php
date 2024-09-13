<?php 
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

if ($_POST['file_size_selector'] == 'changed' && (get_option( 'rbp_media_cleaner_file_size' )/1048576) != ($_POST['file_size_selection']) ){
    if( isset( $_POST['file_size_selection'])  ){
        update_option('rbp_media_cleaner_file_size', $_POST['file_size_selection']*1048576);
        // RESET EVERYTHING
        update_option('rbp_media_cleaner_sync_running', 'not-running');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');
        delete_transient('rmc_media_cleaner_media_data_collectors_posts_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_post_auditor_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array');
        delete_option('rbp_media_cleaner_increment');
        delete_option('rbp_media_cleaner_counter');
        delete_option('rbp_media_cleaner_media_data');
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9d, Media Cleaner Settings. '. $_POST['file_size_selection'] , 'low', 'rbp_media_cleaner');

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
