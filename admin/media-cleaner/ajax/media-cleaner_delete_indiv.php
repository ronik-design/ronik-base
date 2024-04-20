<?php 
/**
* Init Remove individual Media .
*/


error_log(print_r('imageId', true));
error_log(print_r($_POST['imageId'], true));


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


    $f_id_delete = wp_delete_attachment( $_POST['imageId'] , true);
    if($f_id_delete){
        // Send sucess message!
        wp_send_json_success('Reload'); 
    } else{
        wp_delete_attachment( $_POST['imageId'] , false);
    }

    // Send sucess message!
    error_log(print_r('Deleted', true));
    wp_send_json_success('Reload');      
}
?>