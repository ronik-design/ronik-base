<?php

use Ronik\Base\RmcDataGathering;
use Ronik\Base\RbpHelper;
use Ronik\Base\RonikBaseHelper;

/**
 * Code Referenced
 * https://gist.github.com/emiliojva/d894833bf8dbafc5e0e3bb68c9f2c4ea
 */
function formatSizeUnits($bytes)
{
	if ($bytes >= 1073741824) {
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	} elseif ($bytes >= 1048576) {
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	} elseif ($bytes >= 1024) {
		$bytes = number_format($bytes / 1024, 2) . ' KB';
	} elseif ($bytes > 1) {
		$bytes = $bytes . ' bytes';
	} elseif ($bytes == 1) {
		$bytes = $bytes . ' byte';
	} else {
		$bytes = '0 bytes';
	}
	return $bytes;
}

// POST CLEANING
function rmc_cleanInputPOST() {
	function cleanInput($input){
		$search = array(
		  '@<script[^>]*?>.*?</script>@si',
		  '@<[\/\!]*?[^<>]*?>@si',
		  '@<style[^>]*?>.*?</style>@siU',
		  '@<![\s\S]*?--[ \t\n\r]*>@'
		);
		$output = preg_replace($search, '', $input);
		$additional_output = sanitize_text_field( $output );
		return $additional_output;
	}
	// Next lets santize the post data.
	foreach ($_POST as $key => $value) {
		$_POST[$key] = cleanInput($value);
	}
}


function rmc_getLineWithString_ronikdesigns($fileName, $id)
{
	$f_attached_file = get_attached_file($id);
	$pieces = explode('/', $f_attached_file);
	$lines = file(urldecode($fileName));
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, end($pieces)) !== false) {
			return $id;
		}
	}
}

// This function pretty much scans all the files of the entire active theme.
// We try to ignore files that are not using images within.
function rmc_receiveAllFiles_ronikdesigns($dir, $image_id)
{
	$result = array();
	$array_disallow = array("functions.php", "package.json", "package-lock.json", ".", "..", ".DS_Store", "README.md", "composer.json", "composer.lock", ".gitkeep", "node_modules", "vendor");
	$results = array_diff(scandir($dir), $array_disallow);
	$results_reindexed = array_values(array_filter($results));
	$image_ids = '';
	if ($results_reindexed) {
		foreach ($results_reindexed as $key => $value) {
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
				$result[$dir . DIRECTORY_SEPARATOR . $value] = rmc_receiveAllFiles_ronikdesigns($dir . DIRECTORY_SEPARATOR . $value,  $image_id);
			} else {
				$result[] = $value;
				if (rmc_getLineWithString_ronikdesigns(urlencode($dir . DIRECTORY_SEPARATOR . $value), $image_id)) {
					// Unfortunately we have to use the super global variable
					$_POST['imageDirFound'] = rmc_getLineWithString_ronikdesigns(urlencode($dir . DIRECTORY_SEPARATOR . $value), $image_id);
				}
			}
		}
	}
	if (isset($_POST['imageDirFound'])) {
		return $_POST['imageDirFound'];
	} else {
		return;
	}
}


