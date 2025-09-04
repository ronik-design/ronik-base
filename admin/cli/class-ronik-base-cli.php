<?php

/**
 * WP CLI Commands for Ronik Base Plugin
 *
 * @package    Ronik_Base
 * @subpackage Ronik_Base/admin/cli
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * WP CLI Commands for Ronik Base Plugin
 *
 * Provides command line interface for media cleaning operations
 *
 * @since      1.0.0
 * @package    Ronik_Base
 * @subpackage Ronik_Base/admin/cli
 */
class Ronik_Base_CLI
{

    /**
     * Initialize the CLI commands
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if (defined('WP_CLI') && WP_CLI) {
            $this->register_commands();
        }
    }

    	/**
	 * Register all CLI commands
	 *
	 * @since 1.0.0
	 */
	private function register_commands()
	{
		WP_CLI::add_command('media-harmony', 'Ronik_Base_CLI');
	}

    /**
     * Main command handler
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args)
    {
        if (empty($args)) {
            $this->show_help($assoc_args);
            return;
        }

        $subcommand = $args[0];

        switch ($subcommand) {
            case 'help':
                $this->show_help($assoc_args);
                break;
            case 'list':
                $this->list_media($assoc_args);
                break;
            case 'list-preserved':
                $this->list_preserved_media($assoc_args);
                break;
            case 'scan':
                $this->scan_media($assoc_args);
                break;
            case 'clean':
                $this->clean_media($assoc_args);
                break;
            case 'stats':
                $this->show_stats($assoc_args);
                break;
            case 'preserve':
                $this->preserve_media($args, $assoc_args);
                break;
            case 'unpreserve':
                $this->unpreserve_media($args, $assoc_args);
                break;
            default:
                WP_CLI::error("Unknown subcommand: {$subcommand}. Use 'wp media-harmony help' for available commands.");
        }
    }

    /**
     * Show help information for all available commands
     *
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function show_help($assoc_args)
    {
        WP_CLI::log('Media Harmony CLI - WordPress Media Management Tool');
        WP_CLI::log('==================================================');
        WP_CLI::log('');
        
        WP_CLI::log('Available Commands:');
        WP_CLI::log('');
        
        WP_CLI::log('  wp media-harmony                    Show this help message');
        WP_CLI::log('  wp media-harmony help               Show this help message');
        WP_CLI::log('  wp media-harmony list               List all media files with ID and size');
        WP_CLI::log('  wp media-harmony list-preserved     List only preserved media files');
        WP_CLI::log('  wp media-harmony scan               Scan for unused media files');
        WP_CLI::log('  wp media-harmony clean              Clean unused media files');
        WP_CLI::log('  wp media-harmony stats              Show media statistics');
        WP_CLI::log('  wp media-harmony preserve <id>      Preserve a media file from deletion');
        WP_CLI::log('  wp media-harmony unpreserve <id>    Remove preservation from a media file');
        WP_CLI::log('');
        
        WP_CLI::log('Command Options:');
        WP_CLI::log('');
        
        WP_CLI::log('  --limit=<number>                    Number of media files to show (default: 50)');
        WP_CLI::log('  --offset=<number>                   Number of files to skip (for pagination)');
        WP_CLI::log('  --format=<format>                   Output format: table, csv, or json (default: table)');
        WP_CLI::log('  --dry-run                           Preview what would be deleted (for clean command)');
        WP_CLI::log('  --force                             Skip confirmation prompts');
        WP_CLI::log('');
        
        WP_CLI::log('Examples:');
        WP_CLI::log('');
        
        WP_CLI::log('  # Show help');
        WP_CLI::log('  wp media-harmony');
        WP_CLI::log('  wp media-harmony help');
        WP_CLI::log('');
        
        WP_CLI::log('  # List media files');
        WP_CLI::log('  wp media-harmony list');
        WP_CLI::log('  wp media-harmony list --limit=100');
        WP_CLI::log('  wp media-harmony list --format=csv');
        WP_CLI::log('  wp media-harmony list --format=json');
        WP_CLI::log('  wp media-harmony list --limit=25 --offset=50');
        WP_CLI::log('');
        
        WP_CLI::log('  # List preserved media files');
        WP_CLI::log('  wp media-harmony list-preserved');
        WP_CLI::log('  wp media-harmony list-preserved --limit=100');
        WP_CLI::log('  wp media-harmony list-preserved --format=csv');
        WP_CLI::log('  wp media-harmony list-preserved --format=json');
        WP_CLI::log('');
        
        WP_CLI::log('  # Scan for unused media files');
        WP_CLI::log('  wp media-harmony scan');
        WP_CLI::log('');
        
        WP_CLI::log('  # Preview what would be deleted');
        WP_CLI::log('  wp media-harmony clean --dry-run');
        WP_CLI::log('');
        
        WP_CLI::log('  # Clean unused media files (with confirmation)');
        WP_CLI::log('  wp media-harmony clean');
        WP_CLI::log('');
        
        WP_CLI::log('  # Force clean without confirmation');
        WP_CLI::log('  wp media-harmony clean --force');
        WP_CLI::log('');
        
        WP_CLI::log('  # Show media statistics');
        WP_CLI::log('  wp media-harmony stats');
        WP_CLI::log('');
        
        WP_CLI::log('  # Preserve a specific media file');
        WP_CLI::log('  wp media-harmony preserve 123');
        WP_CLI::log('');
        
        WP_CLI::log('  # Remove preservation from a media file');
        WP_CLI::log('  wp media-harmony unpreserve 123');
        WP_CLI::log('');
        
        WP_CLI::log('Requirements:');
        WP_CLI::log('  - Advanced Custom Fields PRO plugin must be installed and activated');
        WP_CLI::log('  - WordPress CLI must be available');
        WP_CLI::log('');
        
        WP_CLI::log('For more information, visit: https://github.com/ronik-design/ronik-base');
    }

    /**
     * List all media files with ID and size information
     *
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function list_media($assoc_args)
    {
        $limit = isset($assoc_args['limit']) ? intval($assoc_args['limit']) : 50;
        $offset = isset($assoc_args['offset']) ? intval($assoc_args['offset']) : 0;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';

        WP_CLI::log('Listing media files...');
        WP_CLI::log('');

        try {
            global $wpdb;
            
            // Get total count
            $total_count = $wpdb->get_var("
                SELECT COUNT(*) FROM {$wpdb->posts} 
                WHERE post_type = 'attachment'
            ");

            if ($total_count == 0) {
                WP_CLI::warning('No media files found.');
                return;
            }

            // Get media files with pagination
            $media_files = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, p.post_date, p.guid,
                       pm_file.meta_value as file_path,
                       pm_size.meta_value as file_size
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm_file ON p.ID = pm_file.post_id AND pm_file.meta_key = '_wp_attached_file'
                LEFT JOIN {$wpdb->postmeta} pm_size ON p.ID = pm_size.post_id AND pm_size.meta_key = '_wp_attachment_metadata'
                WHERE p.post_type = 'attachment'
                ORDER BY p.ID DESC
                LIMIT %d OFFSET %d
            ", $limit, $offset));

            if (empty($media_files)) {
                WP_CLI::warning('No media files found in the specified range.');
                return;
            }

            // Prepare data for display
            $table_data = array();
            foreach ($media_files as $file) {
                $file_size = 0;
                $file_path = '';
                
                // Extract file size from attachment metadata
                if ($file->file_size) {
                    $metadata = maybe_unserialize($file->file_size);
                    if (is_array($metadata) && isset($metadata['filesize'])) {
                        $file_size = $metadata['filesize'];
                    } elseif (is_array($metadata) && isset($metadata['sizes'])) {
                        // Calculate total size from all image sizes
                        foreach ($metadata['sizes'] as $size) {
                            if (isset($size['filesize'])) {
                                $file_size += $size['filesize'];
                            }
                        }
                    }
                }

                // Get file path
                if ($file->file_path) {
                    $file_path = $file->file_path;
                }

                // Get actual file size from filesystem if metadata doesn't have it
                if ($file_size == 0 && $file_path) {
                    $full_path = ABSPATH . 'wp-content/uploads/' . $file_path;
                    if (file_exists($full_path)) {
                        $file_size = filesize($full_path);
                    }
                }

                $table_data[] = array(
                    'ID' => $file->ID,
                    'Title' => $file->post_title ?: '(No Title)',
                    'Size' => $file_size > 0 ? size_format($file_size) : 'Unknown',
                    'Date' => date('Y-m-d H:i:s', strtotime($file->post_date)),
                    'Path' => $file_path ?: 'Unknown'
                );
            }

            // Display results
            if ($format === 'csv') {
                // CSV format
                $csv_data = array();
                foreach ($table_data as $row) {
                    $csv_data[] = implode(',', array_values($row));
                }
                echo implode("\n", $csv_data);
            } elseif ($format === 'json') {
                // JSON format
                echo json_encode($table_data, JSON_PRETTY_PRINT);
            } else {
                // Table format (default)
                WP_CLI\Utils\format_items('table', $table_data, array('ID', 'Title', 'Size', 'Date', 'Path'));
            }

            WP_CLI::log('');
            WP_CLI::log(sprintf('Showing %d of %d media files (offset: %d)', count($media_files), $total_count, $offset));
            
            if ($offset + $limit < $total_count) {
                WP_CLI::log(sprintf('Use --offset=%d to see more files', $offset + $limit));
            }

        } catch (Exception $e) {
            WP_CLI::error('Error listing media: ' . $e->getMessage());
        }
    }

    /**
     * List all preserved media files with ID and size information
     *
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function list_preserved_media($assoc_args)
    {
        $limit = isset($assoc_args['limit']) ? intval($assoc_args['limit']) : 50;
        $offset = isset($assoc_args['offset']) ? intval($assoc_args['offset']) : 0;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';

        WP_CLI::log('Listing preserved media files...');
        WP_CLI::log('');

        try {
            global $wpdb;
            
            // Get total count of preserved media
            $total_count = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'attachment'
                AND pm.meta_key = '_ronik_preserved'
                AND pm.meta_value = '1'
            ");

            if ($total_count == 0) {
                WP_CLI::warning('No preserved media files found.');
                return;
            }

            // Get preserved media files with pagination
            $preserved_files = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, p.post_date, p.guid,
                       pm_file.meta_value as file_path,
                       pm_size.meta_value as file_size,
                       pm_preserved.meta_value as preserved_date
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm_preserved ON p.ID = pm_preserved.post_id 
                    AND pm_preserved.meta_key = '_ronik_preserved'
                    AND pm_preserved.meta_value = '1'
                LEFT JOIN {$wpdb->postmeta} pm_file ON p.ID = pm_file.post_id 
                    AND pm_file.meta_key = '_wp_attached_file'
                LEFT JOIN {$wpdb->postmeta} pm_size ON p.ID = pm_size.post_id 
                    AND pm_size.meta_key = '_wp_attachment_metadata'
                WHERE p.post_type = 'attachment'
                ORDER BY p.ID DESC
                LIMIT %d OFFSET %d
            ", $limit, $offset));

            if (empty($preserved_files)) {
                WP_CLI::warning('No preserved media files found in the specified range.');
                return;
            }

            // Prepare data for display
            $table_data = array();
            foreach ($preserved_files as $file) {
                $file_size = 0;
                $file_path = '';
                
                // Extract file size from attachment metadata
                if ($file->file_size) {
                    $metadata = maybe_unserialize($file->file_size);
                    if (is_array($metadata) && isset($metadata['filesize'])) {
                        $file_size = $metadata['filesize'];
                    } elseif (is_array($metadata) && isset($metadata['sizes'])) {
                        // Calculate total size from all image sizes
                        foreach ($metadata['sizes'] as $size) {
                            if (isset($size['filesize'])) {
                                $file_size += $size['filesize'];
                            }
                        }
                    }
                }

                // Get file path
                if ($file->file_path) {
                    $file_path = $file->file_path;
                }

                // Get actual file size from filesystem if metadata doesn't have it
                if ($file_size == 0 && $file_path) {
                    $full_path = ABSPATH . 'wp-content/uploads/' . $file_path;
                    if (file_exists($full_path)) {
                        $file_size = filesize($full_path);
                    }
                }

                // Get preservation date (when the file was preserved)
                $preserved_date = 'Unknown';
                if ($file->preserved_date) {
                    $preserved_date = date('Y-m-d H:i:s', strtotime($file->preserved_date));
                }

                $table_data[] = array(
                    'ID' => $file->ID,
                    'Title' => $file->post_title ?: '(No Title)',
                    'Size' => $file_size > 0 ? size_format($file_size) : 'Unknown',
                    'Upload Date' => date('Y-m-d H:i:s', strtotime($file->post_date)),
                    'Preserved Date' => $preserved_date,
                    'Path' => $file_path ?: 'Unknown'
                );
            }

            // Display results
            if ($format === 'csv') {
                // CSV format
                $csv_data = array();
                foreach ($table_data as $row) {
                    $csv_data[] = implode(',', array_values($row));
                }
                echo implode("\n", $csv_data);
            } elseif ($format === 'json') {
                // JSON format
                echo json_encode($table_data, JSON_PRETTY_PRINT);
            } else {
                // Table format (default)
                WP_CLI\Utils\format_items('table', $table_data, array('ID', 'Title', 'Size', 'Upload Date', 'Preserved Date', 'Path'));
            }

            WP_CLI::log('');
            WP_CLI::log(sprintf('Showing %d of %d preserved media files (offset: %d)', count($preserved_files), $total_count, $offset));
            
            if ($offset + $limit < $total_count) {
                WP_CLI::log(sprintf('Use --offset=%d to see more files', $offset + $limit));
            }

        } catch (Exception $e) {
            WP_CLI::error('Error listing preserved media: ' . $e->getMessage());
        }
    }

    /**
     * Scan for unused media files
     *
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function scan_media($assoc_args)
    {
        WP_CLI::log('Starting media scan...');

        // Check if ACF is active
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        if (!is_plugin_active('advanced-custom-fields-pro/acf.php')) {
            WP_CLI::error('Advanced Custom Fields PRO must be installed and activated to use this feature.');
            return;
        }

        try {
            // Initialize data gathering
            if (class_exists('Ronik\Base\RmcDataGathering')) {
                $data_gathering = new \Ronik\Base\RmcDataGathering();
                
                // Perform the scan
                $results = $data_gathering->init_media_cleaner();
                
                if ($results) {
                    WP_CLI::success('Media scan completed successfully!');
                    				WP_CLI::log('Use "wp media-harmony media stats" to view scan results.');
                } else {
                    WP_CLI::warning('Media scan completed but no results were found.');
                }
            } else {
                WP_CLI::error('Required class RmcDataGathering not found.');
            }
        } catch (Exception $e) {
            WP_CLI::error('Error during media scan: ' . $e->getMessage());
        }
    }

    /**
     * Clean unused media files
     *
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function clean_media($assoc_args)
    {
        $dry_run = isset($assoc_args['dry-run']) ? true : false;
        $force = isset($assoc_args['force']) ? true : false;

        if (!$force && !$dry_run) {
            WP_CLI::confirm('This will permanently delete unused media files. Are you sure?');
        }

        WP_CLI::log('Starting media cleanup...');

        if ($dry_run) {
            WP_CLI::log('DRY RUN MODE - No files will be deleted');
        }

        try {
            // Get unused media files
            global $wpdb;
            
            $unused_files = $wpdb->get_results("
                SELECT p.ID, p.post_title, p.guid 
                FROM {$wpdb->posts} p 
                WHERE p.post_type = 'attachment' 
                AND p.ID NOT IN (
                    SELECT DISTINCT pm.meta_value 
                    FROM {$wpdb->postmeta} pm 
                    WHERE pm.meta_key = '_thumbnail_id'
                )
                AND p.ID NOT IN (
                    SELECT DISTINCT pm.meta_value 
                    FROM {$wpdb->postmeta} pm 
                    WHERE pm.meta_key LIKE '%_id'
                )
                AND p.ID NOT IN (
                    SELECT DISTINCT pm.meta_value 
                    FROM {$wpdb->postmeta} pm 
                    WHERE pm.meta_key LIKE 'field_%'
                )
            ");

            if (empty($unused_files)) {
                WP_CLI::success('No unused media files found.');
                return;
            }

            WP_CLI::log(sprintf('Found %d potentially unused media files.', count($unused_files)));

            $deleted_count = 0;
            foreach ($unused_files as $file) {
                if ($dry_run) {
                    WP_CLI::log("Would delete: {$file->post_title} (ID: {$file->ID})");
                } else {
                    $result = wp_delete_attachment($file->ID, true);
                    if ($result) {
                        WP_CLI::log("Deleted: {$file->post_title} (ID: {$file->ID})");
                        $deleted_count++;
                    } else {
                        WP_CLI::warning("Failed to delete: {$file->post_title} (ID: {$file->ID})");
                    }
                }
            }

            if ($dry_run) {
                WP_CLI::success('Dry run completed. No files were deleted.');
            } else {
                WP_CLI::success(sprintf('Media cleanup completed. Deleted %d files.', $deleted_count));
            }

        } catch (Exception $e) {
            WP_CLI::error('Error during media cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Show media statistics
     *
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function show_stats($assoc_args)
    {
        WP_CLI::log('Media Statistics:');
        WP_CLI::log('================');

        try {
            global $wpdb;
            
            // Total attachments
            $total_attachments = $wpdb->get_var("
                SELECT COUNT(*) FROM {$wpdb->posts} 
                WHERE post_type = 'attachment'
            ");

            // Used attachments
            $used_attachments = $wpdb->get_var("
                SELECT COUNT(DISTINCT p.ID) 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.meta_value 
                WHERE p.post_type = 'attachment' 
                AND (pm.meta_key = '_thumbnail_id' 
                     OR pm.meta_key LIKE '%_id' 
                     OR pm.meta_key LIKE 'field_%')
            ");

            // Unused attachments
            $unused_attachments = $total_attachments - $used_attachments;

            // File size statistics
            $file_sizes = $wpdb->get_results("
                SELECT pm.meta_value as file_size 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = 'attachment' 
                AND pm.meta_key = '_wp_attached_file'
            ");

            $total_size = 0;
            foreach ($file_sizes as $size) {
                $file_path = ABSPATH . 'wp-content/uploads/' . $size->file_size;
                if (file_exists($file_path)) {
                    $total_size += filesize($file_path);
                }
            }

            WP_CLI::log(sprintf('Total Attachments: %d', $total_attachments));
            WP_CLI::log(sprintf('Used Attachments: %d', $used_attachments));
            WP_CLI::log(sprintf('Unused Attachments: %d', $unused_attachments));
            WP_CLI::log(sprintf('Total File Size: %s', size_format($total_size)));

            			if ($unused_attachments > 0) {
				WP_CLI::log('');
				WP_CLI::log('Use "wp media-harmony media clean" to remove unused files.');
				WP_CLI::log('Use "wp media-harmony media clean --dry-run" for a safe preview.');
			}

        } catch (Exception $e) {
            WP_CLI::error('Error getting statistics: ' . $e->getMessage());
        }
    }

    /**
     * Preserve a media file from deletion
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function preserve_media($args, $assoc_args)
    {
        if (empty($args[1])) {
            WP_CLI::error('Please specify a media ID to preserve.');
        }

        $media_id = intval($args[1]);

        try {
            $post = get_post($media_id);
            if (!$post || $post->post_type !== 'attachment') {
                WP_CLI::error('Invalid media ID or not an attachment.');
            }

            // Add to preserved list (you'll need to implement this based on your existing preserve functionality)
            update_post_meta($media_id, '_ronik_preserved', true);
            
            WP_CLI::success(sprintf('Media file "%s" (ID: %d) has been preserved.', $post->post_title, $media_id));

        } catch (Exception $e) {
            WP_CLI::error('Error preserving media: ' . $e->getMessage());
        }
    }

    /**
     * Remove preservation from a media file
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Command options
     * @since 1.0.0
     */
    private function unpreserve_media($args, $assoc_args)
    {
        if (empty($args[1])) {
            WP_CLI::error('Please specify a media ID to unpreserve.');
        }

        $media_id = intval($args[1]);

        try {
            $post = get_post($media_id);
            if (!$post || $post->post_type !== 'attachment') {
                WP_CLI::error('Invalid media ID or not an attachment.');
            }

            // Remove from preserved list
            delete_post_meta($media_id, '_ronik_preserved');
            
            WP_CLI::success(sprintf('Media file "%s" (ID: %d) is no longer preserved.', $post->post_title, $media_id));

        } catch (Exception $e) {
            WP_CLI::error('Error unpreserving media: ' . $e->getMessage());
        }
    }
}
