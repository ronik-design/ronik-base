<?php
/**
 * Init Unused Media Migration.
 */

// Create an instance of the helper class.
$helper = new RonikBaseHelper;

// Retrieve the timestamp of the last cron run from the database, defaulting to 'false' if not set.
$cronLastRun = get_option('rbp_media_cleaner_cron_last', 'false');

// Get the current timestamp and timestamp for one day ago.
$currentTimestamp = strtotime('now');
$oneDayAgo = strtotime('-1 day');

// Check if the cron job was last run more than a day ago.
if ($cronLastRun && $currentTimestamp > strtotime($cronLastRun)) {
    // Log the cron reset event.
    error_log('Cron Reset');
    
    // Update the sync status to 'not-running' and delete the transient progress data.
    update_option('rbp_media_cleaner_sync_running', 'not-running');
    delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
}

// Short delay to ensure the consistency of operations.
sleep(1);

// Retrieve the current sync status from the database.
$syncRunning = get_option('rbp_media_cleaner_sync_running', 'not-running');

// Retrieve the transient progress and finalized image ID lists.
$progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
$finalizedImageIds = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

// Log the initial sync status and progress.
error_log('Running First Time: ' . $syncRunning);
error_log('Progress: ' . $progress);

// If the sync process is currently running, return the progress status.
if ($syncRunning === 'running') {
    error_log('Running');
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
    error_log('Collector-Sync-done');
}

// Log the increment value or indicate if it is not set.
error_log($_POST['increment'] ?? 'No increment');

// Handle the increment and update the sync progress accordingly.
if (isset($_POST['increment'])) {
    $increment = $_POST['increment'];

    // Check if the finalized image IDs are available.
    if (!empty($finalizedImageIds)) {
        $imageIds = $finalizedImageIds;
    } else {
        // Retrieve the current progress status and return it with a sync-in-progress response if image IDs are not available.
        $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
        wp_send_json_success([
            'sync' => $progress,
            'response' => 'Collector-Sync-inprogress-' . rand(),
        ]);
        exit;
    }

    // Retrieve the finalized image IDs and apply a short delay.
    $imageIds = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');
    sleep(1); // Short delay to ensure the process is up-to-date.

    // Get the MIME type filter and apply it to the image IDs.
    $mimeType = cleaner_post_mime_type($_POST['mime_type']);
    $filteredImageIds = $_POST['mime_type'] === 'all' ? $imageIds : array_filter($imageIds, function($id) use ($mimeType) {
        return in_array(get_post_mime_type($id), $mimeType);
    });

    // Calculate the total count and pagination details.
    $totalCount = count($filteredImageIds);
    $postsPerPage = 35; // Number of posts to process per page.
    $maxPages = ceil($totalCount / $postsPerPage); // Total number of pages required.

    // Reset and clean up if increment is zero.
    if ($increment == 0) {
        databaseScannerMedia__cleaner();
        sleep(1); // Short delay to ensure the clean-up process is complete.
    }

    // Log the current increment and maximum pages.
    error_log('Increment: ' . $increment);
    error_log('Max Increment: ' . $maxPages);

    // If the increment has reached or exceeded the maximum number of pages, prompt for a reload.
    if ($increment >= $maxPages) {
        update_option('rbp_media_cleaner_increment', 1);
        wp_send_json_success([
            'response' => 'Reload',
            'pageCounter' => $increment,
            'pageTotalCounter' => $maxPages,
        ]);
        exit;
    }

    // Process and update the options for the remaining image IDs.
    update_option('rbp_media_cleaner_counter', count($filteredImageIds));
    foreach ($filteredImageIds as $key => $imageId) {
        update_option('rbp_media_cleaner_sync-time', date("m/d/Y h:ia"));
        update_option('rbp_media_cleaner_media_data', $filteredImageIds);

        // If the current image ID is the last in the array, send a completion response.
        if ($imageId === end($filteredImageIds)) {
            wp_send_json_success([
                'response' => 'Done',
                'pageCounter' => $increment,
                'pageTotalCounter' => $maxPages,
            ]);
            exit;
        }
    }

    // Increment the counter for the next batch and send a response.
    $nextIncrement = $increment + 1;
    wp_send_json_success([
        'response' => 'Done',
        'pageCounter' => $nextIncrement,
        'pageTotalCounter' => $maxPages,
    ]);
    exit;

} else {
    // Handle cases where the increment parameter is missing or incorrect.
    wp_send_json_error('Increment error.');
    exit;
}
?>
