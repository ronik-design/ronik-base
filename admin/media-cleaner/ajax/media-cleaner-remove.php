<?php 
/**
* Init Remove Unused Media .
*/

$RmcDataGathering = new RmcDataGathering;
$rbp_media_cleaner_media_data = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
error_log(print_r('Init Remove Unused Media .', true));
error_log(print_r('Deleted', true));
if($rbp_media_cleaner_media_data){
	$delete_attachment = $RmcDataGathering->imageCloneSave( true,  $rbp_media_cleaner_media_data );
	error_log(print_r($delete_attachment, true));
	if($delete_attachment){
		// Simple function that resets everything before we continue processing all the files..
		databaseScannerMedia__cleaner();
		// Throttle after cleaner.
		sleep(1);

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


        // Send sucess message!
        wp_send_json_success('Cleaner-Done'); 
    }
    // Send sucess message!
    error_log(print_r('Deleted', true));
    wp_send_json_success('Cleaner-Done');      
}
// Simple function that resets everything before we continue processing all the files..
databaseScannerMedia__cleaner();
// Throttle after cleaner.
sleep(1);
// Send sucess message!
wp_send_json_success('Cleaner-Done');