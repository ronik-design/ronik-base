<?php
if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
	return;
}

function ronikdesigns_timeout_extend( $time ){
	// Default timeout is 5
	return 20;
}
add_filter( 'http_request_timeout', 'ronikdesigns_timeout_extend' );



// Simple function that erases all option data from plugin,
function databaseScannerMedia__cleaner( ) {    
    global $wpdb;
    error_log(print_r('Lets cleanup the database', true));
    // Remove the original post value to null..
    $_POST['imageDirFound'] = '';
    $tablename = $wpdb->prefix . "posts";

    $sql = $wpdb->prepare( "SELECT * FROM wp_options WHERE option_name LIKE '%rbp_media_cleaner_%' ORDER BY option_name ASC", $tablename );
    $results = $wpdb->get_results( $sql , ARRAY_A );
    if($results){
        foreach($results as $result){
            if(
                ($result['option_name'] !== 'rbp_media_cleaner_api_key') && 
                ($result['option_name'] !== 'rbp_media_cleaner_api_key_validation') && 
                ($result['option_name'] !== 'rbp_media_cleaner_counter') && 
                ($result['option_name'] !== 'rbp_media_cleaner_increment') && 
                ($result['option_name'] !== 'rbp_media_cleaner_sync-time')
            ){
                delete_option( $result['option_name'] );
            }
        }
    }

    // $tablename = $wpdb->prefix . "posts";
    // $sql = $wpdb->prepare( 
    //     "SELECT * FROM $tablename WHERE post_type = 'attachment' ORDER BY ID ASC", $tablename );
    // $results = $wpdb->get_results( $sql , ARRAY_A );
    // if($results){
    //     foreach($results as $result){
    //         $data = wp_get_attachment_metadata( $result['ID'] ); // get the data structured
	// 		$data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_false';  // change the values you need to change
    //         wp_update_attachment_metadata( $result['ID'], $data );  // save it back to the db
    //    }
    // }
}



if ($_POST['post_overide'] == 'media-preserve'){
	foreach (glob(dirname(__FILE__) . '/media-cleaner_preserve.php') as $file) {
		include $file;
	}
} else if( $_POST['post_overide'] == 'media-delete-indiv') {
	foreach (glob(dirname(__FILE__) . '/media-cleaner_delete_indiv.php') as $file) {
		include $file;
	}
} else{
	if(!$_POST['user_option']){
		wp_send_json_error('Security check failed', '400');
		wp_die();	
	}
	
	if(($_POST['user_option'] == 'fetch-media')){
		foreach (glob(dirname(__FILE__) . '/media-cleaner_init.php') as $file) {
			include $file;
		}
	} 
	if($_POST['user_option'] == 'delete-media'){
		foreach (glob(dirname(__FILE__) . '/media-cleaner-remove.php') as $file) {
			include $file;
		}
	} 
}
