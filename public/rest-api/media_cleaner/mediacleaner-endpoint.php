<?php

use Ronik\Base\RbpHelper;
use Ronik\Base\RmcDataGathering;
use Ronik\Base\RonikBaseHelper;

class MediaCleanerDataHandler
{
    private $helper;
    private $upload_dir;

    public function __construct()
    {
        $this->helper = new RonikBaseHelper();
        $this->upload_dir = wp_upload_dir();
        $this->helper->ronikdesigns_increase_memory();
    }

    /**
     * Main handler for media cleaner data requests
     */
    public function handleRequest($data)
    {
        $referer = $data->get_header('referer');
        $slug = $data['slug'] ?? '';
        $filters = $data->get_param('filter') ?? '';

        // Route to appropriate handler based on slug
        switch ($slug) {
            case 'stats':
                return $this->handleStatsRequest();
            case 'sync_status':
                return $this->handleSyncStatusRequest();
            case 'tempsaved':
                return $this->handleTempSavedRequest($referer);
            case 'large':
                return $this->handleSizeFilteredRequest($slug, $filters, $referer);
            case 'small':
                return $this->handleSizeFilteredRequest($slug, $filters, $referer);
            case 'all':
                return $this->handleAllMediaRequest($filters, $referer);
            default:
                return $this->handleFilteredRequest($filters, $referer);
        }
    }

    /**
     * Get media data based on context (preserved vs regular)
     */
    private function getMediaData($referer)
    {
        if ($this->isPreservedPage($referer)) {
            return $this->getPreservedData();
        }
        return get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized') ?: [];
    }

    /**
     * Check if we're on the preserved media page
     */
    private function isPreservedPage($referer)
    {
        return str_contains($referer, 'page=options-ronik-base_preserved');
    }

    /**
     * Get preserved media data
     */
    private function getPreservedData()
    {
        $args = [
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'numberposts' => -1,
            'fields'      => 'ids',
        ];

        $image_ids = get_posts($args);
        $preserved_ids = [];

        foreach ($image_ids as $image_id) {
            $meta_data = wp_get_attachment_metadata($image_id);
            if ($meta_data && $this->isMetadataPreserved($meta_data)) {
                $preserved_ids[] = $image_id;
            }
        }

        return $preserved_ids;
    }

