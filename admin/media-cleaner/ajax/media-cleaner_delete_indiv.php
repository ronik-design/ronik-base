<?php 
/**
* Init Remove individual Media .
*/

$rbpHelper = new RbpHelper;
$RmcDataGathering = new RmcDataGathering;

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 3a, Init Remove individual Media imageId: '.$_POST['imageId'], 'low', 'rbp_media_cleaner');

if(isset($_POST['imageId']) && $_POST['imageId']){
    $rbp_media_cleaner_data_array = get_option('rbp_media_cleaner_media_data'); 
    if( !$rbp_media_cleaner_data_array ){
        $rbp_media_cleaner_data_array = array();
    } else {
        $rbp_media_cleaner_data_array = array_values(array_filter($rbp_media_cleaner_data_array));
    }
    // Pretty much we remove the image id from the data array.
    if (($key = array_search($_POST['imageId'], $rbp_media_cleaner_data_array)) !== false) {
        unset($rbp_media_cleaner_data_array[$key]);
        $rbp_media_cleaner_data_array = array_values($rbp_media_cleaner_data_array);
    }
    update_option('rbp_media_cleaner_media_data', $rbp_media_cleaner_data_array);
    $rbp_data_id = $_POST['imageId'];
    $delete_attachment = $RmcDataGathering->imageCloneSave( false,  $_POST['imageId']);
    if($delete_attachment){
		// Simple function that resets everything before we continue processing all the files..
		databaseScannerMedia__cleaner();
		// Throttle after cleaner.
		sleep(1);
        // Send sucess message!
        wp_send_json_success('Reload'); 
    }
    // Send sucess message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 3b, Init Remove individual Media Deleted ', 'low', 'rbp_media_cleaner');
    wp_send_json_success('Reload');      
}
?>