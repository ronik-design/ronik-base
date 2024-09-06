<?php

class RonikBaseHelper{
    // Semi Imitates the loose LIKE%% Comparison
    public function ronik_compare_like($a_value , $b_value){
        if(stripos($a_value, $b_value) !== FALSE){
            return true;
        } else {
            return false;
        }
    }


    // Function that detects if on local mode.
    public function localValidator(){
        // Check for local string in host.
        $is_local =  str_contains( $_SERVER['HTTP_HOST'] , 'local');
        // If false we check to see if the REMOTE_ADDR is the default local host ip address..
        if(!$is_local){
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                $is_local = true;
            } else {
                $is_local = false;
            }
        }
        return $is_local;
    }

    // Creates an encoded svg for src, lazy loading.
    // This pretty much creates a basebone svg structure.
    public function ronikdesignsbase_svgplaceholder($imgacf=null) {
        $iacf = $imgacf;
        if($iacf){
            if($iacf['width']){
                $width = $iacf['width'];
            }
            if($iacf['height']){
                $height = $iacf['height'];
            }
            $viewbox = "width='{$width}' height='{$height}' viewBox='0 0 {$width} {$height}'";
        } else{
            $viewbox = "viewBox='0 0 100 100'";
        }
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' {$viewbox}%3E%3C/svg%3E";
    }
    

    public function ronikdesigns_increase_memory(){
        // Lets us set the max_execution_time to 1hr
        error_log(print_r( 'First max_execution_time: ' . ini_get('max_execution_time'), true ));
        @set_time_limit( intval( 3600*2 ) );
        error_log(print_r( 'Rewrite max_execution_time: ' . ini_get('max_execution_time'), true ));
        error_log(print_r( 'First memory_limit: ' . ini_get('memory_limit'), true ));
        ini_set('memory_limit', '5024M');
        error_log(print_r( 'Rewrite memory_limit: ' . ini_get('memory_limit'), true ));
    }

	// Write error logs cleanly.
    public function ronikdesigns_write_log($log) {
		// $f_error_email = get_field('error_email', 'option');
        $f_error_email = get_option( 'admin_email' );
		if ($f_error_email) {
			// Remove whitespace.
			$f_error_email = str_replace(' ', '', $f_error_email);
			// Lets run a backtrace to get more useful information.
			$t = debug_backtrace();
			$t_file = 'File Path Location: ' . $t[0]['file'];
			$t_line = 'On Line: ' .  $t[0]['line'];
			$to = $f_error_email;
			$subject = 'Error Found';
			$body = 'Error Message: ' . $log . '<br><br>' . $t_file . '<br><br>' . $t_line;
			$headers = array('Content-Type: text/html; charset=UTF-8');
			wp_mail($to, $subject, $body, $headers);
		}
		if (is_array($log) || is_object($log)) {
			error_log(print_r('<----- ' . $log . ' ----->', true));
		} else {
			error_log(print_r('<----- ' . $log . ' ----->', true));
		}
	}

	// Write error logs cleanly.
	public function ronikdesigns_write_log_devmode($log, $severity_level='low') {
		if($severity_level == 'low'){
			return false;
		}
		// $f_error_email = get_field('error_email', 'option');
        $f_error_email = get_option( 'admin_email' );
		// Lets run a backtrace to get more useful information.
		$t = debug_backtrace();
		$t_file = 'File Path Location: ' . $t[0]['file'];
		$t_line = 'On Line: ' .  $t[0]['line'];

		//  Low, Medium, High, and Critical
		if( $severity_level == 'critical' ){
			if ($f_error_email) {
				// Remove whitespace.
				$f_error_email = str_replace(' ', '', $f_error_email);
				$to = $f_error_email;
				$subject = 'Error Found';
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$body = 'Website URL: '. $_SERVER['HTTP_HOST'] .'<br><br>Error Message: ' . $log . '<br><br>' . $t_file . '<br><br>' . $t_line;
				wp_mail($to, $subject, $body, $headers);
			}
		}
		if (is_array($log) || is_object($log)) {
			error_log(print_r('<----- ' . json_encode($log) . ' ----->', true));
			error_log(print_r( $t_file , true));
			error_log(print_r( $t_line , true));
			error_log(print_r('<----- END LOG '.json_encode($log).' ----->', true));
			error_log(print_r('   ', true));

		} else {
			error_log(print_r('<----- ' . $log . ' ----->', true));
			error_log(print_r( $t_file , true));
			error_log(print_r( $t_line , true));
			error_log(print_r('<----- END LOG '.$log.' ----->', true));
			error_log(print_r('   ', true));
		}
	}
}

/**
 * Code Referenced
 * https://gist.github.com/emiliojva/d894833bf8dbafc5e0e3bb68c9f2c4ea
 */
function formatSizeUnits($bytes){
    if ($bytes >= 1073741824){
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576){
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024){
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1){
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1){
        $bytes = $bytes . ' byte';
    }
    else{
        $bytes = '0 bytes';
    }
    return $bytes;
}

