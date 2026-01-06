<?php

use Ronik\Base\RbpHelper;
use Ronik\Base\RmcDataGathering;

$RmcDataGathering = new RmcDataGathering;

$rbpHelper = new RbpHelper;
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9a, Media Cleaner Settings. ', 'low', 'rbp_media_cleaner');

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9ass, Media Cleaner Settings. ', 'critical', 'rbp_media_cleaner');

if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9b, Media Cleaner Settings. Security check failed', 'low', 'rbp_media_cleaner');
	wp_send_json_error('Security check failed', '400');
	wp_die();
}
// Check if user is logged in.
if (!is_user_logged_in()) {
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9c, Media Cleaner Settings. is_user_logged_in', 'low', 'rbp_media_cleaner');
	return;
}

if ($_POST['file_size_selector'] == 'changed' && isset($_POST['file_size_selection'])) {
    $new_file_size_mb = floatval($_POST['file_size_selection']);
    $current_file_size_bytes = get_option('rbp_media_cleaner_file_size', 0);
    $current_file_size_mb = $current_file_size_bytes / 1048576;
    
    // Only update if the value has actually changed
    if ($new_file_size_mb != $current_file_size_mb) {
        // Save in bytes: 0 MB = 0 bytes, any other value = MB * 1048576
        $new_file_size_bytes = ($new_file_size_mb == 0) ? 0 : ($new_file_size_mb * 1048576);
        update_option('rbp_media_cleaner_file_size', $new_file_size_bytes);
        // RESET EVERYTHING
        $RmcDataGathering->rmc_reset_alldata(); 
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9d, Media Cleaner Settings. '. $new_file_size_mb . ' MB (' . $new_file_size_bytes . ' bytes)', 'low', 'rbp_media_cleaner');
        // Send success message!
        wp_send_json_success('Done');
        wp_die();
    } else {
        // Value hasn't changed, but request was valid
        wp_send_json_success('No changes needed');
        wp_die();
    }
}

// Debug: Log all POST data
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Debug - POST data: ' . print_r($_POST, true), 'low', 'rbp_media_cleaner');

// Check file_import_selector condition
$file_import_selector_set = isset($_POST['file_import_selector']);
$file_import_selector_value = $file_import_selector_set ? $_POST['file_import_selector'] : 'NOT SET';
$file_import_selection_set = isset($_POST['file_import_selection']);
$file_import_selection_value = $file_import_selection_set ? $_POST['file_import_selection'] : 'NOT SET';

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Debug - file_import_selector: ' . $file_import_selector_value . ', file_import_selection: ' . $file_import_selection_value, 'low', 'rbp_media_cleaner');

if ($file_import_selector_set && $_POST['file_import_selector'] == 'changed' && $file_import_selection_set) {
    $new_file_import = sanitize_text_field($_POST['file_import_selection']);
    $current_file_import = get_option('rbp_media_cleaner_file_import', 'off');
    
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, File Import - New: ' . $new_file_import . ', Current: ' . $current_file_import, 'low', 'rbp_media_cleaner');
    
    error_log(print_r('BLAHHHH ', true));
    // Force update by deleting first, then adding fresh (bypasses any caching issues)
    delete_option('rbp_media_cleaner_file_import');
    $add_result = add_option('rbp_media_cleaner_file_import', $new_file_import);
    
    // If add_option returned false, it means the option exists, so use update_option
    if ($add_result === false) {
        $update_result = update_option('rbp_media_cleaner_file_import', $new_file_import);
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Update option result: ' . ($update_result ? 'true' : 'false'), 'low', 'rbp_media_cleaner');
    } else {
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Add option result: ' . ($add_result ? 'true' : 'false'), 'low', 'rbp_media_cleaner');
    }
    
    // Clear any object cache
    wp_cache_delete('rbp_media_cleaner_file_import', 'options');
    
    // Verify it was saved immediately (bypass cache)
    $verify = get_option('rbp_media_cleaner_file_import', 'off');
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Verified saved value: ' . $verify, 'low', 'rbp_media_cleaner');
    
    // Double-check by querying database directly
    global $wpdb;
    $db_value = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        'rbp_media_cleaner_file_import'
    ));
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Direct DB query value: ' . ($db_value ? $db_value : 'NULL'), 'low', 'rbp_media_cleaner');
    
    // Only reset if the value actually changed
    if ($new_file_import !== $current_file_import) {
        // RESET EVERYTHING when backup setting changes
        $RmcDataGathering->rmc_reset_alldata();
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Data reset because value changed from ' . $current_file_import . ' to ' . $new_file_import, 'low', 'rbp_media_cleaner');
    }
    
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Media Cleaner Settings. File Import/Backup set to: ' . $new_file_import, 'low', 'rbp_media_cleaner');
    // Send success message!
    wp_send_json_success('Done');
    wp_die();
} else {
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9e, Condition not met - selector_set: ' . ($file_import_selector_set ? 'true' : 'false') . ', selector_value: ' . $file_import_selector_value . ', selection_set: ' . ($file_import_selection_set ? 'true' : 'false'), 'low', 'rbp_media_cleaner');
}

