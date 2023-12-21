<?php 
/**
* Init Remove Unused Media .
*/

// $rbp_media_cleaner_counter = get_option('rbp_media_cleaner_counter');

$rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');

if($rbp_media_cleaner_media_data){
	foreach($rbp_media_cleaner_media_data as $rbp_data_id){
		// First lets copy the full image to the ronikdetached folder.
		$upload_dir   = wp_upload_dir();
		$link = wp_get_attachment_image_url( $rbp_data_id, 'full' );
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
		$delete_attachment = wp_delete_attachment( $rbp_data_id , true);
		if($delete_attachment){
			//Delete attachment file from disk
			if(get_attached_file( $rbp_data_id )){
				unlink( get_attached_file( $rbp_data_id ) );
			}
			error_log(print_r('File Deleted', true));
		}
		if( $rbp_data_id == end($rbp_media_cleaner_media_data) ){
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

// Simple function that resets everything before we continue processing all the files..
databaseScannerMedia__cleaner();
// Throttle after cleaner.
sleep(1);

// Send sucess message!
wp_send_json_success('Cleaner-Done');