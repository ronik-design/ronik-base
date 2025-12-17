<?php
$autoloadPath = dirname(__FILE__, 4) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
	require_once $autoloadPath;
} else {
	error_log('❌ Autoload not found at media-cleaner: ' . $autoloadPath);
	wp_die('Autoload file missing.');
}


use Ronik\Base\RbpHelper;
use Ronik\Base\RmcDataGathering;
use Ronik\Base\RonikBaseHelper;

$rbpHelper = new RbpHelper;

// Check if user is logged in first
if (!is_user_logged_in()) {
	wp_send_json_error('Authentication required', 401);
	wp_die();
}

// Verify nonce for security
if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ajax-nonce')) {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5a, wp_send_json_error ', 'low', 'rbp_media_cleaner');
	wp_send_json_error('Security check failed', 400);
	wp_die();
}

function rmc_timeout_extend($time)
{
	// Default timeout is 5
	return 200;
}
add_filter('http_request_timeout', 'rmc_timeout_extend');


// Simple function that erases all option data from plugin,
function databaseScannerMedia__cleaner()
{
	$rbpHelper = new RbpHelper;
	global $wpdb;

	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 4a, databaseScannerMedia__cleaner Lets cleanup the database ', 'low', 'rbp_media_cleaner');
	// Use proper table name with prefix
	$sql = $wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_name ASC", $wpdb->esc_like('rbp_media_cleaner_') . '%');
	$results = $wpdb->get_results($sql, ARRAY_A);
	if ($results) {
		foreach ($results as $result) {
			if (
				($result['option_name'] !== 'rbp_media_cleaner_api_key') &&
				($result['option_name'] !== 'rbp_media_cleaner_api_key_validation') &&
				($result['option_name'] !== 'rbp_media_cleaner_counter') &&
				($result['option_name'] !== 'rbp_media_cleaner_increment') &&
				($result['option_name'] !== 'rbp_media_cleaner_sync-time')
			) {
				delete_option($result['option_name']);
			}
		}
	}
}



// Sanitize and validate post_overide parameter
$post_override = isset($_POST['post_overide']) ? sanitize_text_field($_POST['post_overide']) : '';

if ($post_override == 'media-preserve') {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5b, media-preserve ', 'low', 'rbp_media_cleaner');
	$preserve_file = dirname(__FILE__) . '/media-cleaner_preserve.php';
	if (file_exists($preserve_file)) {
		include $preserve_file;
	}
} else if ($post_override == 'media-unpreserve') {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5b, media-unpreserve ', 'low', 'rbp_media_cleaner');
	$unpreserve_file = dirname(__FILE__) . '/media-cleaner_unpreserve.php';
	if (file_exists($unpreserve_file)) {
		include $unpreserve_file;
	}
} else if ($post_override == 'media-delete-indiv') {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5b, media-delete-indiv ', 'low', 'rbp_media_cleaner');
	$delete_file = dirname(__FILE__) . '/media-cleaner_delete_indiv.php';
	if (file_exists($delete_file)) {
		include $delete_file;
	}
} else {
	$user_option = isset($_POST['user_option']) ? sanitize_text_field($_POST['user_option']) : '';
	
	if (!$user_option) {
		$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5c, Security check failed ', 'low', 'rbp_media_cleaner');
		wp_send_json_error('Security check failed', 400);
		wp_die();
	}

	if ($user_option == 'fetch-media') {
		$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5d, fetch-media ', 'low', 'rbp_media_cleaner');
		$init_file = dirname(__FILE__) . '/media-cleaner_init.php';
		if (file_exists($init_file)) {
			include $init_file;
		}
	}
	if ($user_option == 'delete-media') {
		$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5e, delete-media ', 'low', 'rbp_media_cleaner');
		$remove_file = dirname(__FILE__) . '/media-cleaner-remove.php';
		if (file_exists($remove_file)) {
			include $remove_file;
		}
	}
}