    /**
     * Check if metadata indicates preserved status
     */
    private function isMetadataPreserved($meta_data)
    {
        foreach ($meta_data as $data) {
            if ($data === 'rbp_media_cleaner_isdetached_temp-saved') {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle stats request
     */
    private function handleStatsRequest()
    {
        $preserved_data = $this->getPreservedData();
        $unlinked_data = get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_finalized') ?: [];

        $total_media = $this->getTotalMediaCount();
        $total_pages = $this->getTotalPagesCount();

        $stats = [
            'unlinked' => count($unlinked_data),
            'preserved' => count($preserved_data),
            'total' => $total_media,
            'total_pages' => $total_pages,
            'pages_to_media_ratio' => $total_media > 0 ? round($total_pages / $total_media, 2) : 0,
        ];

        // Calculate file sizes
        $stats['unlinked_size'] = $this->calculateTotalSize($unlinked_data);
        $stats['preserved_size'] = $this->calculateTotalSize($preserved_data);
        $stats['unlinked_size_formatted'] = $this->formatFileSize($stats['unlinked_size']);
        $stats['preserved_size_formatted'] = $this->formatFileSize($stats['preserved_size']);
        $stats['safe_to_delete'] = $stats['unlinked'] - $stats['preserved'];

        // Calculate breakdown by file type
        $stats['breakdown'] = $this->calculateFileTypeBreakdown($unlinked_data, $stats['unlinked']);

        // Calculate file size distribution for recommendations
        // Allow bypassing cache via query parameter for debugging
        $force_recalculate = isset($_GET['recalculate_distribution']) && $_GET['recalculate_distribution'] === 'true';
        $stats['file_size_distribution'] = $this->calculateFileSizeDistribution($force_recalculate);
        $stats['current_file_size_setting'] = $this->getCurrentFileSizeSetting();

        return wp_send_json_success($stats);
    }

    /**
     * Handle sync status request
     */
    private function handleSyncStatusRequest()
    {
        $sync_status = [
            'syncRunning' => get_option('rbp_media_cleaner_sync_running', 'not-running'),
            'progress' => get_transient('rmc_media_cleaner_media_data_collectors_image_id_array_progress'),
            'syncTime' => get_option('rbp_media_cleaner_sync-time')
        ];

        return wp_send_json_success($sync_status);
    }

    /**
     * Handle temp saved request
     */
    private function handleTempSavedRequest($referer)
    {
        $mime_types = $this->getAllMimeTypes();
        
        $args = [
            'post_mime_type' => $mime_types,
            'numberposts'    => -1,
            'post_parent'    => get_the_ID(),
            'post_type'      => 'attachment',
            'fields'         => 'ids',
            'meta_query'     => [[
                'key'     => '_wp_attachment_metadata',
                'value'   => 'rbp_media_cleaner_isdetached_temp-saved',
                'compare' => 'LIKE'
            ]]
        ];

        return $this->formatMediaData(get_posts($args), $referer);
    }

    /**
     * Handle size filtered requests (large/small)
     */
    private function handleSizeFilteredRequest($slug, $filters, $referer)
    {
        $media_data = $this->getMediaData($referer);
        
        if (empty($media_data)) {
            return 'no-images';
        }

        // If filtering all without specific type filters, sort by size
        if ($this->isFilteringAllWithoutSpecifics($filters)) {
            return $this->sortMediaBySize($media_data, $slug, $referer);
        }

        // If there are specific filters, filter first then sort by size
        $filtered_ids = $this->filterMediaByType($media_data, $filters);
        
        if (empty($filtered_ids)) {
            return 'no-images';
        }

        // Always sort by size for size-filtered requests
        return $this->sortMediaBySize($filtered_ids, $slug, $referer);
    }

    /**
     * Handle all media request
     */
    private function handleAllMediaRequest($filters, $referer)
    {
        if (str_contains($filters, 'all')) {
            $media_data = $this->getMediaData($referer);
            return $this->formatMediaData($media_data, $referer);
        }

        return 'no-images';
    }

    /**
     * Handle filtered media request
     */
    private function handleFilteredRequest($filters, $referer)
    {
        if (empty($filters)) {
            return 'no-images';
        }

        $media_data = $this->getMediaData($referer);
        
        if (!is_array($media_data)) {
            return 'no-images';
        }

        $filtered_ids = $this->filterMediaByType($media_data, $filters);
        
        if (empty($filtered_ids)) {
            return 'no-images';
        }

        return $this->formatMediaData($filtered_ids, $referer);
    }

    /**
     * Filter media by type based on filters
     */
    private function filterMediaByType($media_data, $filters)
    {
        $filters_array = explode("?", $filters);
        $filtered_ids = [];

        foreach ($filters_array as $filter) {
            $mime_types = cleaner_post_mime_type($filter);
            foreach ($media_data as $media_id) {
                $post_mime_type = get_post_mime_type($media_id);
                if (array_search($post_mime_type, $mime_types)) {
                    $filtered_ids[] = $media_id;
                }
            }
        }

        return array_unique($filtered_ids);
    }

    /**
     * Check if filtering all without specific type filters
     */
    private function isFilteringAllWithoutSpecifics($filters)
    {
        $specific_filters = ['audio', 'gif', 'jpg', 'png', 'video', 'misc'];
        
        if (str_contains($filters, 'all') || empty($filters)) {
            foreach ($specific_filters as $specific) {
                if (str_contains($filters, $specific)) {
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }

    /**
     * Sort media by size (large to small or small to large)
     */
    private function sortMediaBySize($media_data, $sort_direction, $referer)
    {
        $media_sizes = [];

        foreach ($media_data as $media_id) {
            $size = $this->getMediaFileSize($media_id);
            $media_sizes[$media_id] = intval($size);
        }

        arsort($media_sizes, SORT_NATURAL);

        if ($sort_direction === 'small') {
            $media_sizes = array_reverse($media_sizes, true);
        }

        return $this->formatMediaData(array_keys($media_sizes), $referer);
    }

    /**
     * Get media file size with fallback methods
     */
    private function getMediaFileSize($media_id)
    {
        $metadata = wp_get_attachment_metadata($media_id);
        
        // Try metadata filesize first
        if (isset($metadata['filesize']) && $metadata['filesize']) {
            return $metadata['filesize'];
        }

        // Try file path from metadata
        if (isset($metadata['file']) && $metadata['file']) {
            $file_path = $this->upload_dir['basedir'] . '/' . $metadata['file'];
            if (file_exists($file_path)) {
                return filesize($file_path);
            }
        }

        // Fallback to get_attached_file
        $file_path = get_attached_file($media_id);
        return $file_path && file_exists($file_path) ? filesize($file_path) : 0;
    }

    /**
     * Format media data for response
     */
    private function formatMediaData($media_ids, $referer)
    {
        if (empty($media_ids)) {
            return [];
        }

        $formatted_data = [];
        $is_local = $this->helper->localValidator();

        foreach ($media_ids as $index => $media_id) {
            $formatted_data[$index] = $this->formatSingleMediaItem($media_id, $is_local);
        }

        return $formatted_data;
    }

    /**
     * Format a single media item
     */
    private function formatSingleMediaItem($media_id, $is_local)
    {
        $item = ['id' => $media_id];
        
        // Generate thumbnail
        $item['img-thumb'] = $this->generateThumbnail($media_id, $is_local);
        
        // Get file size
        $item['media_size'] = $this->formatSizeUnits($this->getMediaFileSize($media_id));
        
        // Get file path
        $item['media_file'] = $this->getMediaFilePath($media_id);
        
        // Get file type
        $item['media_file_type'] = $this->getMediaFileType($media_id);

        return $item;
    }

    /**
     * Generate thumbnail for media item
     */
    private function generateThumbnail($media_id, $is_local)
    {
        $image_src = wp_get_attachment_image_src($media_id);
        $file_path = get_attached_file($media_id);
        
        // Primary check: Can WordPress generate image data?
        if (!$image_src || empty($image_src[0])) {
            $image_url = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
        } else {
            // Verify the original file exists and is readable
            if (!$file_path || !file_exists($file_path) || !is_readable($file_path)) {
                $image_url = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
            } else {
                // For images, validate with getimagesize (fast, reads header only)
                $mime_type = get_post_mime_type($media_id);
                if (strpos($mime_type, 'image/') === 0) {
                    $image_info = @getimagesize($file_path);
                    if ($image_info === false) {
                        $image_url = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
                    } else {
                        // Try to get thumbnail URL - if this fails, treat as corrupt
                        $image_url = wp_get_attachment_image_url($media_id, 'medium');
                        if (!$image_url) {
                            $image_url = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
                        }
                    }
                } else {
                    // For non-images, just check if thumbnail URL exists
                    $image_url = wp_get_attachment_image_url($media_id, 'thumbnail');
                    if (!$image_url) {
                        $image_url = "/wp-content/plugins/ronik-base/admin/media-cleaner/image/thumb-corrupt-file.svg";
                    }
                }
            }
        }

        $thumbnail = wp_get_attachment_image($media_id, 'medium', false, [
            'data-src' => $image_url,
            'data-id' => $media_id,
            'data-class' => 'image-target',
            'class' => ' lzy_img reveal-disabled',
            'src' => $this->helper->ronikdesignsbase_svgplaceholder(),
            'data-width' => $image_src[1] ?? '50',
            'data-height' => $image_src[2] ?? '50',
            'data-type' => (!$is_local && isset($image_src[0])) ? wp_get_image_mime($image_src[0]) : ''
        ]);

        // Fallback thumbnail for non-image files
        if (!$thumbnail) {
            $thumbnail = $this->generateFallbackThumbnail($media_id);
        }

        // Clean up thumbnail attributes
        return $this->cleanThumbnailAttributes($thumbnail);
    }

    /**
     * Generate fallback thumbnail for non-image files
     */
    private function generateFallbackThumbnail($media_id)
    {
        $mime_type = get_post_mime_type($media_id);
        $thumb_file = $this->getThumbnailFileByMimeType($mime_type);

        return sprintf(
            '<img src="data:image/svg+xml,%%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%%3E%%3C/svg%%3E" 
            class=" lzy_img reveal-disabled" 
            decoding="async" 
            data-src="/wp-content/plugins/ronik-base/admin/media-cleaner/image/%s" 
            data-id="%s" 
            data-class="image-target" 
            data-width="1" 
            data-height="1" 
            data-type="" />',
            $thumb_file,
            $media_id
        );
    }

    /**
     * Get thumbnail file based on mime type
     */
    private function getThumbnailFileByMimeType($mime_type)
    {
        if (str_contains($mime_type, 'video/')) return 'thumb-video-file.jpg';
        if (str_contains($mime_type, 'audio/')) return 'thumb-audio-file.jpg';
        if (str_contains($mime_type, 'image/')) return 'thumb-image-file.jpg';
        return 'thumb-misc-file.jpg';
    }

    /**
     * Clean thumbnail attributes
     */
    private function cleanThumbnailAttributes($thumbnail)
    {
        $patterns = ['/srcset="[^"]*"/', '/sizes="[^"]*"/', '/alt="[^"]*"/'];
        return preg_replace($patterns, '', $thumbnail);
    }

    /**
     * Get media file path
     */
    private function getMediaFilePath($media_id)
    {
        $metadata = wp_get_attachment_metadata($media_id);
        
        if (isset($metadata['file']) && $metadata['file']) {
            return strstr($this->upload_dir['basedir'] . '/' . $metadata['file'], '/uploads/');
        }

        $file_path = get_attached_file($media_id);
        if ($file_path) {
            $relative_path = str_replace($this->upload_dir['basedir'], '', $file_path);
            return '/uploads' . $relative_path;
        }

        return "Not found";
    }

    /**
     * Get media file type
     */
    private function getMediaFileType($media_id)
    {
        $metadata = wp_get_attachment_metadata($media_id);
        
        if (isset($metadata['file']) && $metadata['file']) {
            $full_path = $this->upload_dir['basedir'] . '/' . $metadata['file'];
            if (file_exists($full_path)) {
                return wp_get_image_mime($full_path);
            }
        }

        $file_type = wp_check_filetype(get_attached_file($media_id));
        return $file_type['ext'] ?? 'Not found';
    }

    /**
     * Calculate total size for array of media IDs
     */
    private function calculateTotalSize($media_ids)
    {
        if (!is_array($media_ids)) {
            return 0;
        }

        $total_size = 0;
        foreach ($media_ids as $media_id) {
            $total_size += $this->getMediaFileSize($media_id);
        }

        return $total_size;
    }

    /**
     * Calculate file type breakdown
     */
    private function calculateFileTypeBreakdown($media_ids, $total_count)
    {
        if (!is_array($media_ids) || empty($media_ids)) {
            return [];
        }

        $file_type_data = [];

        foreach ($media_ids as $media_id) {
            $mime_type = get_post_mime_type($media_id);
            $file_type = $this->categorizeFileType($mime_type);

            if (!isset($file_type_data[$file_type])) {
                $file_type_data[$file_type] = ['count' => 0, 'size' => 0];
            }

            $file_type_data[$file_type]['count']++;
            $file_type_data[$file_type]['size'] += $this->getMediaFileSize($media_id);
        }

        // Format and calculate percentages
        $breakdown = [];
        foreach ($file_type_data as $type => $data) {
            $percentage = $total_count > 0 ? round(($data['count'] / $total_count) * 100, 1) : 0;
            
            $breakdown[] = [
                'type' => $type,
                'count' => $data['count'],
                'size' => $data['size'],
                'size_formatted' => $this->formatFileSize($data['size']),
                'percentage' => $percentage
            ];
        }

        // Sort by size (largest first)
        usort($breakdown, fn($a, $b) => $b['size'] - $a['size']);

        return $breakdown;
    }

    /**
     * Categorize file type by mime type
     */
    private function categorizeFileType($mime_type)
    {
        if (str_starts_with($mime_type, 'image/')) return 'images';
        if (str_starts_with($mime_type, 'video/')) return 'videos';
        if (str_starts_with($mime_type, 'audio/')) return 'audio';
        if (str_starts_with($mime_type, 'application/')) return 'documents';
        if (str_starts_with($mime_type, 'text/')) return 'text';
        return 'other';
    }

    /**
     * Get total media count
     */
    private function getTotalMediaCount()
    {
        $args = [
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'numberposts' => -1,
            'fields'      => 'ids',
        ];

        return count(get_posts($args));
    }

    /**
     * Get total pages count (all post types except attachment)
     */
    private function getTotalPagesCount()
    {
        $post_types = get_post_types(array(), 'names', 'and');
        // Remove attachment and other system types
        $post_types = array_diff(
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

        $total_count = 0;
        foreach ($post_types as $post_type) {
            $post_counts = wp_count_posts($post_type);
            $total_count += $post_counts->publish
                + ($post_counts->future ?? 0)
                + ($post_counts->draft ?? 0)
                + ($post_counts->pending ?? 0)
                + ($post_counts->private ?? 0)
                + (property_exists($post_counts, 'archive') ? $post_counts->archive : 0);
        }

        return $total_count;
    }

    /**
     * Calculate file size distribution for different thresholds
     * Uses actual filesystem file sizes (same method as media cleaner) for accurate counts
     * 
     * @param bool $force_recalculate If true, bypasses cache and recalculates
     */
    private function calculateFileSizeDistribution($force_recalculate = false)
    {
        global $wpdb;
        
        // Check cache first (cache for 1 hour) unless forced to recalculate
        $cache_key = 'rmc_file_size_distribution';
        
        if (!$force_recalculate) {
            $cached = get_transient($cache_key);
            if ($cached !== false && is_array($cached) && isset($cached[0])) {
            // Validate cached data - ensure 0 threshold matches current total and has valid data
            $current_total = (int)$wpdb->get_var("
                SELECT COUNT(*) FROM {$wpdb->posts} 
                WHERE post_type = 'attachment' 
                AND post_status = 'inherit'
            ");
            // If cached total doesn't match current total, or if cached data is all zeros, recalculate
            $has_valid_data = false;
            foreach ($cached as $key => $value) {
                if ($key > 0 && $value > 0) {
                    $has_valid_data = true;
                    break;
                }
            }
            
            if ($cached[0] == $current_total && $has_valid_data) {
                error_log("File size distribution: Using cached data");
                return $cached;
            }
                // Clear invalid cache
                error_log("File size distribution: Clearing invalid cache. Total match: " . ($cached[0] == $current_total ? 'yes' : 'no') . ", Has valid data: " . ($has_valid_data ? 'yes' : 'no'));
                delete_transient($cache_key);
            }
        } else {
            // Force recalculate - clear cache
            error_log("File size distribution: Force recalculating - clearing cache");
            delete_transient($cache_key);
        }
        
        $thresholds = [0, 1, 5, 10]; // MB thresholds (0 = total, others match frontend display)
        $distribution = [];
        
        // Get all attachment IDs
        $attachment_ids = $wpdb->get_col("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'attachment' 
            AND post_status = 'inherit'
        ");
        
        $total_attachments = count($attachment_ids);
        $distribution[0] = $total_attachments;
        
        if ($total_attachments == 0) {
            // Return empty distribution if no attachments
            foreach ($thresholds as $threshold_mb) {
                if ($threshold_mb > 0) {
                    $distribution[$threshold_mb] = 0;
                }
            }
            return $distribution;
        }
        
        // Count files for each threshold using actual filesystem sizes
        // Process all files once and check against all thresholds simultaneously for efficiency
        $counts_by_threshold = [];
        foreach ($thresholds as $threshold_mb) {
            if ($threshold_mb > 0) {
                $counts_by_threshold[$threshold_mb] = 0;
            }
        }
        
        $processed = 0;
        $files_found = 0;
        $files_missing = 0;
        $batch_size = 500; // Process in larger batches
        $batches = array_chunk($attachment_ids, $batch_size);
        
        error_log("File size distribution: Starting calculation for {$total_attachments} files");
        
        foreach ($batches as $batch_index => $batch) {
            foreach ($batch as $attachment_id) {
                $filesize_bytes = 0;
                
                // Try multiple methods to get file size (same as media cleaner logic)
                $metadata = wp_get_attachment_metadata($attachment_id);
                
                // Method 1: Check metadata filesize first
                if (isset($metadata['filesize']) && $metadata['filesize'] > 0) {
                    $filesize_bytes = (int)$metadata['filesize'];
                }
                // Method 2: Try file path from metadata
                elseif (isset($metadata['file']) && $metadata['file']) {
                    $file_path = $this->upload_dir['basedir'] . '/' . $metadata['file'];
                    if (file_exists($file_path)) {
                        $filesize_bytes = @filesize($file_path);
                    }
                }
                // Method 3: Fallback to get_attached_file
                if ($filesize_bytes == 0) {
                    $file_path = get_attached_file($attachment_id);
                    if ($file_path && file_exists($file_path)) {
                        $filesize_bytes = @filesize($file_path);
                    }
                }
                
                if ($filesize_bytes > 0) {
                    $files_found++;
                    // Convert to MB (same calculation as media cleaner: bytes / 1024 / 1024)
                    $filesize_mb = $filesize_bytes / 1024 / 1024;
                    
                    // Check against all thresholds at once (using >= for greater than or equal to)
                    foreach ($thresholds as $threshold_mb) {
                        if ($threshold_mb > 0 && $filesize_mb >= $threshold_mb) {
                            $counts_by_threshold[$threshold_mb]++;
                        }
                    }
                } else {
                    $files_missing++;
                    // Log first few missing files for debugging
                    if ($files_missing <= 5) {
                        error_log("File size distribution: File not found for ID {$attachment_id}. Metadata file: " . (isset($metadata['file']) ? $metadata['file'] : 'none') . ", get_attached_file: " . (get_attached_file($attachment_id) ?: 'none'));
                    }
                }
                $processed++;
            }
            
            // Log progress every 10 batches
            if (($batch_index + 1) % 10 == 0) {
                error_log("File size distribution: Processed {$processed}/{$total_attachments} files. Found: {$files_found}, Missing: {$files_missing}");
            }
            
            // If we've processed a lot but found no files, something is wrong
            // But try a larger sample first (maybe files are in later batches)
            if ($processed >= 5000 && $files_found == 0) {
                error_log("File size distribution: WARNING - Processed {$processed} files but found 0 files. This suggests files may not exist on filesystem. Stopping calculation.");
                break; // Exit early - don't scale zeros
            }
            
            // If we've found some files and processed enough, we can scale the rest
            if ($files_found > 0 && $processed >= 5000 && $processed < $total_attachments && ($processed / $total_attachments) < 0.5) {
                // If we've processed 5000+ files but less than 50%, estimate the rest
                $processed_ratio = $processed / $total_attachments;
                
                error_log("File size distribution: Scaling results. Processed {$processed}/{$total_attachments} ({$processed_ratio}), Files found: {$files_found}");
                
                foreach ($thresholds as $threshold_mb) {
                    if ($threshold_mb > 0 && $counts_by_threshold[$threshold_mb] > 0) {
                        // Scale up the counts proportionally
                        $counts_by_threshold[$threshold_mb] = (int)($counts_by_threshold[$threshold_mb] / $processed_ratio);
                    }
                }
                break; // Exit early after scaling
            }
        }
        
        error_log("File size distribution: Completed. Processed {$processed}/{$total_attachments}. Files found: {$files_found}, Missing: {$files_missing}");
        
        // Remove any [0] key from counts_by_threshold if it exists (shouldn't, but safeguard)
        if (isset($counts_by_threshold[0])) {
            unset($counts_by_threshold[0]);
        }
        
        error_log("File size distribution: Counts by threshold: " . print_r($counts_by_threshold, true));
        
        // Initialize distribution array - [0] is always the total, never scaled
        $distribution = [];
        $distribution[0] = $total_attachments; // Always set to total, not scaled
        
        // Set distribution values for each threshold (only for thresholds > 0)
        foreach ($thresholds as $threshold_mb) {
            if ($threshold_mb > 0) {
                $distribution[$threshold_mb] = $counts_by_threshold[$threshold_mb] ?? 0;
            }
        }
        
        // Final safeguard: ensure [0] is always the total (in case anything overwrote it)
        $distribution[0] = $total_attachments;
        
        error_log("File size distribution: Final distribution (total={$total_attachments}): " . print_r($distribution, true));
        
        // Only cache if we have valid data (not all zeros)
        $has_valid_distribution = false;
        foreach ($distribution as $key => $value) {
            if ($key > 0 && $value > 0) {
                $has_valid_distribution = true;
                break;
            }
        }
        
        if ($has_valid_distribution && $distribution[0] > 0) {
            // Cache the result for 1 hour
            set_transient($cache_key, $distribution, HOUR_IN_SECONDS);
            error_log("File size distribution: Cached valid distribution");
        } else {
            error_log("File size distribution: NOT caching - distribution is invalid or all zeros");
            // Clear any existing bad cache
            delete_transient($cache_key);
        }
        
        return $distribution;
    }

    /**
     * Get current file size setting in MB
     * Defaults to 1MB if not set (for stats calculation)
     */
    private function getCurrentFileSizeSetting()
    {
        $file_size_bytes = get_option('rbp_media_cleaner_file_size', 0);
        if ($file_size_bytes == 0) {
            // Default to 1MB if not set (for stats/recommendations)
            return 1.0;
        }
        return $file_size_bytes / 1048576; // Convert bytes to MB
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) return ceil($bytes / 1073741824) . ' GB';
        if ($bytes >= 1048576) return ceil($bytes / 1048576) . ' MB';
        if ($bytes >= 1024) return ceil($bytes / 1024) . ' KB';
        return $bytes . ' bytes';
    }

    /**
     * Format size units (assuming this function exists)
     */
    private function formatSizeUnits($bytes)
    {
        // This function should be defined elsewhere in your codebase
        // Using the same logic as formatFileSize for now
        return $this->formatFileSize($bytes);
    }

    /**
     * Get all supported mime types
     */
    private function getAllMimeTypes()
    {
        return [
            'svg' => 'image/svg+xml',
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpe',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp3|m4a|m4b' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'wma' => 'audio/x-ms-wma',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            'asf|asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            'js' => 'application/javascript',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'rar' => 'application/rar',
            'txt|asc|c|cc|h|srt' => 'text/plain',
            'csv' => 'text/csv',
        ];
    }
}

// Main function that WordPress REST API calls
function ronikdesignsbase_mediacleaner_data($data)
{
    $handler = new MediaCleanerDataHandler();
    return $handler->handleRequest($data);
}

// function my_custom_permission_check()
// {
//     return current_user_can('manage_options');
// }

register_rest_route('mediacleaner/v1', '/mediacollector/(?P<slug>\w+)', [
    'methods' => 'GET',
    'callback' => 'ronikdesignsbase_mediacleaner_data',
    // 'permission_callback' => 'my_custom_permission_check',
]);