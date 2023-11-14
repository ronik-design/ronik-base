<?php 
/**
* Init Remove row Media .
*/

if(isset($_POST['row-id'])){
    if($_POST['image-id']){
        $data = wp_get_attachment_metadata( $_POST['image-id'] ); // get the data structured
        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_temp-saved';  // change the values you need to change
        wp_update_attachment_metadata( $_POST['image-id'], $data );  // save it back to the db        
    }

    $rbp_media_cleaner_file_size = get_option('rbp_media_cleaner_'.$_POST['row-id'].'_file_size') ? delete_option('rbp_media_cleaner_'.$_POST['row-id'].'_file_size', '') : false;
    $rbp_media_cleaner_image_id = get_option('rbp_media_cleaner_'.$_POST['row-id'].'_image_id') ? delete_option('rbp_media_cleaner_'.$_POST['row-id'].'_image_id', '') : false;
    $rbp_media_cleaner_image_url = get_option('rbp_media_cleaner_'.$_POST['row-id'].'_image_url') ? delete_option('rbp_media_cleaner_'.$_POST['row-id'].'_image_url', '') : false;
    $rbp_media_cleaner_thumbnail_preview = get_option('rbp_media_cleaner_'.$_POST['row-id'].'_thumbnail_preview') ? delete_option('rbp_media_cleaner_'.$_POST['row-id'].'_thumbnail_preview', '') : false;
    
    if( !$rbp_media_cleaner_file_size  || !$rbp_media_cleaner_image_id  || !$rbp_media_cleaner_image_url || !$rbp_media_cleaner_thumbnail_preview ){
        // If no rows are found send the error message!
        wp_send_json_error('No rows found!');
    } else {        
        // Send sucess message!
        wp_send_json_success('Reload');
    }

} else{
    // If no rows are found send the error message!
    wp_send_json_error('No rows found!');
}