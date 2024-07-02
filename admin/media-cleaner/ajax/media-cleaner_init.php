<?php
/**
* Init Unused Media Migration.
*/
	// Helper Guide
	$helper = new RonikBaseHelper;
	$rbp_media_cleaner_cron_last = get_option('rbp_media_cleaner_cron_last-ran' , 'false');

	// This is a fallback solution incase user leaves the sync before completion.
	// Or if some odd reason something cancels the sync process.
	if($rbp_media_cleaner_cron_last){
		if (strtotime('-1 day') > strtotime($rbp_media_cleaner_cron_last)) {
			error_log(print_r( 'Cron Reset', true));
			update_option('rbp_media_cleaner_sync_running', 'not-running');
			delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
		}
	}


	sleep(1);






	// Detect if the sync is already running. 
	$rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running' , 'not-running');
	// Lets get the progress bar.
	$transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' );
	// Lets get the finalized image id list.
	$transient_rmc_media_cleaner_media_data_collectors_image_id_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );

	error_log(print_r( 'Running First Time: ' . $rbp_media_cleaner_sync_running, true));







	// If running we return the inprogress and we send the stats..
	if($rbp_media_cleaner_sync_running == 'running'){
		error_log(print_r( 'Running', true));

		// We kill the application.
		$response = array(
			"syncType" => "All ready running",
			"sync" => $transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress,
			"response" => "Collector-Sync-inprogress-".rand(),
		);
		wp_send_json_success($response);
		die();
	}








	// Lets double check the progress is set and is not DONE..
	if(empty($transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress) || $transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress !== 'DONE'){
		error_log(print_r( 'Sync is progress: ' . $transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress, true));
		$transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' );
		// If media id is not in sync we have to recall and based on site size it can take between 5 minutes to 30 minutes.
		$this->rmc_media_sync();
		error_log(print_r( 'TEST', true));
		sleep(1);
		if( $transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress == 'DONE'  ){
			// We kill the application.
			$response = array(
				"response" => "Collector-Sync-done",
			);
			wp_send_json_success($response);
			die();
		}

		// We kill the application.
		$response = array(
			"syncType" => "Sync is not done.",
			"sync" => '0%',
			"response" => "Collector-Sync-inprogress-".rand(),
		);
		wp_send_json_success($response);
		die();
	}









	// Last Check if the finalized image id list is set.
	if(!$transient_rmc_media_cleaner_media_data_collectors_image_id_array || (isset($_POST['sync']) && $_POST['sync'] == 'inprogress')){
			$transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' );
			// If media id is not in sync we have to recall and based on site size it can take between 5 minutes to 30 minutes.
			$this->rmc_media_sync();
			sleep(1);
			if( $transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress == 'DONE'  ){
				// We kill the application.
				$response = array(
					"response" => "Collector-Sync-done",
				);
				wp_send_json_success($response);
				die();
			}
			error_log(print_r( 'Collector-Sync-done ', true));
	}








	error_log(print_r($_POST['increment'] , true));







		
		// Make sure increment is set and sync is not in progress!
		if(isset($_POST['increment'])){
			$increment = $_POST['increment'];
			/**
				* First we check to see if the rmc_media_cleaner_media_data_collectors_image_id_array is set. 
				* If not we run the rmc_media_sync!
			*/
			if( ! empty( $transient_rmc_media_cleaner_media_data_collectors_image_id_array ) ) {
				$rmc_media_cleaner_media_data_collectors_image_id_array = $transient_rmc_media_cleaner_media_data_collectors_image_id_array;
			} else {
				$transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_progress' );

				// We kill the application.
				$response = array(
					"syncType" => "Sync is not done.",
					"sync" => $transient_rmc_media_cleaner_media_data_collectors_image_id_array_progress,
					"response" => "Collector-Sync-inprogress-".rand(),
				);
				wp_send_json_success($response);
				die();
			}



			error_log(print_r('HELP' , true));
			error_log(print_r($_POST['mime_type'], true));


			$rmc_media_cleaner_media_data_collectors_image_id_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
			// Wait 1 Second.
				sleep(1);
			// Mime types
				$select_attachment_type = cleaner_post_mime_type($_POST['mime_type']);
			// Reformat all the ids to only show the desired mimetype!
				$rbp_media_cleaner_media_data_collector_refomat_specific_id = array();
				if ( $_POST['mime_type'] !== 'all' ) {
					foreach ($rmc_media_cleaner_media_data_collectors_image_id_array as $image_id_array){			
						if(array_search( get_post_mime_type($image_id_array) , $select_attachment_type)){
							$rbp_media_cleaner_media_data_collector_refomat_specific_id[] = $image_id_array;
						}
					}
					
				} else {
					// If all we just reassign the variables..
					$rbp_media_cleaner_media_data_collector_refomat_specific_id = $rmc_media_cleaner_media_data_collectors_image_id_array;
				}
			// Overall media counter...
				$throttle_detector = count($rbp_media_cleaner_media_data_collector_refomat_specific_id);
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
			wp_send_json_error('Increment error.');
			die();
		}
		$rmc_recursive_media_scanner_results_final = $rbp_media_cleaner_media_data_collector_refomat_specific_id;
		if($rmc_recursive_media_scanner_results_final){
			// Get the array count..
			update_option( 'rbp_media_cleaner_counter' , count($rmc_recursive_media_scanner_results_final) );
			foreach( $rmc_recursive_media_scanner_results_final as $key => $rmc_recursive_media_scanner_results ){
				update_option('rbp_media_cleaner_sync-time', date("m/d/Y h:ia"));
				update_option('rbp_media_cleaner_media_data', $rmc_recursive_media_scanner_results_final);
	
				if( $rmc_recursive_media_scanner_results == end($rmc_recursive_media_scanner_results_final) ){
					$response = array(
						"response" => "Done",
						"pageCounter" => $increment,
						"pageTotalCounter" => $maxIncrement,
					);
					wp_send_json_success($response);
				}
			}
		} else {
			$f_increment = $increment+1;
			$response = array(
				"response" => "Done",
				"pageCounter" => $f_increment,
				"pageTotalCounter" => $maxIncrement,
			);
			wp_send_json_success($response);
		}

?>
