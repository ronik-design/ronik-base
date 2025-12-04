<?php

/**
 * Init Remove row Media .
 */

use Ronik\Base\RbpHelper;

$rbpHelper = new RbpHelper;
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7a, Init Remove row Media ', 'low', 'rbp_media_cleaner');

$rbp_media_cleaner_data_array = get_option('rbp_media_cleaner_media_data');
if (!$rbp_media_cleaner_data_array) {
    $rbp_media_cleaner_data_array = array();
} else {
    $rbp_media_cleaner_data_array = array_values(array_filter($rbp_media_cleaner_data_array));
}



error_log(print_r($_POST, true));

if (isset($_POST['imageId']) && $_POST['imageId'] && $_POST['preserveType'] == 'unpreserve') {
    $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

    error_log(print_r($transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized, true));


    if (str_contains($_POST['imageId'], ',')) {
        $imageIdArray = explode(',', $_POST['imageId']);
        // Convert string IDs to integers
        $imageIdArray = array_map('intval', $imageIdArray);
        
        // Update metadata for all images
        foreach ($imageIdArray as $imageId) {
            $data = wp_get_attachment_metadata($imageId); // get the data structured
            if ($data) {
                $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_false';  // change the values you need to change
                wp_update_attachment_metadata($imageId, $data);  // save it back to the db
            }
        }
        
        // Merge all IDs with transient at once
        $array_with_preserved_img = array_merge(
            $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized,
            $imageIdArray
        );
        
        // Remove duplicates and reindex
        $array_with_preserved_img = array_values(array_unique($array_with_preserved_img));
        
        error_log(print_r($array_with_preserved_img, true));
        
        // Save transient once with all IDs
        set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_with_preserved_img, DAY_IN_SECONDS);
        
        error_log(print_r('SAVED', true));
    } else {
        $data = wp_get_attachment_metadata($_POST['imageId']); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_false';  // change the values you need to change
        wp_update_attachment_metadata($_POST['imageId'], $data);  // save it back to the db



        $array_with_preserved_img = array_merge(
            $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized,
            array($_POST['imageId'])
        );



        set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_with_preserved_img, DAY_IN_SECONDS);
    }
    // Send sucess message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7b, Init Remove row Media Unpreserve', 'low', 'rbp_media_cleaner');
    wp_send_json_success('Reload');
}









