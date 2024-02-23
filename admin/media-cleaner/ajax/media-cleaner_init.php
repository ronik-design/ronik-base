<?php
/**
* Init Unused Media Migration.
*/
	// Helper Guide
	$helper = new RonikBaseHelper;

	if(isset($_POST['increment'])){
		$increment = $_POST['increment'];
		// Set post status.
		$select_post_status = array('publish', 'pending', 'draft', 'private', 'future');
		// For now we get the page type.
		// $select_post_type = "post ,page ,user_request ,segments ,networks ,programs ,articles ,playlists ,credits ,programming ,contact-form ,videos";

		// WARNING:
		$post_types = get_post_types( array(), 'names', 'and' );
		// We remove a few of the deafult types to help with speed cases..
		$post_types_without_defaults = array_diff($post_types,
			array(
				'attachment',
				'revision',
				'nav_menu_item',
				'custom_css',
				'customize_changeset',
				'oembed_cache',
				'wp_block',
				'wp_template',
				'wp_template_part',
				'wp_global_styles',
				'wp_navigation',
				'acf-post-type',
				'acf-taxonomy',
				'acf-field-group',
				'acf-field'
			)
		);
		$post_types_arrays = array();
		foreach($post_types_without_defaults as $key => $value) {
			array_push($post_types_arrays, $value);
		}
		$select_post_type = implode(",", $post_types_arrays);

		// Mime_type.
		$select_attachment_type = cleaner_post_mime_type($_POST['mime_type']);
		// Overall media counter...
		$throttle_detector =  databaseScannerMedia__allMedia(array('count',$select_attachment_type));
		// Set numberposts to a number that wont destroy the server resources.
		$select_numberposts = 35;
		// We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
		$maxIncrement = ceil($throttle_detector/$select_numberposts);
		if($increment == 0){
			// Simple function that resets everything before we continue processing all the files..
			databaseScannerMedia__cleaner();
			// Throttle after cleaner.
			sleep(1);
		}
		if($increment >= $maxIncrement){
			update_option('rbp_media_cleaner_increment', 1 );
			$response = array(
				"response" => "Reload",
				"pageCounter" => $increment,
				"pageTotalCounter" => $maxIncrement,
			);
			wp_send_json_success($response);
			// wp_send_json_success('Reload');
			die();
		}
	} else {
		$helper->ronikdesigns_write_log_devmode('Increment error.', 'critical');
		wp_send_json_error('Increment error.');
		die();
	}


	// rmc_recursive_media_scanner
	function rmc_recursive_media_scanner($maxIncrement, $increment, $select_attachment_type, $select_post_type, $select_numberposts, $select_post_status){
		// Helper Guide
		$helper = new RonikBaseHelper;

		$offsetValue = $increment * $select_numberposts;
		// Lets gather all the image id of the entire application.
			// We receive all the image id.
			$helper->ronikdesigns_write_log_devmode('Gather All Image ID of the entire website.', 'low');
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

			$helper->ronikdesigns_write_log_devmode('KEVIN $offsetValue', 'low');
			$helper->ronikdesigns_write_log_devmode($offsetValue, 'low');
			$helper->ronikdesigns_write_log_devmode('KEVIN $increment', 'low');
			$helper->ronikdesigns_write_log_devmode($increment, 'low');

		// CHECKPOINT 1
			// error_log(print_r('CHECKPOINT 1: Check for post thumbnail && image attachement.' , true));
			$helper->ronikdesigns_write_log_devmode('CHECKPOINT 1: Check for post thumbnail && image attachement.', 'low');
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
			$helper->ronikdesigns_write_log_devmode('CHECKPOINT 1 COMPLETE: Check for post thumbnail && image attachement', 'low');
			$helper->ronikdesigns_write_log_devmode( count($arr_checkpoint_1b), 'low');
			$helper->ronikdesigns_write_log_devmode( $arr_checkpoint_1b, 'low');


		// CHECKPOINT 2
			$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 2: Check image id within all posts. Primarily for Gutenberg Block Editor.', 'low');

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
			$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 2 COMPLETE: Check image id within all posts. Primarily for Gutenberg Block Editor.', 'low');
			$helper->ronikdesigns_write_log_devmode(  count($arr_checkpoint_2b), 'low');
			$helper->ronikdesigns_write_log_devmode( $arr_checkpoint_2b, 'low');

		// CHECKPOINT 3
			$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 3: check all the postmeta for any image ids in the acf serialized array. AKA any repeater fields or gallery fields.', 'low');

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
			$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 3 COMPLETE: Check all the postmeta for any image ids in the acf serialized array. AKA any repeater fields or gallery fields.', 'low');
			$helper->ronikdesigns_write_log_devmode( count($arr_checkpoint_3b), 'low');
			$helper->ronikdesigns_write_log_devmode( $arr_checkpoint_3b, 'low');


		// CHECKPOINT 4
		$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 4: Check all the postmeta value for the id.', 'low');
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
		$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 4 COMPLETE: Check all the postmeta value for the id.', 'low');
		$helper->ronikdesigns_write_log_devmode( count($arr_checkpoint_4a), 'low');
		$helper->ronikdesigns_write_log_devmode( $arr_checkpoint_4a, 'low');


		// CHECKPOINT 5
			$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 5: Check all the php files within the active theme directory.', 'low');

			$wp_infiles_array = array();
			if($arr_checkpoint_4a){
				foreach($arr_checkpoint_4a as $image_id){
					$wp_infiles_array[] = rmc_receiveAllFiles_ronikdesigns(get_theme_file_path(), $image_id);
				}
			}

		// This part is critical we check all the php files within the active theme directory.
			$arr_checkpoint_5a = cleaner_compare_array_diff($arr_checkpoint_4a, $wp_infiles_array);


		// This part was critical but not anymore. We assume that all file that have post parents are attached but they are not... so let ignore this for now..
			// $arr_has_post_parent = array();
			// if($arr_checkpoint_5a){
			// 	foreach($arr_checkpoint_5a as $arr_checkpoint_id){
			// 		if(get_post_parent($arr_checkpoint_id)){
			// 			$arr_has_post_parent[] = $arr_checkpoint_id;
			// 		}
			// 	}
			// }
			// $arr_checkpoint_5b = cleaner_compare_array_diff($arr_checkpoint_5a, $arr_has_post_parent);
			$arr_checkpoint_5b = $arr_checkpoint_5a;


		// CHECKPOINT COMPLETE
			$helper->ronikdesigns_write_log_devmode( 'CHECKPOINT 5 COMPLETE: Check all the php files within the active theme directory.', 'low');
			$helper->ronikdesigns_write_log_devmode( count($arr_checkpoint_5b), 'low');
			$helper->ronikdesigns_write_log_devmode( $arr_checkpoint_5b, 'low');


		return array_values(array_filter($arr_checkpoint_5b)); // 'reindex' array to cleanup...
	}

	// Pretty much the return will return all id.
	sleep(1);
		$image_array[] = rmc_recursive_media_scanner($maxIncrement, $increment, $select_attachment_type, $select_post_type, $select_numberposts, $select_post_status);
		// remove empty and re-arrange image array
		$image_array2 = array_values(array_filter($image_array));
		$image_array3 = array_unique(array_merge(...$image_array2));

		error_log(print_r('Final Results', true));
		error_log(print_r($image_array3, true));
		$helper->ronikdesigns_write_log_devmode( 'Final Results', 'low');
		if($image_array3){
			// Get the array count..
			update_option( 'rbp_media_cleaner_counter' , count($image_array3) );

			// error_log(print_r('Final Results', true));
			$helper->ronikdesigns_write_log_devmode( 'Final Results', 'low');


			$rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');
			if(!$rbp_media_cleaner_media_data){
				$rbp_media_cleaner_media_data = array();
			}

			foreach( $image_array3 as $key => $f_result ){
				$data = wp_get_attachment_metadata( $f_result ); // get the data structured

				$data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_true';  // change the values you need to change
				$rbp_media_cleaner_media_data[] = $f_result;

				// if( isset($data['rbp_media_cleaner_isdetached']) && ($data['rbp_media_cleaner_isdetached'] !== 'rbp_media_cleaner_isdetached_temp-saved') ){
				// 	$data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_true';  // change the values you need to change
				// 	$rbp_media_cleaner_media_data[] = $f_result;
				// }

				wp_update_attachment_metadata( $f_result, $data );  // save it back to the db
				update_option('rbp_media_cleaner_sync-time', date("m/d/Y h:ia"));
				update_option('rbp_media_cleaner_media_data', $rbp_media_cleaner_media_data);

				if( $f_result == end($image_array3) ){
					error_log(print_r("response DONE", true));

					// // Send sucess message!
					// $f_increment = $increment+1;
					// update_option( 'rbp_media_cleaner_increment', $f_increment );
					// // Sleep for 1 seconds...
					// sleep(1);
					$response = array(
						"response" => "Done",
						"pageCounter" => $increment,
						"pageTotalCounter" => $maxIncrement,
					);
					wp_send_json_success($response);
					// wp_send_json_success('Done');
				}
			}
		} else {
			// If no rows are found send the error message!
			// update_option( 'rbp_media_cleaner_increment', 0 );
			$f_increment = $increment+1;
			// update_option( 'rbp_media_cleaner_increment', $f_increment );

			// Sleep for 1 seconds...
			sleep(1);
			$response = array(
				"response" => "Done",
				"pageCounter" => $f_increment,
				"pageTotalCounter" => $maxIncrement,
			);
			wp_send_json_success($response);

			// wp_send_json_success('Done');

			// wp_send_json_error('No rows found! sss');
		}





?>