// Simple function that returns mimetype,
function cleaner_post_mime_type($mime_type)
{
	// $post_mime_type = get_field('page_media_cleaner_post_mime_type_field_ronikdesign', 'options');
	$post_mime_type = explode(",", $mime_type);
	// https://developer.wordpress.org/reference/hooks/mime_types/
	if ($post_mime_type) {
		$select_attachment_type = array();
		foreach ($post_mime_type as $type) {
			if ($type == 'jpg') {
				$select_attachment_type['jpg'] = "image/jpg";
				$select_attachment_type['jpeg'] = "image/jpeg";
				$select_attachment_type['jpe'] = "image/jpe";
			} else if ($type == 'gif') {
				$select_attachment_type['gif'] = "image/gif";
			} else if ($type == 'png') {
				$select_attachment_type['png'] = "image/png";
			} else if ($type == 'pdf') {
				$select_attachment_type['pdf'] = "application/pdf";
			} else if ($type == 'audio') {
				$select_attachment_type['mp3|m4a|m4b'] = "audio/mpeg";
				$select_attachment_type['wav'] = "audio/wav";
				$select_attachment_type['ogg'] = "audio/ogg";
				$select_attachment_type['wma'] = "audio/x-ms-wma";
				$select_attachment_type['aac'] = "audio/aac";
				$select_attachment_type['flac'] = "audio/flac";
			} else if ($type == 'video') {
				$select_attachment_type['asf|asx'] = "video/x-ms-asf";
				$select_attachment_type['wmv'] = "video/x-ms-wmv";
				$select_attachment_type['wmx'] = "video/x-ms-wmx";
				$select_attachment_type['wm'] = "video/x-ms-wm";
				$select_attachment_type['avi'] = "video/avi";
				$select_attachment_type['divx'] = "video/divx";
				$select_attachment_type['flv'] = "video/x-flv";
				$select_attachment_type['mov|qt'] = "video/quicktime";
				$select_attachment_type['mpeg|mpg|mpe'] = "video/mpeg";
				$select_attachment_type['mp4|m4v'] = "video/mp4";
				$select_attachment_type['ogv'] = "video/ogg";
				$select_attachment_type['webm'] = "video/webm";
				$select_attachment_type['mkv'] = "video/x-matroska";
			} else if ($type == 'misc') {
				$select_attachment_type['js'] = "application/javascript";
				$select_attachment_type['pdf'] = "application/pdf";
				$select_attachment_type['tar'] = "application/x-tar";
				$select_attachment_type['zip'] = "application/zip";
				$select_attachment_type['gz|gzip'] = "application/x-gzip";
				$select_attachment_type['rar'] = "application/rar";
				$select_attachment_type['txt|asc|c|cc|h|srt'] = "text/plain";
				$select_attachment_type['csv'] = "text/csv";
				$select_attachment_type['svg'] = 'image/svg+xml';
				$select_attachment_type['webp'] = "image/webp";
			} else if ($type == 'all') {
				$select_attachment_type['svg'] = 'image/svg+xml';
				$select_attachment_type['jpg'] = "image/jpg";
				$select_attachment_type['jpeg'] = "image/jpeg";
				$select_attachment_type['jpe'] = "image/jpe";
				$select_attachment_type['gif'] = "image/gif";
				$select_attachment_type['png'] = "image/png";
				$select_attachment_type['pdf'] = "application/pdf";
				$select_attachment_type['mp3|m4a|m4b'] = "audio/mpeg";
				$select_attachment_type['wav'] = "audio/wav";
				$select_attachment_type['ogg'] = "audio/ogg";
				$select_attachment_type['wma'] = "audio/x-ms-wma";
				$select_attachment_type['aac'] = "audio/aac";
				$select_attachment_type['flac'] = "audio/flac";
				$select_attachment_type['asf|asx'] = "video/x-ms-asf";
				$select_attachment_type['wmv'] = "video/x-ms-wmv";
				$select_attachment_type['wmx'] = "video/x-ms-wmx";
				$select_attachment_type['wm'] = "video/x-ms-wm";
				$select_attachment_type['avi'] = "video/avi";
				$select_attachment_type['divx'] = "video/divx";
				$select_attachment_type['flv'] = "video/x-flv";
				$select_attachment_type['mov|qt'] = "video/quicktime";
				$select_attachment_type['mpeg|mpg|mpe'] = "video/mpeg";
				$select_attachment_type['mp4|m4v'] = "video/mp4";
				$select_attachment_type['ogv'] = "video/ogg";
				$select_attachment_type['webm'] = "video/webm";
				$select_attachment_type['mkv'] = "video/x-matroska";
				$select_attachment_type['js'] = "application/javascript";
				$select_attachment_type['pdf'] = "application/pdf";
				$select_attachment_type['tar'] = "application/x-tar";
				$select_attachment_type['zip'] = "application/zip";
				$select_attachment_type['gz|gzip'] = "application/x-gzip";
				$select_attachment_type['rar'] = "application/rar";
				$select_attachment_type['txt|asc|c|cc|h|srt'] = "text/plain";
				$select_attachment_type['csv'] = "text/csv";
				$select_attachment_type['webp'] = "image/webp";
			}
		}
		return $select_attachment_type;
	}
	return 'any';
}


