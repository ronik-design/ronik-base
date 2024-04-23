<?php
/**
* Init Remove row Media .
*/

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

        $rbp_media_cleaner_data_array[] = $_POST['unPreserveImageId'];
        $rbp_media_cleaner_data_array = array_values(array_filter($rbp_media_cleaner_data_array));
        update_option('rbp_media_cleaner_media_data', $rbp_media_cleaner_data_array);

        // // Send sucess message!
        error_log(print_r('Unpreserve', true));
        wp_send_json_success('Reload');
    }
}

if(isset($_POST['preserveImageId']) && $_POST['preserveImageId']){
    if(($_POST['preserveImageId'] !== 'invalid') && $_POST['preserveImageId'] !== 'undefined'){
        $data = wp_get_attachment_metadata( $_POST['preserveImageId'] ); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_temp-saved';  // change the values you need to change
        wp_update_attachment_metadata( $_POST['preserveImageId'], $data );  // save it back to the db

        $array_without_preserved_img = array_values(array_diff($rbp_media_cleaner_data_array, array($_POST['preserveImageId'])));
        $array_without_preserved_img_unique = array_unique($array_without_preserved_img);
        update_option('rbp_media_cleaner_media_data', array_values(array_filter($array_without_preserved_img_unique)));

        // Send sucess message!
        error_log(print_r('Preserve', true));
        wp_send_json_success('Reload');
    }
} else{
    // If no rows are found send the error message!
    wp_send_json_error('No rows found!');
}
