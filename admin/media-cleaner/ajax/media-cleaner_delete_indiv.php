<?php 
/**
* Init Remove individual Media .
*/

use Ronik\Base\RbpHelper;
use Ronik\Base\RmcDataGathering;
use Ronik\Base\RonikBaseHelper;

$rbpHelper = new RbpHelper;
$RmcDataGathering = new RmcDataGathering;

// Increase memory limit before processing deletes
$helper = new RonikBaseHelper;
$helper->ronikdesigns_increase_memory();

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 3a, Init Remove individual Media imageId: '.$_POST['imageId'], 'low', 'rbp_media_cleaner');

$transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

if(isset($_POST['imageId']) && $_POST['imageId']){
    if (str_contains($_POST['imageId'], ',')) {
        $imageIdArray = explode(',', $_POST['imageId']);
        $total_count = count($imageIdArray);
        $processed = 0;
        
        foreach ($imageIdArray as $index => $imageId) {
            $delete_attachment = $RmcDataGathering->imageCloneSave( false,  $imageId);
            if($delete_attachment){
                // Simple function that resets everything before we continue processing all the files..
                databaseScannerMedia__cleaner();
                // Throttle after cleaner.
                sleep(1);
            }
            $array_without_deleted_img = $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = array_values(
                array_diff(
                    $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized,
                    array($imageId)
                )
            );
            set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_without_deleted_img, DAY_IN_SECONDS);
            
            $processed++;
            
            // Force garbage collection every 10 files to free memory
            if ($processed % 10 === 0) {
                gc_collect_cycles();
                $current_memory = memory_get_usage(true) / 1024 / 1024;
                $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Deleted ' . $processed . '/' . $total_count . ' files. Memory: ' . round($current_memory, 2) . 'MB', 'low', 'rbp_media_cleaner');
            }
        }
    } else {

        $delete_attachment = $RmcDataGathering->imageCloneSave( false,  $_POST['imageId']);
        if($delete_attachment){
            // Simple function that resets everything before we continue processing all the files..
            databaseScannerMedia__cleaner();
            // Throttle after cleaner.
            sleep(1);
        }
        $array_without_deleted_img = $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = array_values(
            array_diff(
                $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized,
                array($_POST['imageId'])
            )
        );
        set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_without_deleted_img, DAY_IN_SECONDS);

    }
    // Send sucess message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 3b, Init Remove individual Media Deleted ', 'low', 'rbp_media_cleaner');
    wp_send_json_success('Reload');     
}

?>