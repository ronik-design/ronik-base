<?php 
if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
	return;
}

if ($_POST['file_size_selector'] == 'changed' && (get_option( 'rbp_media_cleaner_file_size' )/1048576) != ($_POST['file_size_selection']) ){
    if( isset( $_POST['file_size_selection'])  ){
        update_option('rbp_media_cleaner_file_size', $_POST['file_size_selection']*1048576);

        // RESET EVERYTHING
        update_option('rbp_media_cleaner_sync_running', 'not-running');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

        delete_transient('rmc_media_cleaner_media_data_collectors_posts_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve');

        delete_transient('rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_post_auditor_array');
        delete_transient('rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array');
    
        delete_option('rbp_media_cleaner_increment');
        delete_option('rbp_media_cleaner_counter');
        delete_option('rbp_media_cleaner_media_data');

        error_log(print_r( $_POST['file_size_selection'], true));
        // Send sucess message!
        wp_send_json_success('Done');
    }
}


if( isset( $_POST['file_import_selection'])  ){
    if ($_POST['file_import_selection'] !== 'invalid'){
        update_option('rbp_media_cleaner_file_import', $_POST['file_import_selection']);
        
        // Send sucess message!
        wp_send_json_success('Done');

        error_log(print_r( $_POST['file_import_selection'], true));
        if($_POST['file_import_selection'] == 'on'){
                        // $backup_file_name =  dirname(__FILE__, 2).'/ronikdetached/archive-database.sql';
                        // // if(isset($_POST['restore'])){
                        // // }
                        //     $dbhost = DB_HOST;
                        //     $dbuser = DB_USER;
                        //     $dbpass = DB_PASSWORD;
                        //     $dbname = DB_NAME;
                        //     $sql = '';
                        //     $error = '';
                        //                 // https://www.blogdesire.com/create-a-database-backup-and-restore-system-in-php/
                        //         $con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

                        //     if (file_exists($backup_file_name)) {
                        //         // Deleting starts here
                        //         $query_disable_checks = 'SET foreign_key_checks = 0';
                        //         mysqli_query($con, $query_disable_checks);
                        //         $show_query = 'Show tables';
                        //         $query_result = mysqli_query($con, $show_query);
                        //         $row = mysqli_fetch_array($query_result);
                        //         while ($row) {
                        //             $query = 'DROP TABLE IF EXISTS ' . $row[0];
                        //             $query_result = mysqli_query($con, $query);
                        //             $show_query = 'Show tables';
                        //             $query_result = mysqli_query($con, $show_query);
                        //             $row = mysqli_fetch_array($query_result);
                        //         }
                        //         $query_enable_checks = 'SET foreign_key_checks = 1';
                        //         mysqli_query($con, $query_enable_checks);
                        //         // Deleting ends here

                        //         error_log(print_r('Database Started', true));

                        //         $lines = file($backup_file_name);
                        //         foreach ($lines as $line) {
                        //             if (substr($line, 0, 2) == '--' || $line == '') {
                        //                 continue;
                        //             }
                        //             $sql .= $line;
                        //             if (substr(trim($line), - 1, 1) == ';') {
                        //                 $result = mysqli_query($con, $sql);
                        //                 if (! $result) {
                        //                     $error .= mysqli_error($con) . "\n";
                        //                 }
                        //                 $sql = '';
                        //             }
                        //             // error_log(print_r($line, true));

                        //         }
                        //         if ($error) {
                        //             $message = $error;
                        //             error_log(print_r( 'Error:' . $message, true));
                        //         } else {
                        //             $message = "Database restored successfully";
                        //             error_log(print_r( 'Successfully:' . $message, true));
                        //         }
                        //     }else{
                        //         $message = "Uh Oh! No backup file found on the current directory!";
                        //         error_log(print_r( 'Uh Oh: ' . $message, true));

                        //     }
            
                        //     error_log(print_r($message, true));

        }

    }
}




wp_send_json_error('Security check failed', '400');
wp_die();