function rmc_getLineWithString_ronikdesigns($fileName, $id) {
	$f_attached_file = get_attached_file( $id );
	$pieces = explode('/', $f_attached_file ) ;
	$lines = file( urldecode($fileName) );
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, end($pieces)) !== false) {
			return $id;
		}
	}
}

// This function pretty much scans all the files of the entire active theme.
// We try to ignore files that are not using images within.
function rmc_receiveAllFiles_ronikdesigns($dir, $image_id){
	$result = array();
	$array_disallow = array("functions.php", "package.json", "package-lock.json", ".", "..", ".DS_Store", "README.md", "composer.json", "composer.lock", ".gitkeep", "node_modules", "vendor");
	$results = array_diff(scandir($dir), $array_disallow);
	$results_reindexed = array_values(array_filter($results));
	$image_ids = '';
	if($results_reindexed){
		foreach ($results_reindexed as $key => $value){
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				$result[$dir . DIRECTORY_SEPARATOR . $value] = rmc_receiveAllFiles_ronikdesigns($dir . DIRECTORY_SEPARATOR . $value,  $image_id );
			} else {
				$result[] = $value;
				if(rmc_getLineWithString_ronikdesigns( urlencode($dir . DIRECTORY_SEPARATOR . $value) , $image_id)){
					// Unfortunately we have to use the super global variable
					$_POST['imageDirFound'] = rmc_getLineWithString_ronikdesigns( urlencode($dir . DIRECTORY_SEPARATOR . $value) , $image_id);
				}
			}
		}
	}
    if(isset($_POST['imageDirFound'])){
        return $_POST['imageDirFound'];
    } else{
        return;
    }
}


// Simple function that returns mimetype,
function cleaner_post_mime_type($mime_type){
    // $post_mime_type = get_field('page_media_cleaner_post_mime_type_field_ronikdesign', 'options');
    $post_mime_type = explode(",", $mime_type);
    // https://developer.wordpress.org/reference/hooks/mime_types/
    if($post_mime_type){
        $select_attachment_type = array();
        foreach($post_mime_type as $type){
            if($type == 'jpg'){
                $select_attachment_type['jpg'] = "image/jpg";
                $select_attachment_type['jpeg'] = "image/jpeg";
                $select_attachment_type['jpe'] = "image/jpe";
            } else if($type == 'gif'){
                $select_attachment_type['gif'] = "image/gif";
            } else if($type == 'png'){
                $select_attachment_type['png'] = "image/png";
            } else if($type == 'pdf'){
                $select_attachment_type['pdf'] = "application/pdf";
            } else if($type == 'video'){
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
            } else if($type == 'misc'){
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
            }  else if($type == 'all'){
                $select_attachment_type['svg'] = 'image/svg+xml';
                $select_attachment_type['jpg'] = "image/jpg";
                $select_attachment_type['jpeg'] = "image/jpeg";
                $select_attachment_type['jpe'] = "image/jpe";
                $select_attachment_type['gif'] = "image/gif";
                $select_attachment_type['png'] = "image/png";
                $select_attachment_type['pdf'] = "application/pdf";
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
function databaseScannerMedia__allMedia( $requestParameter ) {
    // Helper Guide
	$helper = new RonikBaseHelper;
    // error_log(print_r('Lets gather all the images of the entire site.', true));
    $helper->ronikdesigns_write_log_devmode('Lets gather all the images of the entire site.', 'low');

    $helper->ronikdesigns_write_log_devmode($requestParameter, 'low');

    global $wpdb;
    // Reformat for quotes...
    array_walk($requestParameter[1], fn(&$x) => $x = "'$x'");
    $tablename = $wpdb->prefix . "posts";
    $sql = $wpdb->prepare(
        "SELECT * FROM $tablename WHERE post_type = 'attachment' AND post_mime_type IN (".implode(" , ", $requestParameter[1]).") ORDER BY ID ASC", $tablename );
    $results = $wpdb->get_results( $sql , ARRAY_A );


    if($requestParameter[0] == 'count'){
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



function cleaner_compare_array_diff($primary, $secondary){
    // Helper Guide
	$helper = new RonikBaseHelper;
    if($secondary){
        // array_diff: Compares array against one or more other arrays and returns the values in array that are not present in any of the other arrays.
        // array_values: Return all the values of an array
        $arr_mixed_diff = array_values(array_diff($primary, $secondary) );
    } else{
        $arr_mixed_diff = $primary;
    }
    // error_log(print_r( 'arr_mixed_diff' , true));
    // error_log(print_r( $arr_mixed_diff , true));
    $helper->ronikdesigns_write_log_devmode('arr_mixed_diff', 'low');
    $helper->ronikdesigns_write_log_devmode($arr_mixed_diff, 'low');

    // 'reindex' array to cleanup...
    if(is_array($arr_mixed_diff) && $arr_mixed_diff){
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

