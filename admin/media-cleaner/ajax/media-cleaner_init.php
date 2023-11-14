<?php
/**
* Init Unused Media Migration.
*/

	if(isset($_POST['increment'])){
		$increment = $_POST['increment'];
		// Set post status.
		$select_post_status = array('publish', 'pending', 'draft', 'private', 'future');
		// For now we get the page type.
		$select_post_type = "page";
		// Mime_type.
		$select_attachment_type = cleaner_post_mime_type($_POST['mime_type']);
		// Overall media counter...
		$throttle_detector =  databaseScannerMedia__allMedia(array('count',$select_attachment_type));
		// Set numberposts to a number that wont destroy the server resources.
		$select_numberposts = 100;
		// We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
		$maxIncrement = ceil($throttle_detector/$select_numberposts); 
		if($increment == 0){
			// Simple function that resets everything before we continue processing all the files..
			databaseScannerMedia__cleaner();
			// Throttle after cleaner.
			sleep(1);
		}
		if($increment > $maxIncrement){
			update_option('rbp_media_cleaner_increment', 1 );
			wp_send_json_success('Reload');
			die();
		}
	} else {
		wp_send_json_error('Increment error.');
		die();
	}

	
	// rmc_recursive_media_scanner
	function rmc_recursive_media_scanner($increment, $select_attachment_type, $select_post_type, $select_numberposts, $select_post_status){		
		$offsetValue = $increment * $select_numberposts;
		// Lets gather all the image id of the entire application.
			// We receive all the image id.
			error_log(print_r('Gather All Image ID of the entire website.' , true));
			$allimagesid = get_posts( array(
				'post_type' => 'attachment',
				'offset' => $offsetValue,
				'numberposts' => $select_numberposts,
				'fields' => 'ids',
				'post_mime_type' => $select_attachment_type,
				'orderby' => 'date',
				'order'  => 'DESC',
			));
			// This will allow us to collect all the image ids.
			$main_image_ids = array();
			if ($allimagesid) {
				foreach ($allimagesid as $imageID){
					$main_image_ids[] = $imageID;
				}
			}
			error_log(print_r( count($main_image_ids) , true));
			error_log(print_r( $main_image_ids , true));


		// CHECKPOINT 1
			error_log(print_r('CHECKPOINT 1: Check for post thumbnail && image attachement.' , true));
		// Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
			$get_all_post_pages = get_posts( array(
				'post_type' => $select_post_type,
				'numberposts' => -1,
				'fields' => 'ids',
				'post_status'  => $select_post_status,
				'orderby' => 'date',
				'order'  => 'DESC',
			));
			$all_post_thumbnail_ids = array();
			$all_image_attachement_ids = array();
			if ($get_all_post_pages) {
				foreach ($get_all_post_pages as $pageID){
					// Critical part is we detect if the attachment has any a parent that matches the id.
					$attachments = get_posts( array(
						'post_type' => 'attachment',
						'numberposts' => -1,
						'fields' => 'ids',
						'post_parent' => $pageID,
						'post_mime_type' => $select_attachment_type,
						'orderby' => 'date',
						'order'  => 'DESC',
					));
					if ($attachments) {
						foreach ($attachments as $attachmentID){
							$all_image_attachement_ids[] = $attachmentID;
						}
					}
					if( get_post_thumbnail_id( $pageID ) ){
						$all_post_thumbnail_ids[] = get_post_thumbnail_id( $pageID );
					}
				}
			}
	
		// Lets remove any duplicated matches & set to new array.
			// First let remove the thumbnail id from the bulk main id array
			$arr_checkpoint_1a = cleaner_compare_array_diff($main_image_ids, $all_post_thumbnail_ids);
			$arr_checkpoint_1b = cleaner_compare_array_diff($arr_checkpoint_1a, $all_image_attachement_ids);
			// $arr_checkpoint_1c = array_values(array_filter($arr_checkpoint_1b));

		// CHECKPOINT COMPLETE
			error_log(print_r('CHECKPOINT 1 COMPLETE: Check for post thumbnail && image attachement' , true));
			error_log(print_r( count($arr_checkpoint_1b) , true));
			error_log(print_r( $arr_checkpoint_1b , true));


		// CHECKPOINT 2
			error_log(print_r('CHECKPOINT 2: Check image id within all posts. Primarily for Gutenberg Block Editor.' , true));
			$wp_postsid_gutenberg_image_array = array();
			$wp_posts_gutenberg_image_array = array();
			if($arr_checkpoint_1b){
				foreach($arr_checkpoint_1b as $a => $image_id){
					// This will search for the image id within the posts. This is primarily for Gutenberg Block Editor. The image id is stored within the post content...
					$f_postsid = get_posts(
						array(
							'post_status'  => $select_post_status,
							'post_type' => $select_post_type,
							'fields' => 'ids',
							'posts_per_page' => -1,
							's'  => ':'.$image_id,
							'orderby' => 'date',
							'order'  => 'DESC',
						),
					);
					if($f_postsid){
						foreach($f_postsid as $b => $postsid){
							$wp_postsid_gutenberg_image_array[] = $image_id;
						}
					}
					// lets get the attached file name. Search through the wp_posts data table. This is not ideal, but is the only good way to search for imageid for gutenberg blocks. Plus any images that are inserted into posts manually.
					$f_attached_file = get_attached_file( $image_id );
					$pieces = explode('/', $f_attached_file ) ;
					$f_postsattached = get_posts( array(
						'post_status'  => $select_post_status,
						'post_type' => $select_post_type,
						'fields' => 'ids',
						'posts_per_page' => -1,
						's'  => end($pieces),
						'orderby' => 'date',
						'order'  => 'DESC',
					) );
					if($f_postsattached){
						foreach($f_postsattached as $key => $posts){
							error_log(print_r($posts, true));
							if($posts){
								$wp_posts_gutenberg_image_array[] = $image_id;
							}
						}
					}
				}
			}
		// Lets remove any duplicated matches & set to new array.
			// First let remove the Gutenberg id from the bulk main id array
			$arr_checkpoint_2a = cleaner_compare_array_diff($arr_checkpoint_1b, $wp_postsid_gutenberg_image_array);
			$arr_checkpoint_2b = cleaner_compare_array_diff($arr_checkpoint_2a, $wp_posts_gutenberg_image_array);

		// CHECKPOINT COMPLETE
			error_log(print_r('CHECKPOINT 2 COMPLETE: Check image id within all posts. Primarily for Gutenberg Block Editor.' , true));
			error_log(print_r( count($arr_checkpoint_2b) , true));
			error_log(print_r( $arr_checkpoint_2b , true));

		// CHECKPOINT 3
			error_log(print_r('CHECKPOINT 3: check all the postmeta for any image ids in the acf serialized array. AKA any repeater fields or gallery fields.' , true));
			$wp_postsmeta_acf_repeater_image_array = array();
			$wp_postsmeta_acf_repeater_image_url_array = array();
			if($arr_checkpoint_2b){
				foreach($arr_checkpoint_2b as $image_id){
					// This part is critical we check all the postmeta for any image ids in the acf serialized array. AKA any repeater fields or gallery fields.
					$f_posts = get_posts( array(
						'fields' => 'ids',
						'post_type' => $select_post_type,
						'post_status'  => $select_post_status,
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
							'value' => sprintf(':"%s";', $image_id),
							'compare' => 'LIKE',
							)
						),
						'orderby' => 'date',
						'order'  => 'DESC',
					) );
					if($f_posts){
						foreach($f_posts as $key => $posts){
							if($posts){
								$wp_postsmeta_acf_repeater_image_array[] = $image_id;
							}
						}
					}
					// This part is critical we check all the postmeta for any image ids in the acf serialized array. AKA any repeater fields or gallery fields.
					$f_posts_2 = get_posts( array(
						'fields' => 'ids',
						'post_type' => $select_post_type,
						'post_status'  => $select_post_status,
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
								'value' => sprintf(':"%s";', get_attached_file($image_id)),
								'compare' => 'LIKE',
							)
						),
						'orderby' => 'date',
						'order'  => 'DESC',
					));
					if($f_posts_2){
						foreach($f_posts_2 as $key => $posts){
							if($posts){
								$wp_postsmeta_acf_repeater_image_url_array[] = $image_id;
							}
						}
					}
				}
			}
		// Lets remove any duplicated matches & set to new array.
			// First let remove the Gutenberg id from the bulk main id array
			$arr_checkpoint_3a = cleaner_compare_array_diff($arr_checkpoint_2b, $wp_postsmeta_acf_repeater_image_array);
			$arr_checkpoint_3b = cleaner_compare_array_diff($arr_checkpoint_3a, $wp_postsmeta_acf_repeater_image_url_array);

		// CHECKPOINT COMPLETE
			error_log(print_r('CHECKPOINT 3 COMPLETE: Check all the postmeta for any image ids in the acf serialized array. AKA any repeater fields or gallery fields.' , true));
			error_log(print_r( count($arr_checkpoint_3b) , true));
			error_log(print_r( $arr_checkpoint_3b , true));


		// CHECKPOINT 4
		error_log(print_r('CHECKPOINT 4: Check all the postmeta value for the id.' , true));
			$wp_postsmeta_acf_array = array();
			if($arr_checkpoint_3b){
				foreach($arr_checkpoint_3b as $image_id){
					$f_posts = get_posts( array(
						'fields' => 'ids',
						'post_type' => $select_post_type,
						'post_status'  => $select_post_status,
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
							'value' => $image_id,
							'compare' => '==',
							)
						),
						'orderby' => 'date',
						'order'  => 'DESC',
					));
					if($f_posts){
						foreach($f_posts as $key => $posts){
							if($posts){
								$wp_postsmeta_acf_array[] = $image_id;
							}
						}
					}
				}
			}
		// This part is critical we check all the postmeta for any image ids in the meta value
			$arr_checkpoint_4a = cleaner_compare_array_diff($arr_checkpoint_3b, $wp_postsmeta_acf_array);
		// CHECKPOINT COMPLETE
		error_log(print_r('CHECKPOINT 4 COMPLETE: Check all the postmeta value for the id.' , true));
		error_log(print_r( count($arr_checkpoint_4a) , true));
		error_log(print_r( $arr_checkpoint_4a , true));


		// CHECKPOINT 5
			error_log(print_r('CHECKPOINT 5: Check all the php files within the active theme directory.' , true));
			$wp_infiles_array = array();
			if($arr_checkpoint_4a){
				foreach($arr_checkpoint_4a as $image_id){
					$wp_infiles_array[] = rmc_receiveAllFiles_ronikdesigns(get_theme_file_path(), $image_id);
				}
			}
		// This part is critical we check all the php files within the active theme directory.
			$arr_checkpoint_5a = cleaner_compare_array_diff($arr_checkpoint_4a, $wp_infiles_array);


		// CHECKPOINT COMPLETE
			error_log(print_r('CHECKPOINT 5 COMPLETE: Check all the php files within the active theme directory.' , true));
			error_log(print_r( count($arr_checkpoint_5a) , true));
			error_log(print_r( $arr_checkpoint_5a , true));

		return array_values(array_filter($arr_checkpoint_5a)); // 'reindex' array to cleanup...
	}

	// Pretty much the return will return all id.
	sleep(1);
		$image_array[] = rmc_recursive_media_scanner($increment, $select_attachment_type, $select_post_type, $select_numberposts, $select_post_status);
		// remove empty and re-arrange image array
		$image_array = array_values(array_filter($image_array));
		$image_array = array_unique(array_merge(...$image_array));
	
		error_log(print_r('Final Results', true));
		// error_log(print_r($image_array, true));
		if($image_array){
			// Get the array count..
			update_option( 'rbp_media_cleaner_counter' , count($image_array) );
	
			foreach( $image_array as $key => $f_result ){
				$data = wp_get_attachment_metadata( $f_result ); // get the data structured
				$data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_true';  // change the values you need to change
				wp_update_attachment_metadata( $f_result, $data );  // save it back to the db
				update_option('rbp_media_cleaner_sync-time', date("m/d/Y h:ia"));
				update_option('rbp_media_cleaner_' . $key . '_file_size', ((filesize(get_attached_file($f_result)))));
				update_option('rbp_media_cleaner_' . $key . '_image_id', $f_result);
				update_option('rbp_media_cleaner_' . $key . '_image_url', get_attached_file($f_result) );
				update_option('rbp_media_cleaner_' . $key . '_thumbnail_preview', $f_result);
	
				if( $f_result == end($image_array) ){
					// // Send sucess message!
					// $f_increment = $increment+1;
					// update_option( 'rbp_media_cleaner_increment', $f_increment );
					// // Sleep for 1 seconds...
					// sleep(1);
					wp_send_json_success('Done');
				}
			}
		} else {
			// If no rows are found send the error message!
			// update_option( 'rbp_media_cleaner_increment', 0 );
			$f_increment = $increment+1;
			// update_option( 'rbp_media_cleaner_increment', $f_increment );
	
			// Sleep for 1 seconds...
			sleep(1);
			wp_send_json_success('Done');
	
			// wp_send_json_error('No rows found! sss');
		}





?>
