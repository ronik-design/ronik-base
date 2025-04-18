<?php 
/**
* Init Remove Unused Media .
*/
$RmcDataGathering = new RmcDataGathering;
$rbpHelper = new RbpHelper;
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 8a, Init Remove Unused Media . ', 'low', 'rbp_media_cleaner');

$RmcDataGathering = new RmcDataGathering;
$rbp_media_cleaner_media_data = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
if($rbp_media_cleaner_media_data){
	$delete_attachment = $RmcDataGathering->imageCloneSave( true,  $rbp_media_cleaner_media_data );
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 8b, Init Remove Unused Media . '. $delete_attachment , 'low', 'rbp_media_cleaner');

	if($delete_attachment){
		// Simple function that resets everything before we continue processing all the files..
		databaseScannerMedia__cleaner();
		// Throttle after cleaner.
		sleep(1);
        $RmcDataGathering->rmc_reset_alldata();
        // Send sucess message!
        wp_send_json_success('Cleaner-Done'); 
    }
    // Send sucess message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 8c, Init Remove Unused Media . Deleted' , 'low', 'rbp_media_cleaner');

    wp_send_json_success('Cleaner-Done');      
}
// Simple function that resets everything before we continue processing all the files..
databaseScannerMedia__cleaner();
// Throttle after cleaner.
sleep(1);
// Send sucess message!
wp_send_json_success('Cleaner-Done');