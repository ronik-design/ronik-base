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
    $f_option = update_option('rbp_media_cleaner_api_key', (isset($_POST['apikey'])) ? $_POST['apikey'] : '' );
    $f_option_validation = update_option('rbp_media_cleaner_api_key_validation', (isset($_POST['apikeyValidation'])) ? $_POST['apikeyValidation'] : 'invalid' );

    if($f_option){
        // Send sucess message!
        wp_send_json_success('Reload');
    } else{
        // Send error message!
        wp_send_json_error('No rows found!');
    }
}

if($_POST['plugin_slug'] == 'ronik_optimization'){
    $f_option = update_option('rbp_optimization_api_key', (isset($_POST['apikey'])) ? $_POST['apikey'] : '' );
    $f_option_validation = update_option('rbp_optimization_api_key_validation', (isset($_POST['apikeyValidation'])) ? $_POST['apikeyValidation'] : 'invalid' );

    if($f_option){
        // Send sucess message!
        wp_send_json_success('Reload');
    } else{
        // Send error message!
        wp_send_json_error('No rows found!');
    }
}

