<?php 
/**
* Init Remove Unused Media .
*/

if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
	return;
}


if($_POST['plugin_slug'] == 'ronik_media_cleaner'){
    if(!get_option('rbp_media_cleaner_api_key') && get_option('rbp_media_cleaner_api_key') !== '' ){
        $f_option = add_option('rbp_media_cleaner_api_key', $_POST['apikey']);
    } else {
        $f_option = update_option('rbp_media_cleaner_api_key', $_POST['apikey']);
    }
    if($f_option){
        // Send sucess message!
        wp_send_json_success('Reload');
    } else{
        // Send error message!
        wp_send_json_error('No rows found!');
    }
}

if($_POST['plugin_slug'] == 'ronik_optimization'){
    if(!get_option('rbp_optimization_api_key')){
        $f_option = add_option('rbp_optimization_api_key', $_POST['apikey']);
    } else {
        $f_option = update_option('rbp_optimization_api_key', $_POST['apikey']);
    }

    if($f_option){
        // Send sucess message!
        wp_send_json_success('Reload');
    } else{
        // Send error message!
        wp_send_json_error('No rows found!');
    }
}

