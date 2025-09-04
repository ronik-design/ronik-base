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


if (isset($_POST['imageId']) && $_POST['imageId'] && $_POST['preserveType'] == 'preserve') {
    $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

    if (str_contains($_POST['imageId'], ',')) {
        $imageIdArray = explode(',', $_POST['imageId']);
        foreach ($imageIdArray as $imageId) {
            $data = wp_get_attachment_metadata($imageId); // get the data structured
            $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_temp-saved';  // change the values you need to change
            wp_update_attachment_metadata($imageId, $data);  // save it back to the db

            $array_without_preserved_img = $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = array_values(
                array_diff(
                    $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized,
                    array($imageId)
                )
            );
            set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_without_preserved_img, DAY_IN_SECONDS);
        }
    } else {
        $data = wp_get_attachment_metadata($_POST['imageId']); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_temp-saved';  // change the values you need to change
        wp_update_attachment_metadata($_POST['imageId'], $data);  // save it back to the db
        $array_without_preserved_img = $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = array_values(
            array_diff(
                $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized,
                array($_POST['imageId'])
            )
        );
        set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_without_preserved_img, DAY_IN_SECONDS);
    }
    // Send sucess message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7b, Init Remove row Media Unpreserve', 'low', 'rbp_media_cleaner');
    wp_send_json_success('Reload');
}




















error_log('Unpreserve KEVIN FIX THIS ');
/// Unpreserve KEVIN FIX THIS 
if (isset($_POST['imageId']) && $_POST['imageId'] && $_POST['preserveType'] == 'unpreserve') {
    if (($_POST['imageId'] !== 'invalid') && $_POST['imageId'] !== 'undefined') {
        $data = wp_get_attachment_metadata($_POST['imageId']); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_temp-saved';  // change the values you need to change
        wp_update_attachment_metadata($_POST['imageId'], $data);  // save it back to the db
        $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');
        $array_without_preserved_img = array_values(array_diff($transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized, array($_POST['imageId'])));
        $array_without_preserved_img_unique = array_unique($array_without_preserved_img);
        set_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized', $array_without_preserved_img_unique, DAY_IN_SECONDS);
        // Send sucess message!
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7b, Init Remove row Media Preserve', 'low', 'rbp_media_cleaner');

        wp_send_json_success('Reload');
    }
} else {
    // If no rows are found send the error message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7c, Init Remove row Media No rows found!', 'low', 'rbp_media_cleaner');

    wp_send_json_error('No rows found!');
}
