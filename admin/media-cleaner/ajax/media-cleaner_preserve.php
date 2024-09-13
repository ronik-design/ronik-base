<?php
/**
* Init Remove row Media .
*/

$rbpHelper = new RbpHelper;
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7a, Init Remove row Media ', 'low', 'rbp_media_cleaner');

$rbp_media_cleaner_data_array = get_option('rbp_media_cleaner_media_data');
if( !$rbp_media_cleaner_data_array ){
    $rbp_media_cleaner_data_array = array();
} else {
    $rbp_media_cleaner_data_array = array_values(array_filter($rbp_media_cleaner_data_array));
}

if(isset($_POST['unPreserveImageId']) && $_POST['unPreserveImageId']){
    if(($_POST['unPreserveImageId'] !== 'invalid') && $_POST['unPreserveImageId'] !== 'undefined'){
        $data = wp_get_attachment_metadata( $_POST['unPreserveImageId'] ); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = '';  // change the values you need to change
        wp_update_attachment_metadata( $_POST['unPreserveImageId'], $data );  // save it back to the db
        // update_option('rbp_media_cleaner_media_data', $rbp_media_cleaner_data_array);
        $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
        $array_with_preserved_img = array_merge($transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized, array($_POST['unPreserveImageId']) );
        set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' , $array_with_preserved_img , DAY_IN_SECONDS );
        // Send sucess message!
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7b, Init Remove row Media Unpreserve', 'low', 'rbp_media_cleaner');

        wp_send_json_success('Reload');
    }
}

if(isset($_POST['preserveImageId']) && $_POST['preserveImageId']){
    if(($_POST['preserveImageId'] !== 'invalid') && $_POST['preserveImageId'] !== 'undefined'){
        $data = wp_get_attachment_metadata( $_POST['preserveImageId'] ); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_temp-saved';  // change the values you need to change
        wp_update_attachment_metadata( $_POST['preserveImageId'], $data );  // save it back to the db
        $transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
        $array_without_preserved_img = array_values(array_diff($transient_rmc_media_cleaner_media_data_collectors_image_id_array_finalized, array($_POST['preserveImageId'])));
        $array_without_preserved_img_unique = array_unique($array_without_preserved_img);
        set_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' , $array_without_preserved_img_unique , DAY_IN_SECONDS );
        // Send sucess message!
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7b, Init Remove row Media Preserve', 'low', 'rbp_media_cleaner');

        wp_send_json_success('Reload');
    }
} else{
    // If no rows are found send the error message!
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 7c, Init Remove row Media No rows found!', 'low', 'rbp_media_cleaner');

    wp_send_json_error('No rows found!');
}
