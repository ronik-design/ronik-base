<?php 
class RmcDataGathering{


    // Semi Imitates the loose LIKE%% Comparison
    public function ronik_compare_like($a_value , $b_value){
        if(stripos($a_value, $b_value) !== FALSE){
            return true;
        } else {
            return false;
        }
    }

    public function rmc_getLineWithString_ronikdesigns($fileName, $id) {
        $f_attached_file = get_attached_file( $id );
        $pieces = explode('/', $f_attached_file ) ;
        $lines = file( urldecode($fileName) );
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, end($pieces)) !== false) {
                return $id;
            }
        }
    }
    
     // This function pretty much scans all the files of the entire active theme.
        // We try to ignore files that are not using images within.
    public function rmc_receiveAllFiles_ronikdesigns($dir, $image_id){
        $result = array();
        $array_disallow = array("functions.php", "package.json", "package-lock.json", ".", "..", ".DS_Store", "README.md", "composer.json", "composer.lock", ".gitkeep", "node_modules", "vendor");
        $results = array_diff(scandir($dir), $array_disallow);
        $results_reindexed = array_values(array_filter($results));
        $image_ids = '';
        if($results_reindexed){
            foreach ($results_reindexed as $key => $value){
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
                    $result[$dir . DIRECTORY_SEPARATOR . $value] = $this->rmc_receiveAllFiles_ronikdesigns($dir . DIRECTORY_SEPARATOR . $value,  $image_id );
                } else {
                    $result[] = $value;
                    if($this->rmc_getLineWithString_ronikdesigns( urlencode($dir . DIRECTORY_SEPARATOR . $value) , $image_id)){
                        // Unfortunately we have to use the super global variable
                        $_POST['imageDirFound'] = $this->rmc_getLineWithString_ronikdesigns( urlencode($dir . DIRECTORY_SEPARATOR . $value) , $image_id);
                    }
                }
            }
        }
        if(isset($_POST['imageDirFound'])){
            return $_POST['imageDirFound'];
        } else{
            return;
        }
    }


    // postTypesRetrieval retrieves all the post types and custom post types of the entire site.
    public function postTypesRetrieval(){
        $post_types = get_post_types( array(), 'names', 'and' );
        // We remove a few of the deafult types to help with speed cases..
        $post_types_without_defaults = array_diff($post_types,
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
        foreach($post_types_without_defaults as $key => $value) {
            array_push($post_types_arrays, $value);
        }
        return $post_types_arrays;
    }



    // A simple data transient function.
    public function dataTransient( $transientName , $targetData ){
        $transient = get_transient( $transientName );
        if( ! empty( $transient ) ) {
            $data = $transient;
        } else {
            $data = $targetData;
            // Save the response so we don't have to call again until tomorrow.
            set_transient( $transientName , $data, DAY_IN_SECONDS );
        }
        return $data;
    }



    // Function that returns all post ids
    public function postIDCollector($select_post_status, $select_post_type ){
        error_log(print_r('postIDCollector Started' , true));
        $counter = 0; 
        if($select_post_type){
            $count_posts = array();
            foreach($select_post_type as $post_type){
                $count_posts[] = wp_count_posts($post_type)->publish + wp_count_posts($post_type)->future + wp_count_posts($post_type)->draft + wp_count_posts($post_type)->pending + wp_count_posts($post_type)->private + wp_count_posts($post_type)->archive;
            }
            foreach($count_posts as $count_post){
                $counter = $counter + $count_post;
            }
        }
        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $select_numberposts = 35;
        $throttle_detector = $counter;
        $maxIncrement = ceil($throttle_detector/$select_numberposts);

        // Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
        function postIDCollector($select_post_status, $select_post_type, $offsetValue, $select_numberposts ){
            $all_post_pages = get_posts( array(
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
        $numbers = range( 0 , $maxIncrement);
        foreach( $numbers as $number){
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;	
            $rmc_data_collectors_post_ids_array[] = postIDCollector($select_post_status, $select_post_type, $offsetValue, $select_numberposts );
        }	

        error_log(print_r('postIDCollector DONE' , true));
        // Merge and filter and reindex.
        return array_values(array_filter(array_merge(...$rmc_data_collectors_post_ids_array)));
    }


    // Function that returns all Image IDs
    public function imageIDCollector($select_attachment_type, $select_numberposts, $file_size, $maxIncrement){
        error_log(print_r('imageIDCollector Started' , true));
        // Get all Image IDs.
        function imgIDCollector($select_attachment_type, $offsetValue, $select_numberposts, $file_size){
            $allimagesid = get_posts( array(
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
                foreach ($allimagesid as $imageID){
                    $data = wp_get_attachment_metadata( $imageID ); // get the data structured
                    $data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_false'; 
                    wp_update_attachment_metadata( $imageID, $data );  // save it back to the db

                        // finds the total file / image size
                        $filesize = filesize( get_attached_file( $imageID ) );
                        // converts bits to mega bytes
                        $filesize_convert = $filesize / 1024 / 1024;
                        // converts number to format based on locale
                        $filesize  = number_format_i18n( $filesize_convert, 3 );
                        // creates new meta field with file size of an image
                        update_post_meta( $imageID, '_wp_attachment_image_filesize', $filesize );


                    // $all_image_ids[] = $imageID;
                    // This is responsible for only getting the large images rather then the tiny ones.
                    if( filesize( get_attached_file( $imageID ) ) >= $file_size ){
                        $all_image_ids[] = $imageID;
                    }
                }
                return $all_image_ids;
            }
        }
        // We throttle the number of images so it doesnt kill the server.
        $rmc_data_collectors_ids_array = array(); 
        $numbers = range( 0 , $maxIncrement);
        foreach( $numbers as $number){
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;	
            $rmc_data_collectors_ids_array[$number] = imgIDCollector($select_attachment_type, $offsetValue, $select_numberposts, $file_size );
        }

        error_log(print_r('imageIDCollector DONE' , true));
        return array_values(array_filter(array_merge(...array_filter($rmc_data_collectors_ids_array))));
    }




    // Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
    public function specificImageThumbnailAuditor( $specificPageID, $allimagesid  ){
        error_log(print_r('imageThumbnailAuditor Started' , true));
        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $select_numberposts = 35;
        $throttle_detector_attachement = count($allimagesid);
        $maxIncrement_attachement = ceil($throttle_detector_attachement/$select_numberposts);

        if( !function_exists('specificImageAttachement') ){
            function specificImageAttachement($allimagesid){
                $all_image_attachement_ids = array();
                if($allimagesid){
                    foreach($allimagesid as $image_id){
                        if(wp_get_post_parent_id($image_id)){
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
        $numbers_attachement = range( 0 , $maxIncrement_attachement);
        foreach( $numbers_attachement as $number){
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;	
            $allimagesid_array = array_slice($allimagesid, $offsetValue, $select_numberposts, true);
            $all_image_attachement_ids_array[] = specificImageAttachement($allimagesid_array);
        }
        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter(array_merge(...$all_image_attachement_ids_array))));
        error_log(print_r('imageThumbnailAuditor Checkpoint 1a DONE' , true));

        $all_post_thumbnail_ids = array();
        if( get_post_thumbnail_id( $specificPageID ) ){
            $all_post_thumbnail_ids[] = get_post_thumbnail_id( $specificPageID );
        }

        $arr_checkpoint_1b = cleaner_compare_array_diff($arr_checkpoint_1a, array_values(array_filter($all_post_thumbnail_ids)));
        error_log(print_r('imageThumbnailAuditor Checkpoint 1b DONE' , true));
        return $arr_checkpoint_1b;
    }




    // Lets get all of the pages, posts and custom post types of the entire application. Thumbnail.
    public function imageThumbnailAuditor( $get_all_post_pages, $allimagesid, $select_attachment_type ){
        error_log(print_r('imageThumbnailAuditor Started' , true));
        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $select_numberposts = 35;
        $throttle_detector_attachement = count($allimagesid);
        $maxIncrement_attachement = ceil($throttle_detector_attachement/$select_numberposts);

        function imageAttachement($allimagesid){
            $all_image_attachement_ids = array();
            if($allimagesid){
                foreach($allimagesid as $image_id){
                    if(wp_get_post_parent_id($image_id)){
                        // $all_image_attachement_ids[] = $all_image_attachement_ids[] = $image_id;
                        $all_image_attachement_ids[] = $image_id;

                    }                
                }
            }
            return $all_image_attachement_ids;
        }
        // We throttle the number of images so it doesnt kill the server.
        $all_image_attachement_ids_array = array(); 
        $numbers_attachement = range( 0 , $maxIncrement_attachement);
        foreach( $numbers_attachement as $number){
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;	
            $allimagesid_array = array_slice($allimagesid, $offsetValue, $select_numberposts, true);
            $all_image_attachement_ids_array[] = imageAttachement($allimagesid_array);
        }
        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter(array_merge(...$all_image_attachement_ids_array))));
        error_log(print_r('imageThumbnailAuditor Checkpoint 1a DONE' , true));


        // We get the overall number of posts and divide it by the numberposts and round up that will allow us to page correctly. Then we plus by 1 for odd errors.
        $throttle_detector_thumbnail = count($allimagesid);
        $maxIncrement_thumbnail = ceil($throttle_detector_thumbnail/$select_numberposts);
        function postThumbnail($get_all_post_pages) {
            $all_post_thumbnail_ids = array();
            if ($get_all_post_pages) {
                foreach ($get_all_post_pages as $pageID){
                    if( get_post_thumbnail_id( $pageID ) ){
                        $all_post_thumbnail_ids[] = get_post_thumbnail_id( $pageID );
                    }
                }
            }
            return $all_post_thumbnail_ids;
        }
        // We throttle the number of images so it doesnt kill the server.
        $all_post_thumbnail_ids_array = array(); 
        $numbers_thumbnail = range( 0 , $maxIncrement_thumbnail);
        foreach( $numbers_thumbnail as $number){
            $increment = $number;
            $offsetValue = $increment * $select_numberposts;	
            $get_all_post_pages_array = array_slice($get_all_post_pages, $offsetValue, $select_numberposts, true);
            $all_post_thumbnail_ids_array[] = postThumbnail($get_all_post_pages_array);
        }	

        $arr_checkpoint_1b = cleaner_compare_array_diff($arr_checkpoint_1a, array_values(array_filter(array_merge(...$all_post_thumbnail_ids_array))));
        error_log(print_r('imageThumbnailAuditor Checkpoint 1b DONE' , true));
        return $arr_checkpoint_1b;
    }




    // Check the image id, the og file path, and the image base name.
    public function specificImagePostAuditor( $allimagesid , $specificPageID ){
        error_log(print_r('imagePostAuditor Started' , true));
        // We do a very loose search for the image id in the post_meta value.
        // At the same time we search for the image file path in the post_meta value.
        $wp_postsmeta_id_audit_array = array();
        if($allimagesid){
            foreach($allimagesid as $j => $image_id){
                $f_posts = get_posts( array(
                    'fields' => 'ids',
                    'posts_per_page' => 1,
                    'include' => $specificPageID,
                    'meta_query' => array(
                        array(
                            'value' => '(?:^|\W)'.$image_id.'(?:$|\W)'.'|'.'i:'.$image_id.';'.'|'.get_attached_file($image_id).'|'.basename(get_attached_file($image_id)),
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

                if($f_posts){
                    error_log(print_r( $j , true));
                    foreach($f_posts as $key => $posts){
                        if($posts){



                                    // // Lets search the post meta of all posts...
                                    // $postmetas = get_post_meta( $posts );
                                    // // First we get all the meta values & keys from the current post.
                                    // if($postmetas){
                                    //     foreach($postmetas as $meta_key => $meta_value) {
                                    //         $f_meta_val = $meta_value[0];
                                    //         if(ronik_compare_like($f_meta_val , 'i:'.$image_id.';')){
                                    //             if($meta_key !== 'wp-smpro-smush-data'){
                                    //                 $wp_postsmeta_id_audit_array[] = $image_id;
                                    //             }
                                    //         } else {
                                    //             $wp_postsmeta_id_audit_array[] = $image_id;
                                    //         }
                                    //     }
                                    // }

                                    $wp_postsmeta_id_audit_array[] = $image_id;



                        }
                    }
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_id_audit_array)));
        error_log(print_r('imagePostAuditor Checkpoint 1a DONE' , true));
        return $arr_checkpoint_1a;
    }











    // Check the image id, the og file path, and the image base name.
    public function imagePostAuditor( $allimagesid , $all_post_pages, $select_post_status, $select_post_type ){
        error_log(print_r('imagePostAuditor Started' , true));
        // We do a very loose search for the image id in the post_meta value.
        // At the same time we search for the image file path in the post_meta value.
        $wp_postsmeta_id_audit_array = array();
        if($allimagesid){
            foreach($allimagesid as $j => $image_id){
                $f_posts = get_posts( array(
                    'fields' => 'ids',
                    'post_type' => $select_post_type,
                    'post_status'  => $select_post_status,
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        array(
                            'value' => '(?:^|\W)'.$image_id.'(?:$|\W)'.'|'.'i:'.$image_id.';'.'|'.get_attached_file($image_id).'|'.basename(get_attached_file($image_id)),
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

                if($f_posts){
                    error_log(print_r( $j , true));
                    foreach($f_posts as $key => $posts){
                        if($posts){



                                    // // Lets search the post meta of all posts...
                                    // $postmetas = get_post_meta( $posts );
                                    // // First we get all the meta values & keys from the current post.
                                    // if($postmetas){
                                    //     foreach($postmetas as $meta_key => $meta_value) {
                                    //         $f_meta_val = $meta_value[0];
                                    //         if(ronik_compare_like($f_meta_val , 'i:'.$image_id.';')){
                                    //             if($meta_key !== 'wp-smpro-smush-data'){
                                    //                 $wp_postsmeta_id_audit_array[] = $image_id;
                                    //             }
                                    //         } else {
                                    //             $wp_postsmeta_id_audit_array[] = $image_id;
                                    //         }
                                    //     }
                                    // }


                            $wp_postsmeta_id_audit_array[] = $image_id;
                        }
                    }
                    
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_id_audit_array)));
        error_log(print_r('imagePostAuditor Checkpoint 1a DONE' , true));
        return $arr_checkpoint_1a;
    }




    // Check the post content and do a loose find if the basename is within the post content. This is most ideal for gutenberg blocks.
    public function specificImagePostContentAuditor( $allimagesid , $post_id ){
        error_log(print_r('imagePostContentAuditor Started' , true));
        // This searches the posts content
        // Lets get the post meta of all posts...
        $wp_postsmeta_wp_content_id_audit_array = array();

        if($allimagesid){
            foreach($allimagesid as $k => $image_id){
                //  We do a loose comparison if the meta value has any keyword of en.
                if(ronik_compare_like( get_post_field('post_content', $post_id) , basename(get_attached_file($image_id)))){
                    error_log(print_r( $k , true));
                    $wp_postsmeta_wp_content_id_audit_array[] = $image_id;
                }
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_wp_content_id_audit_array)));
        error_log(print_r('imagePostContentAuditor Checkpoint 1a DONE' , true));
        return $arr_checkpoint_1a;
    }

    








    // Check the post content and do a loose find if the basename is within the post content. This is most ideal for gutenberg blocks.
    public function imagePostContentAuditor( $allimagesid , $all_post_pages ){
        error_log(print_r('imagePostContentAuditor Started' , true));
        // This searches the posts content
        // Lets get the post meta of all posts...
        $wp_postsmeta_wp_content_id_audit_array = array();
        if($all_post_pages){
            foreach($all_post_pages as $i => $post_id){
                if($allimagesid){
                    foreach($allimagesid as $k => $image_id){
                        //  We do a loose comparison if the meta value has any keyword of en.
                        if(ronik_compare_like( get_post_field('post_content', $post_id) , basename(get_attached_file($image_id)))){
                            error_log(print_r( $k , true));
                            $wp_postsmeta_wp_content_id_audit_array[] = $image_id;
                        }
                    }
                }  
        
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_filter($wp_postsmeta_wp_content_id_audit_array)));
        error_log(print_r('imagePostContentAuditor Checkpoint 1a DONE' , true));
        return $arr_checkpoint_1a;
    }














    // Check all the files for the image.
    public function imageFilesystemAudit( $allimagesid  ){
        error_log(print_r('imageFilesystemAudit Started' , true));

        error_log(print_r(get_theme_file_path(), true));
        
        $wp_infiles_array = array();
        if($allimagesid){
            foreach($allimagesid as $image_id){
                $wp_infiles_array[] = $this->rmc_receiveAllFiles_ronikdesigns(get_theme_file_path(), $image_id);
            }
        }

        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_unique(array_filter($wp_infiles_array))));
        error_log(print_r('imageFilesystemAudit Checkpoint 1a DONE' , true));
        return $arr_checkpoint_1a;
    }








    // Check all the files for the image.
    public function imagePreserveAudit( $allimagesid  ){
        error_log(print_r('imagePreserveAudit Started' , true));


        $meta_temp_saved_array = array();
        if($allimagesid){
            foreach($allimagesid as $image_id){
                $meta_datas = wp_get_attachment_metadata( $image_id ); // get the data structured

                if($meta_datas){
                    foreach($meta_datas as $meta_data ){

                        if( $meta_data == 'rbp_media_cleaner_isdetached_temp-saved' ){
                            $meta_temp_saved_array[] = $image_id;
                        }
                    }
                }
            }
        }


        error_log(print_r($meta_temp_saved_array , true));




        $arr_checkpoint_1a = cleaner_compare_array_diff($allimagesid, array_values(array_unique(array_filter($meta_temp_saved_array))));
        error_log(print_r('imagePreserveAudit Checkpoint 1a DONE' , true));
        return $arr_checkpoint_1a;
    }

        




    public function imageMarker( $allimagesid  ) {
        if($allimagesid){
            foreach($allimagesid as $imageid){
                $data = wp_get_attachment_metadata( $imageid ); // get the data structured
				$data['rbp_media_cleaner_isdetached'] = 'rbp_media_cleaner_isdetached_true'; 
				wp_update_attachment_metadata( $imageid, $data );  // save it back to the db
            }
        }
    }





    public function imageCloneSave( $is_array , $imagesid ) {
        $f_file_import = get_option( 'rbp_media_cleaner_file_import' );

        // Lets us set the max_execution_time to 1hr
        error_log(print_r( 'First max_execution_time: ' . ini_get('max_execution_time'), true ));
        @set_time_limit( intval( 3600 ) );
        error_log(print_r( 'Rewrite max_execution_time: ' . ini_get('max_execution_time'), true ));

        error_log(print_r( 'First memory_limit: ' . ini_get('memory_limit'), true ));
        ini_set('memory_limit', '100024M');
        error_log(print_r( 'Rewrite memory_limit: ' . ini_get('memory_limit'), true ));



        if(!$is_array){
            $rbp_media_cleaner_media_data = array($imagesid);
        } else {
            $rbp_media_cleaner_media_data = $imagesid;
        }
        
        error_log(print_r('$rbp_media_cleaner_media_data', true));
        error_log(print_r($rbp_media_cleaner_media_data, true));


        if($f_file_import == 'off' || !isset($f_file_import)){
            if($rbp_media_cleaner_media_data){
                foreach($rbp_media_cleaner_media_data as $rbp_data_id){
                    $clone_path = get_post_meta($rbp_data_id , '_wp_attached_file' ); // Full path
                    $delete_attachment_clone = wp_delete_attachment(  attachment_url_to_postid( $clone_path[0] ) , true);
                    if($delete_attachment_clone){
                        //Delete attachment file from disk
                        unlink( get_attached_file( $clone_path ) );
                        error_log(print_r('Clone File Deleted', true));
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
                        return true;
                    }
                }
            }
            return true;
        }


        if($rbp_media_cleaner_media_data){
            foreach($rbp_media_cleaner_media_data as $rbp_data_id){
                $time_stamp = time();
                // First lets copy the full image to the ronikdetached folder.
                $upload_dir   = wp_upload_dir();
                // We must use the get_attached_file function
                $link = get_attached_file($rbp_data_id);
                $file_path = $link;
                $file_name = basename ( get_attached_file( $rbp_data_id ) );                    
                $file_path_date = str_replace( $upload_dir['baseurl'], '', $link);
                $file_path_date_mod = str_replace($file_name , '', $file_path_date);
                $file_path_date_mod_array = explode('/wp-content/uploads', $file_path_date_mod);
                $file_path_date_mod_array_reindexed = array_values(array_filter($file_path_date_mod_array));
                $file_path_date_mod_array_last = explode('/', $file_path_date_mod_array_reindexed[1]);
                $file_path_date_mod_array_last_reindexed = array_values(array_filter($file_path_date_mod_array_last));
                //Year in YYYY format.
                $year = $file_path_date_mod_array_last_reindexed[0];
                //Month in mm format, with leading zeros.
                $month = $file_path_date_mod_array_last_reindexed[1];
                //The folder path for our file should be YYYY/MM/DD
                $directory = dirname(__FILE__, 2).'/ronikdetached/archive-'.time()."/$year/$month/";
                // If the directory doesn't already exists.
                // if(!is_dir($directory)){
                //     //Create our directory.
                //     mkdir($directory, 0777, true);
                // }
                // if($file_path){
                //     copy($file_path , $directory.$file_name);
                // }


                if(!is_dir(dirname(__FILE__, 2).'/ronikdetached/')){
                    //Create our directory.
                    mkdir(dirname(__FILE__, 2).'/ronikdetached/', 0777, true);
                }


                // // Clean up the temporary files delete all files after roughly 30 days.
                // $dir = new DirectoryIterator(dirname(__FILE__, 2).'/ronikdetached/');
                // foreach ($dir as $i => $fileinfo) {
                //     if (!$fileinfo->isDot()) {
                //         $file_path_date_og = str_replace( 'archive-', '', $fileinfo->getFilename());
                //         $file_path_date_og_mod = str_replace( '.zip', '', $file_path_date_og);
                //         $dateTime = new DateTime();
                //         // $dateTime->modify('-30 day');
                //         $dateTime->modify('-1 minute');
                        
                //         if( $file_path_date_og_mod <= $dateTime->getTimestamp() ){
                //             unlink(  dirname(__FILE__, 2).'/ronikdetached/'.$fileinfo->getFilename() );
                //             // error_log(print_r( 'Past' , true));
                //             // error_log(print_r( dirname(__FILE__, 2).'/ronikdetached/'.$fileinfo->getFilename() , true));
                //         } else {
                //             // error_log(print_r( 'NEW' , true));
                //             // error_log(print_r( $fileinfo->getFilename() , true));
                //         }
                //     }
                // }

                // Erase old files and database
                unlink(  dirname(__FILE__, 2).'/ronikdetached/archive-database.sql' );
                unlink(  dirname(__FILE__, 2).'/ronikdetached/archive-media.zip' );


                $zip = new ZipArchive();
                $filename = dirname(__FILE__, 2)."/ronikdetached/archive-media.zip";
                if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
                    exit("cannot open <$filename>\n");
                }
                // Add a file new.txt file to zip using the text specified
                $zip->addFromString('instructions.txt', "Unzip the folder and copy the media back to the mirror path inside the folder.");
                // $zip->addFile($directory.$file_name, "$year/$month/".$file_name);
                $zip->addFile($file_path, "$year/$month/".$file_name);
                // $zip->addFile($file_path , $upload_dir['basedir']);
                // error_log(print_r($upload_dir['basedir'], true));
                $zip->close();



                $clone_path = get_post_meta($rbp_data_id , '_wp_attached_file' ); // Full path
                $delete_attachment_clone = wp_delete_attachment(  attachment_url_to_postid( $clone_path[0] ) , true);
                if($delete_attachment_clone){
                    //Delete attachment file from disk
                    unlink( get_attached_file( $clone_path ) );
                    error_log(print_r('Clone File Deleted', true));
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

                // if( $rbp_data_id == end($rbp_media_cleaner_media_data) ){
                //     return true;
                // }
            }

            $dbhost = DB_HOST;
            $dbuser = DB_USER;
            $dbpass = DB_PASSWORD;
            $dbname = DB_NAME;


            // https://www.blogdesire.com/create-a-database-backup-and-restore-system-in-php/
            $con = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
            if(isset($_POST['backup'])){    }
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
                    for ($i = 0; $i < $columnCount; $i ++) {
                        while ($row = mysqli_fetch_row($result)) {
                            $sqlScript .= "INSERT INTO $table VALUES(";
                            for ($j = 0; $j < $columnCount; $j ++) {
                                $row[$j] = $row[$j];           
                                if (isset($row[$j])) {
                                    $sqlScript .= '"' . mysqli_real_escape_string($con,$row[$j]) . '"';
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
                if(!empty($sqlScript)) {
                    $backup_file_name =  dirname(__FILE__, 2).'/ronikdetached/archive-database.sql';
                    $fileHandler = fopen($backup_file_name, 'w+');
                    $number_of_lines = fwrite($fileHandler, $sqlScript);
                    fclose($fileHandler);
                    $message = "Backup Created Successfully";
                    error_log(print_r($message, true));
                }




            



   



                
            return true;

        }        
    }
}