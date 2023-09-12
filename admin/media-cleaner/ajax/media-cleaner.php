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


if($_POST['post_overide'] == 'media-row-removal'){
	error_log(print_r('landed', true));
	foreach (glob(dirname(__FILE__) . '/media-cleaner_row-remove.php') as $file) {
		include $file;
	}
} else {
	if(!$_POST['user_option']){
		wp_send_json_error('Security check failed', '400');
		wp_die();	
	}
	
	if($_POST['user_option'] == 'fetch-media'){
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



