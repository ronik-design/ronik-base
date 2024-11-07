<?php
$rbpHelper = new RbpHelper;
$rbpHelper->ronikdesigns_write_log_devmode('API Checkpoint: Ref 1a ', 'low', 'rbp_api_checkpoint');
// Verify nonce for security
if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
    $rbpHelper->ronikdesigns_write_log_devmode('API Checkpoint: Ref 1b, Security check failed ', 'low', 'rbp_api_checkpoint');

    wp_send_json_error('Security check failed', 400);
    wp_die();
}

// Ensure the user is logged in
if (!is_user_logged_in()) {
    $rbpHelper->ronikdesigns_write_log_devmode('API Checkpoint: Ref 1c, User not logged in ', 'low', 'rbp_api_checkpoint');

    wp_send_json_error('User not logged in', 403);
    wp_die();
}

// Retrieve the API key from options
$rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key', '');
if($this->beta_mode_state){
    // error_log(print_r('BETA API KEY', true));
    // $rbp_media_cleaner_api_key = 'beta-key';
    $rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key', '');
}


$rbp_media_cleaner_sync_running = get_option('rbp_media_cleaner_sync_running', '');
$rbp_media_cleaner_sync_running_time = get_option('rbp_media_cleaner_sync_running-time', 'invalid');

// A time validator if the sync is r4unning longer then necessary!
if($rbp_media_cleaner_sync_running == 'running'){
    $date = new DateTime(); // For today/now, don't pass an arg.

    if($rbp_media_cleaner_sync_running_time == 'invalid'){
        update_option('rbp_media_cleaner_sync_running-time', date($date->format("m/d/Y h:ia")));
        update_option('rbp_media_cleaner_sync_running', 'not-running');
    } else {
        $date->modify("-120 minutes");
        if(date($date->format("m/d/Y h:ia")) > $rbp_media_cleaner_sync_running_time){
            error_log(print_r('Start Reseting Everything!' , true));
            // RESET EVERYTHING
            delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');
            delete_transient('rmc_media_cleaner_media_data_collectors_posts_array');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_thumbnail_auditor_array');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_not_preserve');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_filesystem_auditor_array');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_post_auditor_array');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_post_content_auditor_array');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_option_auditor_array');
            delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array');

            delete_option('rbp_media_cleaner_increment');
            delete_option('rbp_media_cleaner_counter');
            delete_option('rbp_media_cleaner_media_data');

            sleep(2);
            delete_option('rbp_media_cleaner_sync-time');
            delete_option('rbp_media_cleaner_sync_running-time');
            update_option('rbp_media_cleaner_sync_running', 'not-running');
            $rbpHelper->ronikdesigns_write_log_devmode('API Checkpoint: Ref 1d, EXPIRED ', 'low', 'rbp_media_cleaner');
            error_log(print_r('Completed Reseting Everything!' , true));



            // $this->rmc_media_sync();

            // error_log(print_r('EHHH' , true));


            









            // Retrieve the current sync status from the database.

$syncRunning = get_option('rbp_media_cleaner_sync_running', 'not-running');

// Retrieve the transient progress and finalized image ID lists.
$progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
$finalizedImageIds = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

// If the sync process is currently running, return the progress status.
if ($syncRunning === 'running') {
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 6d, Running: ' . $progress , 'low', 'rbp_media_cleaner');
    wp_send_json_success([
        'sync' => $progress,
        'response' => 'Collector-Sync-inprogress-' . rand(),
    ]);
    exit;
}

