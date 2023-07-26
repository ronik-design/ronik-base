<?php
/**
* Init Unused Media Migration.
*/
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

error_log(print_r('Final Results', true));


function recursive_delete($number){
	$post_type = get_field('page_media_cleaner_post_type_field_ronikdesign', 'options');	
	$select_post_type = $post_type;
	$post_mime_type = get_field('page_media_cleaner_post_mime_type_field_ronikdesign', 'options');			
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
			}
		}
	}
	$select_numberposts = get_field('page_media_cleaner_numberposts_field_ronikdesign', 'options');
	$offsetValue = $number * $select_numberposts;
	$select_post_status = array('publish', 'pending', 'draft', 'private', 'future');

	// Lets gather all the image id of the entire application.
		// We receive all the image id.
		error_log(print_r('Gather All Image ID of the entire website.' , true));
		$allimagesid = get_posts( array(
			'post_type' => 'attachment',
			// 'posts_per_page' => 2,
			'offset' => $offsetValue,
			// 'numberposts' => 20, // Do not add more then 150.
			// 'numberposts' => -1, // Do not add more then 150.
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

	// CHECKPOINT 1
		error_log(print_r('Get all image ids: '.count($main_image_ids) , true));
		error_log(print_r('CHECKPOINT 1' , true));
		// sleep(1);
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
		if($all_post_thumbnail_ids){
			$arr_checkpoint_1a = array_values(array_diff($main_image_ids, $all_post_thumbnail_ids) );
		} else{
			$arr_checkpoint_1a = $main_image_ids;
		}
		// Second let remove any image id that has a image attachment associated with it id.
		if($all_image_attachement_ids){
			$arr_checkpoint_1b = array_values(array_diff($arr_checkpoint_1a, $all_image_attachement_ids) );
		} else {
			$arr_checkpoint_1b = $arr_checkpoint_1a;
		}

	// CHECKPOINT COMPLETE
		error_log(print_r('Remove thumbnail id from bulk main id array: '.count($arr_checkpoint_1a) , true));
		error_log(print_r('Remove images attachment from bulk main id array: '.count($arr_checkpoint_1b) , true));
		error_log(print_r('CHECKPOINT 1 COMPLETE' , true));
		// sleep(1);

	// CHECKPOINT 2
		error_log(print_r('CHECKPOINT 2' , true));

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
						if($posts->ID){
							$wp_posts_gutenberg_image_array[] = $image_id;
						}
					}
				}
			}
		}
		
	// Lets remove any duplicated matches & set to new array.
		// First let remove the Gutenberg id from the bulk main id array
		if($wp_postsid_gutenberg_image_array){
			$arr_checkpoint_2a = array_values(array_diff($arr_checkpoint_1b, $wp_postsid_gutenberg_image_array) );
		} else{
			$arr_checkpoint_2a = $arr_checkpoint_1b;
		}
		if($wp_posts_gutenberg_image_array){
			$arr_checkpoint_2b = array_values(array_diff($arr_checkpoint_2a, $wp_posts_gutenberg_image_array) );
		} else {
			$arr_checkpoint_2b = $arr_checkpoint_2a;
		}
		// 'reindex' array to cleanup...
		$arr_checkpoint_2c = array_values(array_filter($arr_checkpoint_2b)); 

	// CHECKPOINT COMPLETE
		error_log(print_r('Remove Gutenberg id from bulk main id array: '.count($arr_checkpoint_2a) , true));
		error_log(print_r('Remove Gutenberg Image from bulk main id array: '.count($arr_checkpoint_2b) , true));
		error_log(print_r('Reindex Array: '.count($arr_checkpoint_2c) , true));
		error_log(print_r('CHECKPOINT 2 COMPLETE' , true));
		// sleep(1);

	// CHECKPOINT 3
		error_log(print_r('CHECKPOINT 3' , true));
	
		$wp_postsmeta_acf_repeater_image_array = array();
		$wp_postsmeta_acf_repeater_image_url_array = array();
		if($arr_checkpoint_2c){
			foreach($arr_checkpoint_2c as $image_id){

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
		if($wp_postsmeta_acf_repeater_image_array){
			$arr_checkpoint_3a = array_values(array_diff($arr_checkpoint_2c, $wp_postsmeta_acf_repeater_image_array) );
		} else{
			$arr_checkpoint_3a = $arr_checkpoint_2c;
		}
		if($wp_postsmeta_acf_repeater_image_url_array){
			$arr_checkpoint_3b = array_values(array_diff($arr_checkpoint_3a, $wp_postsmeta_acf_repeater_image_url_array) );
		} else{
			$arr_checkpoint_3b = $arr_checkpoint_3a;
		}

	// CHECKPOINT COMPLETE
		error_log(print_r('Postmeta for any image ids in the acf serialized array: '.count($arr_checkpoint_3a) , true));
		error_log(print_r('Postmeta for any image url in the acf serialized array: '.count($arr_checkpoint_3b) , true));
		error_log(print_r('CHECKPOINT 3 COMPLETE' , true));
		// sleep(1);

	// CHECKPOINT 4
	error_log(print_r('CHECKPOINT 4' , true));

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
		if($wp_postsmeta_acf_array){
			$arr_checkpoint_4a = array_values(array_diff($arr_checkpoint_3b, $wp_postsmeta_acf_array) );
		} else{
			$arr_checkpoint_4a = $arr_checkpoint_3b;
		}
		$arr_checkpoint_4b = array_values(array_filter($arr_checkpoint_4a)); // 'reindex' array to cleanup...


	// CHECKPOINT COMPLETE
	error_log(print_r('Postmeta for any image ids in the acf array: '.count($arr_checkpoint_4a) , true));
	error_log(print_r('Reindex: '.count($arr_checkpoint_4b) , true));
	error_log(print_r('CHECKPOINT 4 COMPLETE' , true));
	// sleep(1);

	// CHECKPOINT 5
		error_log(print_r('CHECKPOINT 5' , true));
								
		$wp_infiles_array = array();
		if($arr_checkpoint_4b){
			foreach($arr_checkpoint_4b as $image_id){
				$wp_infiles_array[] = rbp_receiveAllFiles_ronikdesigns($image_id);						
			}	
		}	

	// This part is critical we check all the php files within the active theme directory.
		if($wp_infiles_array){
			$arr_checkpoint_5a = array_values(array_diff($arr_checkpoint_4b, $wp_infiles_array) );
		} else{
			$arr_checkpoint_5a = $arr_checkpoint_4b;
		}

	// CHECKPOINT COMPLETE
		error_log(print_r('Check all the php files within the active theme directory: '.count($arr_checkpoint_5a) , true));
		error_log(print_r('CHECKPOINT 5 COMPLETE' , true));
		// sleep(1);

	return array_values(array_filter($arr_checkpoint_5a)); // 'reindex' array to cleanup...
}
// Warning this script will slow down the entire server. Use only a small amount at a time.
$f_offset_value_end = get_field('page_media_cleaner_offset_field_ronikdesign', 'options');
$f_offset_value_start = $f_offset_value_end - 5;
$image_array = array();
foreach ( range( $f_offset_value_start, $f_offset_value_end ) as $number) {
	$image_array[] = recursive_delete($number);
}
// remove empty and re-arrange image array
$image_array = array_values(array_filter($image_array));
$image_array = array_unique(array_merge(...$image_array));


error_log(print_r(memory_get_usage(true), true));
error_log(print_r(memory_get_usage(), true));

error_log(print_r('Final Results', true));
error_log(print_r($image_array, true));
if($image_array){
	// Get the array count..
	update_option( 'options_page_media_cleaner_field' , count($image_array) );
	foreach( $image_array as $key => $f_result ){
		update_option('options_page_media_cleaner_field_' . $key . '_file_size', ((filesize(get_attached_file($f_result)))/1000)/1000);
		update_option('options_page_media_cleaner_field_' . $key . '_image_id', $f_result);
		update_option('options_page_media_cleaner_field_' . $key . '_image_url', get_attached_file($f_result) );
		update_option('options_page_media_cleaner_field_' . $key . '_thumbnail_preview', $f_result);

		if( $f_result == end($image_array) ){
			// Sleep for 2 seconds...
			// sleep(1);
			// Send sucess message!
			wp_send_json_success('Done');
		}
	}
} else {
	// If no rows are found send the error message!
	wp_send_json_error('No rows found!');
}
