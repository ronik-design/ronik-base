<?php 
if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
	return;
}

if ($_POST['file_size_selector'] == 'changed'){
    if(isset( $_POST['file_size_selection']) && $_POST['file_size_selection'] ){

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

        error_log(print_r( $_POST['file_size_selection'], true));

        // Send sucess message!
        wp_send_json_success('Done');
    } else {
        wp_send_json_error('Security check failed', '400');
        wp_die();
    }
}