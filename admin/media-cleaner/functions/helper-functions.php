<?php

class RonikBaseHelper{
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

    // We have to comment this out because post_parent is not a 100% fact that image is attached or is not attached.
    // if($results){
    //     $array_collector = array();
    //     foreach($results as $key => $result){
    //         if( isset($result['post_parent']) ){
    //             if( ($result['post_parent'] == 0 )){
    //                 $array_collector[] = $result['ID'];
    //             }
    //         } else {
    //             $array_collector[] = $result['ID'];
    //         }
    //     }
    // }
    // if($requestParameter[0] == 'count'){
    //     if(isset($array_collector) && $array_collector){            
    //         return count($array_collector);
    //     } else {
    //         if($requestParameter[0] == 'count'){
    //             return count($results);
    //         }
    //     }
    // }
    if($requestParameter[0] == 'count'){
        return count($results);
    }
}


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

// Depending on the size of the site we would not want to do this but in this case it is okay.
function ronik_database_cleaner(){
    $f_ronik_database_cleaner = get_option( 'options_ronik-database-cleaner', 'not-triggered' );
    // delete_option( 'options_ronik-database-cleaner' );
    // update_option( 'options_ronik-database-cleaner' , 'not-triggered' );

    if($f_ronik_database_cleaner == 'not-triggered'){
        // Switch to triggered value this will prevent multiple triggers
        update_option( 'options_ronik-database-cleaner' , 'triggered' );
        // Lets target the wp_options values.
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM wp_options WHERE option_value LIKE '%[:en]%' ORDER BY option_name ASC", ARRAY_A );
        if($results){
            foreach($results as $result){
                // English Target
                $string_option = explode('[:zh]', (explode('[:en]', $result['option_value'])[1]))[0];
                update_option( $result['option_name'], wp_slash($string_option) );
            }
        }
        // Lets target the wp_posts post_content values
        // TARGET: <!--:en-->
        $results = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_content LIKE '%<!--:en-->%' ORDER BY post_title ASC", ARRAY_A );
        if($results){
            foreach($results as $result){
                // English Target
                $string_content = explode('<!--:-->', (explode('<!--:en-->', $result['post_content'])[1]))[0];
                // Update post
                $my_post = array(
                    'ID'           => $result['ID'],
                    'post_content' => wp_slash($string_content),
                );
                // Update the post into the database
                wp_update_post( $my_post );

            }
        }
        // Lets target the wp_posts post_title values
        // TARGET: <!--:en-->
        $results = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_title LIKE '%<!--:en-->%' ORDER BY post_title ASC", ARRAY_A );
        if($results){
            foreach($results as $result){
                // English Target
                $string_title = explode('<!--:-->', (explode('<!--:en-->', $result['post_title'])[1]))[0];
                // Update post
                $my_post = array(
                    'ID'           => $result['ID'],
                    'post_title' => wp_slash($string_title),
                );
                // Update the post into the database
                wp_update_post( $my_post );

            }
        }
        $f_get_all_posts = get_posts( array(
            'numberposts'       => -1,
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'), // Any sometimes doesnt work for all...
            'fields' => 'ids',
            'order' => 'ASC',
            'post_type' => array('page', 'nav_menu_item', 'families', 'subfamilies', 'products', 'news', 'faqs', 'distributors')  // Any sometimes doesnt work for all, it depends if regisetred..
        ) );
        if($f_get_all_posts){
            foreach($f_get_all_posts as $f_post_id){
                // Lets update the post meta of all posts...
                $postmetas = get_post_meta( $f_post_id );
                // First we get all the meta values & keys from the current post.
                if($postmetas){
                    foreach($postmetas as $meta_key => $meta_value) {
                        $f_meta_val = $meta_value[0];
                        //  We do a loose comparison if the meta value has any keyword of en.
                        if(ronik_compare_like($f_meta_val , '[:en]')){
                            // English Target
                            $string = explode('[:zh]', (explode('[:en]', $f_meta_val)[1]))[0];
                            update_post_meta($f_post_id, $meta_key, wp_slash($string));
                        }
                    }
                }
                // Next lets update all the post title & posts_content...
                    //  We do a loose comparison if the meta value has any keyword of en.
                    // Post Title
                    $f_post_title = get_post_field( 'post_title', $f_post_id );
                    if(ronik_compare_like(get_post_field( 'post_title', $f_post_id ) , '[:en]')){
                        // English Target
                        $string_title_1 = explode('[:zh]', (explode('[:en]', $f_post_title)[1]))[0];
                        $f_post_title = wp_slash($string_title_1);
                    }
                    if(ronik_compare_like(get_post_field( 'post_title', $f_post_id ) , '[:en]')){
                        // English Target
                        $string_title_2 = explode('[:]', (explode('[:en]', $f_post_title)[1]))[0];
                        $f_post_title = wp_slash($string_title_2);
                    }
                    // Post Content
                    $f_post_content = get_post_field( 'post_content', $f_post_id );
                    if(ronik_compare_like(get_post_field( 'post_title', $f_post_id ) , '[:en]')){
                        // English Target
                        $string_content_1 = explode('[:zh]', (explode('[:en]', $f_post_content)[1]))[0];
                        $f_post_content = wp_slash($string_content_1);
                    }
                    if(ronik_compare_like(get_post_field( 'post_title', $f_post_id ) , '[:en]')){
                        // English Target
                        $string_content_2 = explode('[:]', (explode('[:en]', $f_post_content)[1]))[0];
                        $f_post_content = wp_slash($string_content_2);
                    }
                    // Update post
                    $my_post = array(
                        'ID'           => $f_post_id,
                        'post_title'   => $f_post_title,
                        'post_content' => $f_post_content,
                    );
                    // Update the post into the database
                    wp_update_post( $my_post );
            }
        } else {
            update_option( 'options_ronik-database-cleaner' , 'not-triggered' );
        }
    }

}










