<?php 
/**
* Init Remove Unused Media .
*/

$rbp_media_cleaner_counter = get_option('rbp_media_cleaner_counter');

if($rbp_media_cleaner_counter){
	$numbers = range(0, ($rbp_media_cleaner_counter));

	foreach($numbers as $number){
		$rbp_media_cleaner_image_id = get_option('rbp_media_cleaner_'.$number.'_image_id');
		if($rbp_media_cleaner_image_id){
			// First lets copy the full image to the ronikdetached folder.
			$upload_dir   = wp_upload_dir();
			$link = wp_get_attachment_image_url( $rbp_media_cleaner_image_id, 'full' );
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
			if($file_path){
				copy($file_path , $directory.end($file_name));
			}
			// Delete attachment from database only, not file
			$delete_attachment = wp_delete_attachment( $rbp_media_cleaner_image_id , true);
			if($delete_attachment){
				//Delete attachment file from disk
				if(get_attached_file( $rbp_media_cleaner_image_id )){
					unlink( get_attached_file( $rbp_media_cleaner_image_id ) );
				}
				error_log(print_r('File Deleted', true));
				update_option('options_page_media_cleaner_field_' . $number . '_file_size',  '');
				update_option('options_page_media_cleaner_field_' . $number . '_image_id',  '');
				update_option('options_page_media_cleaner_field_' . $number . '_image_url', '' );
				update_option('options_page_media_cleaner_field_' . $number . '_thumbnail_preview', '');
			}

			if( $number == end($numbers) ){
				// Get the array count..
				// update_option( 'options_page_media_cleaner_field' , '' );	
				// Simple function that resets everything before we continue processing all the files..
				databaseScannerMedia__cleaner();
				// Throttle after cleaner.
				sleep(1);
				// Send sucess message!
				wp_send_json_success('Cleaner-Done');
			}
		}
	}
} else {
	// If no rows are found send the error message!
	wp_send_json_error('No rows found!');
}

// Simple function that resets everything before we continue processing all the files..
databaseScannerMedia__cleaner();
// Throttle after cleaner.
sleep(1);

// Send sucess message!
wp_send_json_success('Cleaner-Done');