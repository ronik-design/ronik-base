<?php
// Verify nonce for security
if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
    wp_send_json_error('Security check failed', 400);
    wp_die();
}

// Ensure the user is logged in
if (!is_user_logged_in()) {
    return;
}

// Get API key from options
$rbp_media_cleaner_api_key = get_option('rbp_media_cleaner_api_key', '');


switch (true) {
    case isset($_POST['media_progress']) && $_POST['media_progress'] === 'checker_run':
        handleMediaProgress($rbp_media_cleaner_api_key);
        break;

    case isset($_POST['api_validation']) && $_POST['api_validation'] === 'invalidate':
        handleApiValidation();
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

// Handle media progress
function handleMediaProgress($apiKey) {
    if ($apiKey) {
        $syncStatus = get_option('rbp_media_cleaner_sync_running', 'not-running');

        if ($syncStatus === 'running') {
            $progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
            $finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');

            if ($progress !== 'DONE') {
                wp_send_json_success($progress);
            } elseif ($finalized) {
                delete_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');

                wp_send_json_success('COMPLETED');
            }
        } else {
            // wp_send_json_success('COMPLETED');
            // wp_send_json_success('SEMI_SUCCESS');

        }
    }
}

// Handle API validation
function handleApiValidation() {
    $apiKey = get_option('rbp_media_cleaner_api_key', '');

    if ($apiKey) {
        update_option('rbp_media_cleaner_api_key', '');
        update_option('rbp_media_cleaner_api_key_validation', 'invalid');
        wp_send_json_success('Reload');
    } else {
        wp_send_json_success('noreload');
    }
}

// Handle API key check
function handleApiKeyCheck($apiKey) {
    if ($apiKey) {
        wp_send_json_success($apiKey);
    } else {
        wp_send_json_error('Invalid');
    }
}

// Handle plugin slug update
function handlePluginSlugUpdate($keyOption, $validationOption) {
    $apiKey = $_POST['apikey'] ?? '';
    $apiKeyValidation = $_POST['apikeyValidation'] ?? 'invalid';

    $isUpdated = update_option($keyOption, $apiKey);
    update_option($validationOption, $apiKeyValidation);

    if ($isUpdated) {
        wp_send_json_success('Reload');
    } else {
        wp_send_json_error('No rows found!');
    }
}

$progress = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress');
$finalized = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized');
if ($progress == 'DONE') {
    wp_send_json_success('COMPLETED');
}