// Check post_types_selector condition
$post_types_selector_set = isset($_POST['post_types_selector']);
$post_types_selector_value = $post_types_selector_set ? $_POST['post_types_selector'] : 'NOT SET';
$post_types_selection_set = isset($_POST['post_types_selection']);
$post_types_selection_value = $post_types_selection_set ? $_POST['post_types_selection'] : 'NOT SET';

$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Debug - post_types_selector: ' . $post_types_selector_value . ', post_types_selection: ' . $post_types_selection_value, 'low', 'rbp_media_cleaner');

if ($post_types_selector_set && $_POST['post_types_selector'] == 'changed' && $post_types_selection_set) {
    // Get the raw JSON string - don't sanitize as it will corrupt JSON
    $post_types_json = wp_unslash($_POST['post_types_selection']);
    $new_post_types = json_decode($post_types_json, true);
    
    // Validate that we got an array
    if (!is_array($new_post_types)) {
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9g, Invalid post types data received. JSON: ' . $post_types_json . ', Decoded: ' . print_r($new_post_types, true), 'low', 'rbp_media_cleaner');
        wp_send_json_error('Invalid post types data');
        wp_die();
    }
    
    // Get all available post types directly (not from saved option) to validate against
    $post_types = get_post_types(array(), 'names', 'and');
    // Remove default types that shouldn't be included
    $all_post_types = array_diff(
        $post_types,
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
            'acf-field',
            'acf-ui-options-page',
            'wp_font_family',
            'wp_font_face',
            'ame_ac_changeset'
        )
    );
    $all_post_types = array_values($all_post_types); // Re-index array
    
    // Filter to only include valid post types
    $validated_post_types = array_intersect($new_post_types, $all_post_types);
    
    // Get current saved post types for comparison
    $current_post_types = get_option('rbp_media_cleaner_post_types');
    if (!is_array($current_post_types)) {
        $current_post_types = $all_post_types;
    }
    
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9g, Post Types - New: ' . print_r($validated_post_types, true) . ', Current: ' . print_r($current_post_types, true), 'low', 'rbp_media_cleaner');
    
    // Save the selected post types
    update_option('rbp_media_cleaner_post_types', $validated_post_types);
    
    // Only reset if the value actually changed
    $current_sorted = $current_post_types;
    $new_sorted = $validated_post_types;
    sort($current_sorted);
    sort($new_sorted);
    
    if ($current_sorted !== $new_sorted) {
        // RESET EVERYTHING when post types setting changes
        $RmcDataGathering->rmc_reset_alldata();
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9g, Data reset because post types changed', 'low', 'rbp_media_cleaner');
    }
    
    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9g, Media Cleaner Settings. Post Types set to: ' . print_r($validated_post_types, true), 'low', 'rbp_media_cleaner');
    // Send success message!
    wp_send_json_success('Done');
    wp_die();
}

// If we get here, neither file_size, file_import, nor post_types was changed
// This shouldn't normally happen, but return success anyway
$rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 9f, Media Cleaner Settings. No valid changes detected', 'low', 'rbp_media_cleaner');
wp_send_json_success('No changes');
wp_die();
