<?php 
/**
* Init Remove Unused Media .
*/

// $rbp_media_cleaner_counter = get_option('rbp_media_cleaner_counter');

$RmcDataGathering = new RmcDataGathering;

// $rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');
$rbp_media_cleaner_media_data = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );


error_log(print_r('Deleted', true));


if($rbp_media_cleaner_media_data){
	$delete_attachment = $RmcDataGathering->imageCloneSave( true,  $rbp_media_cleaner_media_data );

	error_log(print_r($delete_attachment, true));

	if($delete_attachment){
		// Simple function that resets everything before we continue processing all the files..
		databaseScannerMedia__cleaner();
		// Throttle after cleaner.
		sleep(1);
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