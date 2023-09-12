<?php 
/**
* Init Remove Unused Media .
*/

$f_media_cleaner = get_field('page_media_cleaner_field', 'options');
if($f_media_cleaner){
	foreach($f_media_cleaner as $key => $media_cleaner){

		// First lets copy the full image to the ronikdetached folder.
		$upload_dir   = wp_upload_dir();
		$link = wp_get_attachment_image_url( $media_cleaner['image_id'], 'full' );
		$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $link);
		$file_name = explode('/', $link);	
		
		//Year in YYYY format.
		$year = date("Y");
		//Month in mm format, with leading zeros.
		$month = date("m");
		//Day in dd format, with leading zeros.
		$day = date("d");
		//The folder path for our file should be YYYY/MM/DD
		$directory = dirname(__FILE__, 2).'/ronikdetached/'."$year/$month/$day/";
		//If the directory doesn't already exists.
		if(!is_dir($directory)){
			//Create our directory.
			mkdir($directory, 0777, true);
		}
		copy($file_path , $directory.end($file_name));

		// Delete attachment from database only, not file
		$delete_attachment = wp_delete_attachment( $media_cleaner['image_id'] , true);
		if($delete_attachment){
			//Delete attachment file from disk
			unlink( get_attached_file( $media_cleaner['image_id'] ) );
			error_log(print_r('File Deleted', true));
			update_option('options_page_media_cleaner_field_' . $key . '_file_size',  '');
			update_option('options_page_media_cleaner_field_' . $key . '_image_id',  '');
			update_option('options_page_media_cleaner_field_' . $key . '_image_url', '' );
			update_option('options_page_media_cleaner_field_' . $key . '_thumbnail_preview', '');
		}

		if( $media_cleaner == end($f_media_cleaner) ){
			// Get the array count..
			update_option( 'options_page_media_cleaner_field' , '' );
			// sleep(1);				
			// Send sucess message!
			wp_send_json_success('Done');
		}

	}
} else {
	// If no rows are found send the error message!
	wp_send_json_error('No rows found!');
}
// Send sucess message!
wp_send_json_success('Done');