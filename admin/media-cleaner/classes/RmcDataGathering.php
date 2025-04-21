<?php

namespace Ronik\Base;

use Ronik\Base\RbpHelper;

class RmcDataGathering
{
    public function rmc_reset_alldata()
    {
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
        delete_option('rbp_media_cleaner_sync-time');
        delete_option('rbp_media_cleaner_sync_running-time');
        update_option('rbp_media_cleaner_sync_running', 'not-running');
        error_log(print_r('Completed Reseting Everything!', true));
    }

    public function rmc_getLineWithString_ronikdesigns($fileName, $id)
    {
        $f_attached_file = get_attached_file($id);
        $pieces = explode('/', $f_attached_file);
        $lines = file(urldecode($fileName));
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, end($pieces)) !== false) {
                return $id;
            }
        }
    }

    // This function pretty much scans all the files of the entire active theme.
    // We try to ignore files that are not using images within.
    public function rmc_receiveAllFiles_ronikdesigns($dir, $image_id)
    {
        $result = array();
        $array_disallow = array("functions.php", "package.json", "package-lock.json", ".", "..", ".DS_Store", "README.md", "composer.json", "composer.lock", ".gitkeep", "node_modules", "vendor");
        $results = array_diff(scandir($dir), $array_disallow);
        $results_reindexed = array_values(array_filter($results));
        $image_ids = '';
        if ($results_reindexed) {
            foreach ($results_reindexed as $key => $value) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$dir . DIRECTORY_SEPARATOR . $value] = $this->rmc_receiveAllFiles_ronikdesigns($dir . DIRECTORY_SEPARATOR . $value,  $image_id);
                } else {
                    $result[] = $value;
                    if ($this->rmc_getLineWithString_ronikdesigns(urlencode($dir . DIRECTORY_SEPARATOR . $value), $image_id)) {
                        // Unfortunately we have to use the super global variable
                        $_POST['imageDirFound'] = $this->rmc_getLineWithString_ronikdesigns(urlencode($dir . DIRECTORY_SEPARATOR . $value), $image_id);
                    }
                }
            }
        }
        if (isset($_POST['imageDirFound'])) {
            return $_POST['imageDirFound'];
        } else {
            return;
        }
    }


    // postTypesRetrieval retrieves all the post types and custom post types of the entire site.
    public function postTypesRetrieval()
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 12a, postTypesRetrieval retrieves all the post types and custom post types of the entire site. ', 'low', 'rbp_media_cleaner');
        $post_types = get_post_types(array(), 'names', 'and');
        // We remove a few of the deafult types to help with speed cases..
        $post_types_without_defaults = array_diff(
            $post_types,
            array(
                'contact-form',
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
                'acf-ui-options-page'
            )
        );
        $post_types_arrays = array();
        foreach ($post_types_without_defaults as $key => $value) {
            array_push($post_types_arrays, $value);
        }
        return $post_types_arrays;
    }



    // A simple data transient function.
    public function dataTransient($transientName, $targetData)
    {
        $transient = get_transient($transientName);
        if (! empty($transient)) {
            $data = $transient;
        } else {
            $data = $targetData;
            // Save the response so we don't have to call again until tomorrow.
            set_transient($transientName, $data, DAY_IN_SECONDS);
        }
        return $data;
    }



    // Function that returns all post ids
    public function postIDCollector($select_post_status, $select_post_type)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('postIDCollector: Ref 1a postIDCollector Started ', 'low', 'rbp_media_cleaner');

        $counter = 0;
        if ($select_post_type) {
            $count_posts = array();
            foreach ($select_post_type as $post_type) {
                $post_counts = wp_count_posts($post_type);
                $archive_count = property_exists($post_counts, 'archive') ? $post_counts->archive : 0;
                $count_posts[] = $post_counts->publish
                    + $post_counts->future
                    + $post_counts->draft
                    + $post_counts->pending
                    + $post_counts->private
                    + $archive_count;
            }
            foreach ($count_posts as $count_post) {
                $counter = $counter + $count_post;
            }
        }
        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $select_numberposts = 35;
        $throttle_detector = $counter;
        $maxIncrement = ceil($throttle_detector / $select_numberposts);

        // Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
        function postIDCollector($select_post_status, $select_post_type, $offsetValue, $select_numberposts)
        {
            $all_post_pages = get_posts(array(
                'post_type' => $select_post_type,
                'fields' => 'ids',
                'post_status'  => $select_post_status,
                'offset' => $offsetValue,
                'numberposts' => $select_numberposts,
                'orderby' => 'date',
                'order'  => 'DESC',
            ));
            return $all_post_pages;
        }

        // We throttle the number of images so it doesnt kill the server.
        $rmc_data_collectors_post_ids_array = array();
        $numbers = range(0, $maxIncrement);
        foreach ($numbers as $number) {
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;
            $rmc_data_collectors_post_ids_array[] = postIDCollector($select_post_status, $select_post_type, $offsetValue, $select_numberposts);
        }

        $rbpHelper->ronikdesigns_write_log_devmode('postIDCollector: Ref 1b postIDCollector Done ', 'low', 'rbp_media_cleaner');

        // Merge and filter and reindex.
        return array_values(array_filter(array_merge(...$rmc_data_collectors_post_ids_array)));
    }


    // Function that returns all Image IDs
    public function imageIDCollector($select_attachment_type, $select_numberposts, $file_size, $maxIncrement)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imageIDCollector: Ref 1a imageIDCollector Started ', 'low', 'rbp_media_cleaner');

        // Get all Image IDs.
        function imgIDCollector($select_attachment_type, $offsetValue, $select_numberposts, $file_size)
        {
            $allimagesid = get_posts(array(
                'post_type' => 'attachment',
                'fields' => 'ids',
                'post_mime_type' => $select_attachment_type,
                'offset' => $offsetValue,
                'numberposts' => $select_numberposts,
                'orderby' => 'date',
                'order'  => 'DESC',
            ));

            if ($allimagesid) {
                $all_image_ids = array();
                foreach ($allimagesid as $imageID) {
                    $data = wp_get_attachment_metadata($imageID); // get the data structured
                    if (isset($data['rbp_media_cleaner_isdetached']) && $data['rbp_media_cleaner_isdetached'] == 'rbp_media_cleaner_isdetached_temp-saved') {
                    } else {
                        $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_false';
                        wp_update_attachment_metadata($imageID, $data);  // save it back to the db

                        if (file_exists(get_attached_file($imageID))) {
                            // finds the total file / image size
                            $filesize = filesize(get_attached_file($imageID));
                            // converts bits to mega bytes
                            $filesize_convert = $filesize / 1024 / 1024;
                            // converts number to format based on locale
                            $filesize  = number_format_i18n($filesize_convert, 3);
                            // creates new meta field with file size of an image
                            update_post_meta($imageID, '_wp_attachment_image_filesize', $filesize);

                            // $all_image_ids[] = $imageID;
                            // This is responsible for only getting the large images rather then the tiny ones.
                            if (filesize(get_attached_file($imageID)) >= $file_size) {
                                // error_log(print_r( 'ssss', true));
                                $all_image_ids[] = $imageID;
                            }
                        }
                    }


                    // if (isset($data['rbp_media_cleaner_isdetached']) && $data['rbp_media_cleaner_isdetached'] !== 'rbp_media_cleaner_isdetached_temp-saved') {

                    //     $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_false';
                    //     wp_update_attachment_metadata($imageID, $data);  // save it back to the db

                    //     if (file_exists(get_attached_file($imageID))) {
                    //         // finds the total file / image size
                    //         $filesize = filesize(get_attached_file($imageID));
                    //         // converts bits to mega bytes
                    //         $filesize_convert = $filesize / 1024 / 1024;
                    //         // converts number to format based on locale
                    //         $filesize  = number_format_i18n($filesize_convert, 3);
                    //         // creates new meta field with file size of an image
                    //         update_post_meta($imageID, '_wp_attachment_image_filesize', $filesize);

                    //         // $all_image_ids[] = $imageID;
                    //         // This is responsible for only getting the large images rather then the tiny ones.
                    //         if (filesize(get_attached_file($imageID)) >= $file_size) {
                    //             // error_log(print_r( 'ssss', true));
                    //             $all_image_ids[] = $imageID;
                    //         }
                    //     }
                    // }




                }
                return $all_image_ids;
            }
        }
        // We throttle the number of images so it doesnt kill the server.
        $rmc_data_collectors_ids_array = array();
        $numbers = range(0, $maxIncrement);
        foreach ($numbers as $number) {
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;
            $rmc_data_collectors_ids_array[$number] = imgIDCollector($select_attachment_type, $offsetValue, $select_numberposts, $file_size);
        }

        $rbpHelper->ronikdesigns_write_log_devmode('imageIDCollector: Ref 1b imageIDCollector Done ', 'low', 'rbp_media_cleaner');

        return array_values(array_filter(array_merge(...array_filter($rmc_data_collectors_ids_array))));
    }




    // Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
    public function specificImageThumbnailAuditor($specificPageID, $allimagesid)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('specificImageThumbnailAuditor: Ref 1a imageThumbnailAuditor Started ', 'low', 'rbp_media_cleaner');

        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $select_numberposts = 35;
        $throttle_detector_attachement = count($allimagesid);
        $maxIncrement_attachement = ceil($throttle_detector_attachement / $select_numberposts);

        if (!function_exists('specificImageAttachement')) {
            function specificImageAttachement($allimagesid)
            {
                $all_image_attachement_ids = array();
                if ($allimagesid) {
                    foreach ($allimagesid as $image_id) {
                        if (wp_get_post_parent_id($image_id)) {
                            // $all_image_attachement_ids[] = $all_image_attachement_ids[] = $image_id;
                            $all_image_attachement_ids[] = $image_id;
                        }
                    }
                }
                return $all_image_attachement_ids;
            }
        }
        // We throttle the number of images so it doesnt kill the server.
        $all_image_attachement_ids_array = array();
        $numbers_attachement = range(0, $maxIncrement_attachement);
        foreach ($numbers_attachement as $number) {
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;
            $allimagesid_array = array_slice($allimagesid, $offsetValue, $select_numberposts, true);
            $all_image_attachement_ids_array[] = specificImageAttachement($allimagesid_array);
        }
        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter(array_merge(...$all_image_attachement_ids_array))));
        $rbpHelper->ronikdesigns_write_log_devmode('specificImageThumbnailAuditor: Ref 1b imageThumbnailAuditor Checkpoint 1a DONE ', 'low', 'rbp_media_cleaner');

        $all_post_thumbnail_ids = array();
        if (get_post_thumbnail_id($specificPageID)) {
            $all_post_thumbnail_ids[] = get_post_thumbnail_id($specificPageID);
        }

        $arr_checkpoint_1b = cleaner_compare_array_diff($arr_checkpoint_1a, array_values(array_filter($all_post_thumbnail_ids)));
        $rbpHelper->ronikdesigns_write_log_devmode('specificImageThumbnailAuditor: Ref 1c imageThumbnailAuditor Checkpoint 1b DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1b;
    }




    // Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
    public function imageThumbnailAuditor($get_all_post_pages, $allimagesid, $select_attachment_type)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imageThumbnailAuditor: Ref 1a imageThumbnailAuditor Started ', 'low', 'rbp_media_cleaner');

        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $select_numberposts = 35;
        $throttle_detector_attachement = count($allimagesid);
        $maxIncrement_attachement = ceil($throttle_detector_attachement / $select_numberposts);

        function imageAttachement($allimagesid)
        {
            $all_image_attachement_ids = array();
            if ($allimagesid) {
                foreach ($allimagesid as $image_id) {
                    if (wp_get_post_parent_id($image_id)) {
                        // $all_image_attachement_ids[] = $all_image_attachement_ids[] = $image_id;
                        $all_image_attachement_ids[] = $image_id;
                    }
                }
            }
            return $all_image_attachement_ids;
        }
        // We throttle the number of images so it doesnt kill the server.
        $all_image_attachement_ids_array = array();
        $numbers_attachement = range(0, $maxIncrement_attachement);
        foreach ($numbers_attachement as $number) {
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;
            $allimagesid_array = array_slice($allimagesid, $offsetValue, $select_numberposts, true);
            $all_image_attachement_ids_array[] = imageAttachement($allimagesid_array);
        }
        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter(array_merge(...$all_image_attachement_ids_array))));
        $rbpHelper->ronikdesigns_write_log_devmode('imageThumbnailAuditor: Ref 1b imageThumbnailAuditor DONE ', 'low', 'rbp_media_cleaner');


        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $throttle_detector_thumbnail = count($allimagesid);
        $maxIncrement_thumbnail = ceil($throttle_detector_thumbnail / $select_numberposts);
        function postThumbnail($get_all_post_pages)
        {
            $all_post_thumbnail_ids = array();
            if ($get_all_post_pages) {
                foreach ($get_all_post_pages as $pageID) {
                    if (get_post_thumbnail_id($pageID)) {
                        $all_post_thumbnail_ids[] = get_post_thumbnail_id($pageID);
                    }
                }
            }
            return $all_post_thumbnail_ids;
        }
        // We throttle the number of images so it doesnt kill the server.
        $all_post_thumbnail_ids_array = array();
        $numbers_thumbnail = range(0, $maxIncrement_thumbnail);
        foreach ($numbers_thumbnail as $number) {
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;
            $get_all_post_pages_array = array_slice($get_all_post_pages, $offsetValue, $select_numberposts, true);
            $all_post_thumbnail_ids_array[] = postThumbnail($get_all_post_pages_array);
        }

        $arr_checkpoint_1b = cleaner_compare_array_diff($arr_checkpoint_1a, array_values(array_filter(array_merge(...$all_post_thumbnail_ids_array))));
        $rbpHelper->ronikdesigns_write_log_devmode('imageThumbnailAuditor: Ref 1c imageThumbnailAuditor DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1b;
    }




    // Check the image id, the og file path, and the image base name.
    public function specificImagePostAuditor($allimagesid, $specificPageID)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('specificImagePostAuditor: Ref 1a imagePostAuditor Started ', 'low', 'rbp_media_cleaner');

        // We do a very loose search for the image id in the post_meta value.
        // At the same time we search for the image file path in the post_meta value.
        $wp_postsmeta_id_audit_array = array();
        if ($allimagesid) {
            foreach ($allimagesid as $j => $image_id) {
                $f_posts = get_posts(array(
                    'fields' => 'ids',
                    'posts_per_page' => 1,
                    'include' => $specificPageID,
                    'meta_query' => array(
                        array(
                            'value' => '(?:^|\W)' . $image_id . '(?:$|\W)' . '|' . 'i:' . $image_id . ';' . '|' . get_attached_file($image_id) . '|' . basename(get_attached_file($image_id)),
                            'compare' => 'REGEXP',
                        ),
                        'relation' => 'AND',
                        array(
                            'key' => '_wp_attachment_backup_sizes',
                            'compare' => '!='
                        )
                    ),
                    'orderby' => 'date',
                    'order'  => 'DESC',
                ));

                if ($f_posts) {
                    foreach ($f_posts as $key => $posts) {
                        if ($posts) {
                            $wp_postsmeta_id_audit_array[] = $image_id;
                        }
                    }
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_id_audit_array)));
        $rbpHelper->ronikdesigns_write_log_devmode('specificImagePostAuditor: Ref 1b imagePostAuditor DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1a;
    }





    // Check the image id, the og file path, and the image base name.
    public function imagePostAuditor($allimagesid, $all_post_pages, $select_post_status, $select_post_type)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imagePostAuditor: Ref 1a imagePostAuditor Started ', 'low', 'rbp_media_cleaner');

        // We do a very loose search for the image id in the post_meta value.
        // At the same time we search for the image file path in the post_meta value.
        $wp_postsmeta_id_audit_array = array();
        if ($allimagesid) {
            foreach ($allimagesid as $j => $image_id) {
                $f_posts = get_posts(array(
                    'fields' => 'ids',
                    'post_type' => $select_post_type,
                    'post_status'  => $select_post_status,
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        array(
                            'value' => '(?:^|\W)' . $image_id . '(?:$|\W)' . '|' . 'i:' . $image_id . ';' . '|' . get_attached_file($image_id) . '|' . basename(get_attached_file($image_id)),
                            'compare' => 'REGEXP',
                        ),
                        'relation' => 'AND',
                        array(
                            'key' => '_wp_attachment_backup_sizes',
                            'compare' => '!='
                        )
                    ),
                    'orderby' => 'date',
                    'order'  => 'DESC',
                ));

                if ($f_posts) {
                    foreach ($f_posts as $key => $posts) {
                        if ($posts) {
                            $wp_postsmeta_id_audit_array[] = $image_id;
                        }
                    }
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_id_audit_array)));
        $rbpHelper->ronikdesigns_write_log_devmode('imagePostAuditor: Ref 1b imagePostAuditor DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1a;
    }




    // Check the post content and do a loose find if the basename is within the post content. This is most ideal for gutenberg blocks.
    public function specificImagePostContentAuditor($allimagesid, $post_id)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('specificImagePostContentAuditor: Ref 1a imagePostContentAuditor Started ', 'low', 'rbp_media_cleaner');

        $helper = new RonikBaseHelper;
        // This searches the posts content
        // Lets get the post meta of all posts...
        $wp_postsmeta_wp_content_id_audit_array = array();

        if ($allimagesid) {
            foreach ($allimagesid as $k => $image_id) {
                //  We do a loose comparison if the meta value has any keyword of en.
                if ($helper->ronik_compare_like(get_post_field('post_content', $post_id), basename(get_attached_file($image_id)))) {
                    error_log(print_r($k, true));
                    $wp_postsmeta_wp_content_id_audit_array[] = $image_id;
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_wp_content_id_audit_array)));
        $rbpHelper->ronikdesigns_write_log_devmode('specificImagePostContentAuditor: Ref 1b imagePostContentAuditor DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1a;
    }







    // Check the post content and do a loose find if the basename is within the post content. This is most ideal for gutenberg blocks.
    public function imagePostContentAuditor($allimagesid, $all_post_pages)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imagePostContentAuditor: Ref 1a imagePostContentAuditor Started ', 'low', 'rbp_media_cleaner');

        $helper = new RonikBaseHelper;

        // This searches the posts content
        // Lets get the post meta of all posts...
        $wp_postsmeta_wp_content_id_audit_array = array();
        if ($all_post_pages) {
            foreach ($all_post_pages as $i => $post_id) {
                if ($allimagesid) {
                    foreach ($allimagesid as $k => $image_id) {
                        //  We do a loose comparison if the meta value has any keyword of en.
                        if ($helper->ronik_compare_like(get_post_field('post_content', $post_id), basename(get_attached_file($image_id)))) {
                            $wp_postsmeta_wp_content_id_audit_array[] = $image_id;
                        }
                    }
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_wp_content_id_audit_array)));
        $rbpHelper->ronikdesigns_write_log_devmode('imagePostContentAuditor: Ref 1a imagePostContentAuditor DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1a;
    }



    public function imagOptionAuditor($allimagesid, $all_post_pages, $select_post_status, $select_post_type)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1a imagOptionAuditor Started ', 'low', 'rbp_media_cleaner');

        // Define the function before using it
        function search_value_in_option($option_value, $search_term, $depth = 0)
        {
            $rbpHelper = new RbpHelper;

            if ($depth > 10) { // Limit the recursion depth to prevent infinite loops
                $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1b iMax recursion depth reached. ', 'low', 'rbp_media_cleaner');
                return false;
            }
            if (is_array($option_value) || is_object($option_value)) {
                foreach ($option_value as $value) {
                    if (search_value_in_option($value, $search_term, $depth + 1)) {
                        return true;  // Match found, exit immediately
                    }
                }
            } elseif (is_string($option_value)) {
                return strpos($option_value, $search_term) !== false;  // Return true or false based on match
            }
            return false; // If no match found in the entire search, return false
        }

        // Your main code
        $wp_option_id_audit_array = array();
        global $wpdb;
        $all_options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options");
        // Number of options to process in each chunk to avoid overwhelming the server
        $chunk_size = 35; // Adjust this number to throttle processing
        $total_options = count($all_options);
        $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1c Total options count: ' . $total_options, 'low', 'rbp_media_cleaner');

        // Process the chunks of all options directly
        foreach (array_chunk($all_options, $chunk_size) as $chunk_index => $option_chunk) {
            $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1d Processing chunk: ' . ($chunk_index + 1), 'low', 'rbp_media_cleaner');
            foreach ($option_chunk as $i => $option) {
                $option_name = $option->option_name;
                // Additional logging to catch unserialization issues
                $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1e Attempting to unserialize option: ' . $option_name, 'low', 'rbp_media_cleaner');
                $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1f Option value before unserialize: ' . print_r($option->option_value, true), 'low', 'rbp_media_cleaner');
                $option_value = maybe_unserialize($option->option_value);

                // Check if unserialization failed
                if ($option_value === false && $option->option_value !== 'b:0;') {
                    $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1g Failed to unserialize option: ' . $option_name, 'low', 'rbp_media_cleaner');
                    continue;
                }

                // Log the unserialized option value for debugging
                $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1h Option value after unserialize: ' . print_r($option_value, true), 'low', 'rbp_media_cleaner');

                // Skip if empty aka false
                if (!$option_value) {
                    $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1i Skipped empty option: ' . $option_name, 'low', 'rbp_media_cleaner');
                    continue;
                }

                // Skip any option name that starts with an underscore (_)
                if (strpos($option_name, '_') === 0) {
                    $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1j Skipped system option: ' . $option_name, 'low', 'rbp_media_cleaner');
                    continue;
                }

                // Skip certain options based on their name
                $skipped_patterns = [
                    'start_of_week',
                    'use_smilies',
                    'use_balanceTags',
                    'permalink_structure',
                    'category_base',
                    'tag_base',
                    'blog_charset',
                    'blogname',
                    'blogdescription',
                    'admin_email',
                    'default_category',
                    'default_post_format',
                    'default_pingback_flag',
                    'default_ping_status',
                    'default_comment_status',

                    'thumbnail_size_',
                    'medium_size_',
                    'large_size_',
                    'uploads_use_yearmonth_folders',
                    'image_default_align',
                    'image_default_size',
                    'image_default_link_type',

                    'comment_',
                    'comments_notify',
                    'moderation_keys',
                    'comment_max_links',
                    'comment_registration',
                    'require_name_email',
                    'comment_whitelist',
                    'comment_moderation',
                    'comment_order',
                    'thread_comments',

                    '_transient_',                     // All transient values (both site & user)
                    '_site_transient_',               // Site-wide transients
                    '_transient_timeout_',            // Transient timeouts
                    '_site_transient_timeout_',

                    '_theme_',                         // Theme mod values
                    'widget_',                         // All widget data
                    'sidebars_widgets',               // Sidebar configuration
                    'uninstall_plugins',              // Plugin cleanup lists
                    'recently_activated',             // Plugin activation memory

                    'rewrite_rules',                  // URL rewrites
                    'cron',                           // WP-Cron event data
                    'core_updater',                   // WP core updater state
                    'auto_update_',                   // Any auto-update setting
                    'dismissed_update_',             // Skipped/dismissed updates
                    'site_icon',                      // Site favicon ID
                    'can_compress_scripts',          // JS compression cache
                    'avatar_default',                // Default avatar
                    'show_avatars',                  // Avatar toggle
                    'medium_crop',                   // Image crop settings
                    'image_default_',                // Default image settings

                    'options_whitelist_domains_',
                    'options_press_whitelists_',
                    'options_pat_auto_ignore_domains_',

                    'wpesu-plugin-genesis-blocks',
                    'posts_per_page',
                    'posts_per_rss'
                ];


                $skip_option = false;
                foreach ($skipped_patterns as $pattern) {
                    if (strpos($option_name, $pattern) === 0) {
                        $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1k Skipped specific option: ' . $option_name, 'low', 'rbp_media_cleaner');
                        $skip_option = true;
                        break;
                    }
                }
                if ($skip_option) {
                    continue;
                }

                $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1l Processing option: ' . $option_name, 'low', 'rbp_media_cleaner');

                foreach ($allimagesid as $image_id) {
                    $pattern_id = '/(?<![\d.])(?<![a-zA-Z])' . preg_quote((string)$image_id, '/') . '(?![\d.])/';

                    $pattern_serialized = '/i:' . $image_id . ';/'; // Serialized image ID pattern
                    $pattern_file_path = get_attached_file($image_id); // Full file path
                    $pattern_file_name = basename($pattern_file_path); // File name

                    // Handle relative paths
                    $uploads_position = strpos($pattern_file_path, '/uploads/');
                    $relative_path = $uploads_position !== false
                        ? substr($pattern_file_path, $uploads_position)
                        : $pattern_file_path;

                    $pattern_ids = '"id":' . $image_id;

                    $match_found = false;


                    if (is_string($option_value) && preg_match($pattern_id, $option_value, $matches)) {

                        error_log('Matched option_name: ' . print_r($option_name, true));
                        error_log('Matched option_value: ' . print_r($option_value, true));
                        error_log('Matched imagOptionAuditor: ' . print_r($matches, true));
                    }
                    // if (is_array($option_value)) {
                    //     $flattened = json_encode($option_value);
                    //     if (preg_match($pattern_id, $flattened, $matches)) {
                    //         error_log('Matched in array via json_encode: ' . print_r($matches, true));
                    //     }
                    // }


                    // Check for image ID matches in various formats
                    if (is_string($option_value)) {
                        if (
                            preg_match($pattern_id, $option_value)
                            || preg_match($pattern_serialized, $option_value)
                            || strpos($option_value, $pattern_file_path) !== false
                            || strpos($option_value, $relative_path) !== false
                        ) {
                            $wp_option_id_audit_array[] = $image_id;
                            $match_found = true;
                        }
                    } elseif (search_value_in_option($option_value, $pattern_ids, 0)) { // Start recursion with depth 0
                        $wp_option_id_audit_array[] = $image_id;
                        $match_found = true;
                    } else {
                        if (search_value_in_option($option_value, $relative_path, 0)) { // Start recursion with depth 0
                            $wp_option_id_audit_array[] = $image_id;
                            $match_found = true;
                        }
                    }
                    if ($match_found) {
                        $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1m Match found for image id: ' . $image_id . ' in option:' . $option_name, 'low', 'rbp_media_cleaner');
                    }
                }
                // Additional check to log progress within the chunk
                $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1n Processed option index:' . $i . ' in chunk:' . ($chunk_index + 1), 'low', 'rbp_media_cleaner');
            }
            $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1o Finished processing chunk ' . ($chunk_index + 1), 'low', 'rbp_media_cleaner');
            // Throttle by introducing a short sleep time between chunks (e.g., 1 second)
            sleep(1); // Adjust the sleep time as necessary for your server load
        }
        $rbpHelper->ronikdesigns_write_log_devmode('imagOptionAuditor: Ref 1p Processing completed. Total matches found: ' . count($wp_option_id_audit_array), 'low', 'rbp_media_cleaner');

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_option_id_audit_array)));
        $rbpHelper->ronikdesigns_write_log_devmode('imagePostAuditor: Ref 1b imageOptionAudit DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1a;
    }


    // Check all the files for the image.
    public function imageFilesystemAudit($allimagesid)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imageFilesystemAudit: Ref 1a imageFilesystemAudit Started ', 'low', 'rbp_media_cleaner');
        $rbpHelper->ronikdesigns_write_log_devmode('imageFilesystemAudit: Ref 1b imageFilesystemAudit ' . get_theme_file_path(), 'low', 'rbp_media_cleaner');

        $wp_infiles_array = array();
        if ($allimagesid) {
            foreach ($allimagesid as $image_id) {
                $wp_infiles_array[] = $this->rmc_receiveAllFiles_ronikdesigns(get_theme_file_path(), $image_id);
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_unique(array_filter($wp_infiles_array))));
        $rbpHelper->ronikdesigns_write_log_devmode('imageFilesystemAudit: Ref 1c imageFilesystemAudit DONE ', 'low', 'rbp_media_cleaner');
        return $arr_checkpoint_1a;
    }






    // Check all the files for the image.
    public function imagePreserveAudit($allimagesid)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imagePreserveAudit: Ref 1a imagePreserveAudit Started ', 'low', 'rbp_media_cleaner');

        $meta_temp_saved_array = array();
        if ($allimagesid) {
            foreach ($allimagesid as $image_id) {
                $meta_datas = wp_get_attachment_metadata($image_id); // get the data structured

                if ($meta_datas) {
                    foreach ($meta_datas as $meta_data) {

                        if ($meta_data == 'rbp_media_cleaner_isdetached_temp-saved') {
                            $meta_temp_saved_array[] = $image_id;
                        }
                    }
                }
            }
        }

        if ($meta_temp_saved_array) {
            $rbpHelper->ronikdesigns_write_log_devmode('imagePreserveAudit: Ref 1b imagePreserveAudit  ' . $meta_temp_saved_array, 'low', 'rbp_media_cleaner');
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_unique(array_filter($meta_temp_saved_array))));
        $rbpHelper->ronikdesigns_write_log_devmode('imagePreserveAudit: Ref 1a imagePreserveAudit DONE ', 'low', 'rbp_media_cleaner');

        return $arr_checkpoint_1a;
    }






    public function imageMarker($allimagesid)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('imageMarker: Ref 1a imageMarker Started ', 'low', 'rbp_media_cleaner');

        if ($allimagesid) {
            foreach ($allimagesid as $imageid) {
                $data = wp_get_attachment_metadata($imageid); // get the data structured
                if ($data['rbp_media_cleaner_isdetached'] !== 'rbp_media_cleaner_isdetached_temp-saved') {
                    $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_true';
                    wp_update_attachment_metadata($imageid, $data);  // save it back to the db
                }
            }
        }
        $rbpHelper->ronikdesigns_write_log_devmode('imageMarker: Ref 1b imageMarker DONE ', 'low', 'rbp_media_cleaner');
    }





    public function imageCloneSave($is_array, $imagesid)
    {
        $rbpHelper = new RbpHelper;
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11a, imageCloneSave. ', 'low', 'rbp_media_cleaner');

        $f_file_import = get_option('rbp_media_cleaner_file_import');
        // Update the memory option.
        $helper = new RonikBaseHelper;
        $helper->ronikdesigns_increase_memory();


        if (!$is_array) {
            $rbp_media_cleaner_media_data = array($imagesid);
        } else {
            $rbp_media_cleaner_media_data = $imagesid;
        }

        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11b, imageCloneSave. ' . print_r($rbp_media_cleaner_media_data, true), 'low', 'rbp_media_cleaner');

        if ($f_file_import == 'off' || !isset($f_file_import)) {
            if ($rbp_media_cleaner_media_data) {
                foreach ($rbp_media_cleaner_media_data as $rbp_data_id) {
                    $clone_path = get_post_meta($rbp_data_id, '_wp_attached_file'); // Full path
                    $delete_attachment_clone = wp_delete_attachment(attachment_url_to_postid($clone_path[0]), true);
                    if ($delete_attachment_clone) {


                        //Delete attachment file from disk
                        $clone_file = get_attached_file($clone_path);
                        if ($clone_file && file_exists($clone_file)) {
                            unlink($clone_file);
                        }

                        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11b, imageCloneSave. Clone File Deleted', 'low', 'rbp_media_cleaner');
                    }

                    // Delete attachment from database only, not file
                    $delete_attachment = wp_delete_attachment($rbp_data_id, true);
                    if ($delete_attachment) {
                        //Delete attachment file from disk
                        if (get_attached_file($rbp_data_id)) {
                            unlink(get_attached_file($rbp_data_id));
                        }
                        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11c, imageCloneSave. File Deleted', 'low', 'rbp_media_cleaner');
                    }

                    if ($rbp_data_id == end($rbp_media_cleaner_media_data)) {
                        return true;
                    }
                }
            }
            return true;
        }


        if ($rbp_media_cleaner_media_data) {
            foreach ($rbp_media_cleaner_media_data as $rbp_data_id) {
                $time_stamp = time();
                // First lets copy the full image to the ronikdetached folder.
                $upload_dir   = wp_upload_dir();
                // We must use the get_attached_file function
                $link = get_attached_file($rbp_data_id);
                $file_path = $link;
                $file_name = basename(get_attached_file($rbp_data_id));
                $file_path_date = str_replace($upload_dir['baseurl'], '', $link);
                $file_path_date_mod = str_replace($file_name, '', $file_path_date);
                $file_path_date_mod_array = explode('/wp-content/uploads', $file_path_date_mod);
                $file_path_date_mod_array_reindexed = array_values(array_filter($file_path_date_mod_array));
                if (isset($file_path_date_mod_array_reindexed[1])) {
                    $file_path_date_mod_array_last = explode('/', $file_path_date_mod_array_reindexed[1]);
                    $file_path_date_mod_array_last_reindexed = array_values(array_filter($file_path_date_mod_array_last));
                    //Year in YYYY format.
                    $year = $file_path_date_mod_array_last_reindexed[0];
                    //Month in mm format, with leading zeros.
                    $month = $file_path_date_mod_array_last_reindexed[1];
                    //The folder path for our file should be YYYY/MM/DD
                }

                if (!is_dir(dirname(__FILE__, 2) . '/ronikdetached/')) {
                    //Create our directory.
                    mkdir(dirname(__FILE__, 2) . '/ronikdetached/', 0777, true);
                }

                // Erase old files and database
                if (file_exists(dirname(__FILE__, 2) . '/ronikdetached/archive-database.sql')) {
                    unlink(dirname(__FILE__, 2) . '/ronikdetached/archive-database.sql');
                }
                if (file_exists(dirname(__FILE__, 2) . '/ronikdetached/archive-media.zip')) {
                    unlink(dirname(__FILE__, 2) . '/ronikdetached/archive-media.zip');
                }

                if ($file_path && isset($file_path_date_mod_array_reindexed[1])) {
                    if (file_exists($file_path)) {
                        $zip = new ZipArchive();
                        $filename = dirname(__FILE__, 2) . "/ronikdetached/archive-media.zip";
                        if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
                            exit("cannot open <$filename>\n");
                        }
                        // Add a file new.txt file to zip using the text specified
                        $zip->addFromString('instructions.txt', "Unzip the folder and copy the media back to the mirror path inside the folder.");
                        $zip->addFile($file_path, "$year/$month/" . $file_name);
                        $zip->close();
                    }
                }


                $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11d, imageCloneSave. ' . $rbp_data_id, 'low', 'rbp_media_cleaner');

                $clone_path = get_post_meta($rbp_data_id, '_wp_attached_file'); // Full path
                if (isset($clone_path[0])) {
                    $delete_attachment_clone = wp_delete_attachment(attachment_url_to_postid($clone_path[0]), true);
                    if ($delete_attachment_clone) {
                        //Delete attachment file from disk
                        if (file_exists(get_attached_file($clone_path))) {
                            unlink(get_attached_file($clone_path));
                        }
                        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11e, imageCloneSave. Clone File Deleted ', 'low', 'rbp_media_cleaner');
                    }
                }

                // Delete attachment from database only, not file
                $delete_attachment = wp_delete_attachment($rbp_data_id, true);
                if ($delete_attachment) {
                    //Delete attachment file from disk
                    if (get_attached_file($rbp_data_id)) {
                        if (file_exists(get_attached_file($rbp_data_id))) {
                            unlink(get_attached_file($rbp_data_id));
                        }
                    }
                    $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11f, imageCloneSave.  File Deleted ', 'low', 'rbp_media_cleaner');
                }
            }

            $dbhost = DB_HOST;
            $dbuser = DB_USER;
            $dbpass = DB_PASSWORD;
            $dbname = DB_NAME;
            // https://www.blogdesire.com/create-a-database-backup-and-restore-system-in-php/
            $con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
            if (isset($_POST['backup'])) {
            }
            $tables = array();
            $sql = "SHOW TABLES";
            $result = mysqli_query($con, $sql);
            while ($row = mysqli_fetch_row($result)) {
                $tables[] = $row[0];
            }
            $sqlScript = "";
            foreach ($tables as $table) {
                $query = "SHOW CREATE TABLE $table";
                $result = mysqli_query($con, $query);
                $row = mysqli_fetch_row($result);
                $sqlScript .= "\n\n" . $row[1] . ";\n\n";
                $query = "SELECT * FROM $table";
                $result = mysqli_query($con, $query);
                $columnCount = mysqli_num_fields($result);
                for ($i = 0; $i < $columnCount; $i++) {
                    while ($row = mysqli_fetch_row($result)) {
                        $sqlScript .= "INSERT INTO $table VALUES(";
                        for ($j = 0; $j < $columnCount; $j++) {
                            $row[$j] = $row[$j];
                            if (isset($row[$j])) {
                                $sqlScript .= '"' . mysqli_real_escape_string($con, $row[$j]) . '"';
                            } else {
                                $sqlScript .= '""';
                            }
                            if ($j < ($columnCount - 1)) {
                                $sqlScript .= ',';
                            }
                        }
                        $sqlScript .= ");\n";
                    }
                }
                $sqlScript .= "\n";
            }
            if (!empty($sqlScript)) {
                $backup_file_name =  dirname(__FILE__, 2) . '/ronikdetached/archive-database.sql';
                $fileHandler = fopen($backup_file_name, 'w+');
                $number_of_lines = fwrite($fileHandler, $sqlScript);
                fclose($fileHandler);
                $message = "Backup Created Successfully";
                error_log(print_r($message, true));
                $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 11g, imageCloneSave. BACKUP ' . $message, 'low', 'rbp_media_cleaner');
            }
            return true;
        }
        return true;
    }
}