// Pretty much a quick way to get the overall count of the media.
function databaseScannerMedia__allMedia($requestParameter)
{
	// Helper Guide
	$rbpHelper = new RbpHelper;
	$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 14a, databaseScannerMedia__allMedia a quick way to get the overall count of the media. ', 'low', 'rbp_media_cleaner');


	$helper = new RonikBaseHelper;
	// error_log(print_r('Lets gather all the images of the entire site.', true));
	$helper->ronikdesigns_write_log_devmode('Lets gather all the images of the entire site.', 'low');

	$helper->ronikdesigns_write_log_devmode($requestParameter, 'low');

	global $wpdb;
	// Reformat for quotes...
	array_walk($requestParameter[1], fn(&$x) => $x = "'$x'");
	$tablename = $wpdb->prefix . "posts";
	$sql = $wpdb->prepare(
		"SELECT * FROM $tablename WHERE post_type = 'attachment' AND post_mime_type IN (" . implode(" , ", $requestParameter[1]) . ") ORDER BY ID ASC",
		$tablename
	);
	$results = $wpdb->get_results($sql, ARRAY_A);


	if ($requestParameter[0] == 'count') {
		return count($results);
	}
}



// // Pretty much a quick way to get the overall count of the media.
// function databaseScannerMedia__allPosts( $requestParameter ) {
//     // Helper Guide
// 	$helper = new RonikBaseHelper;
//     // error_log(print_r('Lets gather all the images of the entire site.', true));
//     $helper->ronikdesigns_write_log_devmode('Lets gather all the images of the entire site.', 'low');

//     $helper->ronikdesigns_write_log_devmode($requestParameter, 'low');

//     global $wpdb;
//     // Reformat for quotes...
//     array_walk($requestParameter[1], fn(&$x) => $x = "'$x'");
//     $tablename = $wpdb->prefix . "posts";
//     $sql = $wpdb->prepare(
//         "SELECT * FROM $tablename WHERE post_type = 'attachment' AND post_mime_type IN (".implode(" , ", $requestParameter[1]).") ORDER BY ID ASC", $tablename );
//     $results = $wpdb->get_results( $sql , ARRAY_A );


//     if($requestParameter[0] == 'count'){
//         return count($results);
//     }
// }



function cleaner_compare_array_diff($primary, $secondary)
{
	// Helper Guide
	$helper = new RonikBaseHelper;
	if ($secondary) {
		// array_diff: Compares array against one or more other arrays and returns the values in array that are not present in any of the other arrays.
		// array_values: Return all the values of an array
		$arr_mixed_diff = array_values(array_diff($primary, $secondary));
	} else {
		$arr_mixed_diff = $primary;
	}
	// error_log(print_r( 'arr_mixed_diff' , true));
	// error_log(print_r( $arr_mixed_diff , true));
	$helper->ronikdesigns_write_log_devmode('arr_mixed_diff', 'low');
	$helper->ronikdesigns_write_log_devmode($arr_mixed_diff, 'low');

	// 'reindex' array to cleanup...
	if (is_array($arr_mixed_diff) && $arr_mixed_diff) {
		$arr_mixed_diff_reindexed = array_values(array_filter($arr_mixed_diff));
	} else {
		$arr_mixed_diff_reindexed = [];
	}
	// error_log(print_r( 'arr_mixed_diff_reindexed' , true));
	// error_log(print_r( $arr_mixed_diff_reindexed , true));
	$helper->ronikdesigns_write_log_devmode('arr_mixed_diff_reindexed', 'low');
	$helper->ronikdesigns_write_log_devmode($arr_mixed_diff_reindexed, 'low');
	return $arr_mixed_diff_reindexed;
}