// If progress is not set to 'DONE', initiate or continue the sync process.
if (empty($progress) || $progress !== 'DONE') {
    // Call the method to start or continue the media sync process.
    $this->rmc_media_sync();
    sleep(2); // Short delay to ensure the sync process is updated.

    // Retrieve the updated progress status.
    $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');

    // If progress is 'DONE', send a success response indicating completion.
    if ($progress === 'DONE') {
        wp_send_json_success('COMPLETED');
        exit;
    }

    // Return the sync progress status with a random response string.
    wp_send_json_success([
        'sync' => '0%',
        'response' => 'Collector-Sync-inprogress-' . rand(),
    ]);
    exit;
}
// If the finalized image ID list is not set or the sync is in progress, check and proceed.
if (!$finalizedImageIds || (isset($_POST['sync']) && $_POST['sync'] === 'inprogress')) {
    // Retrieve the current progress status.
    $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
    
    // Call the sync method to ensure the image IDs are up-to-date.
    $this->rmc_media_sync();
    sleep(1); // Short delay to ensure the sync process is updated.

    // If progress is 'DONE', send a success response indicating completion.
    if ($progress === 'DONE') {
        wp_send_json_success('COMPLETED');
        exit;
    }
    // Log the completion status.
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 6e, Collector-Sync-done'  , 'low', 'rbp_media_cleaner');
}






// $this->rmc_media_sync();

error_log(print_r('EHHH' , true));



            

            // wp_send_json_success('COMPLETED');
            // sleep(2);
            // wp_die();
        } else {
            $rbpHelper->ronikdesigns_write_log_devmode('API Checkpoint: Ref 1d, NOT EXPIRED ', 'low', 'rbp_media_cleaner');
        }
    }
}







// Handle different AJAX requests based on POST data
switch (true) {
    case isset($_POST['media_progress']) && $_POST['media_progress'] === 'checker_run':
        handleMediaProgress($rbp_media_cleaner_api_key);
        break;

    case isset($_POST['api_validation']) && $_POST['api_validation'] === 'invalidate':
        handleApiValidation($rbp_media_cleaner_api_key);
        break;

    case isset($_POST['api_key']) && $_POST['api_key'] === 'ronik_media_cleaner':
        handleApiKeyCheck($rbp_media_cleaner_api_key);
        break;

    case isset($_POST['plugin_slug']) && $_POST['plugin_slug'] === 'ronik_media_cleaner':
        handlePluginSlugUpdate('rbp_media_cleaner_api_key', 'rbp_media_cleaner_api_key_validation');
        break;

    case isset($_POST['plugin_slug']) && $_POST['plugin_slug'] === 'ronik_optimization':
        handlePluginSlugUpdate('rbp_optimization_api_key', 'rbp_optimization_api_key_validation');
        break;

    default:
        wp_send_json_error('Invalid request');
        break;
}

// Handle media progress updates
function handleMediaProgress($apiKey) {
    if ($apiKey) {
        $syncStatus = get_option('rbp_media_cleaner_sync_running', 'not-running');
        $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
        $finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

        if ($syncStatus === 'running') {
            if ($progress !== 'DONE') {
                wp_send_json_success($progress);
            } elseif ($finalized) {
                delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
                wp_send_json_success('COMPLETED');
            }
        } else {
            if ($progress === 'DONE') {
                wp_send_json_success('COMPLETED');
            } else{
                wp_send_json_success('NOT_RUNNING');
            }
        }
    }
}

// Handle API key invalidation
function handleApiValidation() {
    $apiKey = get_option('rbp_media_cleaner_api_key', '');

    if ($apiKey) {
        update_option('rbp_media_cleaner_api_key', '');
        update_option('rbp_media_cleaner_api_key_validation', 'invalid');
        wp_send_json_success('Reload');
    } else {
        // wp_send_json_success('No change required');
    }
}

// Handle API key checking
function handleApiKeyCheck($apiKey) {
    if ($apiKey) {
        wp_send_json_success($apiKey);
    } else {
        wp_send_json_error('Invalid API key');
    }
}

// Handle plugin slug updates
function handlePluginSlugUpdate($keyOption, $validationOption) {
    $apiKey = $_POST['apikey'] ?? '';
    $apiKeyValidation = $_POST['apikeyValidation'] ?? 'invalid';

    $isUpdated = update_option($keyOption, $apiKey);
    update_option($validationOption, $apiKeyValidation);

    if ($isUpdated) {
        wp_send_json_success('Reload');
    } else {
        wp_send_json_error('Failed to update options');
    }
}

// Check the progress of media cleaning and send the appropriate response
$progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
if ($progress === 'DONE') {
    wp_send_json_success('COMPLETED');
}

if(!$progress){
    wp_send_json_success('NOT_RUNNING');
}
