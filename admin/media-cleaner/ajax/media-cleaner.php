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

if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5a, wp_send_json_error ', 'low', 'rbp_media_cleaner');

	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
	return;
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

	// Remove the original post value to null..
	$_POST['imageDirFound'] = '';
	$tablename = $wpdb->prefix . "posts";
	$sql = $wpdb->prepare("SELECT * FROM wp_options WHERE option_name LIKE '%rbp_media_cleaner_%' ORDER BY option_name ASC", $tablename);
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



if ($_POST['post_overide'] == 'media-preserve') {
	error_log(print_r('preserve 2 ', true));

	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5b, media-preserve ', 'low', 'rbp_media_cleaner');
	foreach (glob(dirname(__FILE__) . '/media-cleaner_preserve.php') as $file) {
		include $file;
	}
} else if ($_POST['post_overide'] == 'media-unpreserve') {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5b, media-unpreserve ', 'low', 'rbp_media_cleaner');
	foreach (glob(dirname(__FILE__) . '/media-cleaner_unpreserve.php') as $file) {
		include $file;
	}
} else if ($_POST['post_overide'] == 'media-delete-indiv') {
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5b, media-delete-indiv ', 'low', 'rbp_media_cleaner');
	foreach (glob(dirname(__FILE__) . '/media-cleaner_delete_indiv.php') as $file) {
		include $file;
	}
} else {
	if (!$_POST['user_option']) {
		$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5c, Security check failed ', 'low', 'rbp_media_cleaner');

		wp_send_json_error('Security check failed', '400');
		wp_die();
	}

	if (($_POST['user_option'] == 'fetch-media')) {
		$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5d, fetch-media ', 'low', 'rbp_media_cleaner');

		foreach (glob(dirname(__FILE__) . '/media-cleaner_init.php') as $file) {
			include $file;
		}
	}
	if ($_POST['user_option'] == 'delete-media') {
		$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 5e, delete-media ', 'low', 'rbp_media_cleaner');

		foreach (glob(dirname(__FILE__) . '/media-cleaner-remove.php') as $file) {
			include $file;
		}
	}
}
