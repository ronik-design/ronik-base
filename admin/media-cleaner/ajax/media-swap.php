<?php 
    update_post_meta( $_POST['id'], 'mediaSwapFileTimestamp', time() );
    $image_id = $_POST['id'];
    $wp_post_ids_found = array();
    // Default settings...
    $RmcDataGathering = new RmcDataGathering;
    // We retreive the follow post status.
    $select_post_status = array('publish', 'pending', 'draft', 'private', 'future', 'archive');
    // Dynamically retrieve the post types for entire site including custom post types.
    $select_post_type = $RmcDataGathering->postTypesRetrieval();
    $f_posts = get_posts( array(
        'fields' => 'ids',
        'post_type' => $select_post_type,
        'post_status'  => $select_post_status,
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'value' => '(?:^|\W)'.$image_id.'(?:$|\W)'.'|'.'i:'.$image_id.';'.'|'.get_attached_file($image_id).'|'.basename(get_attached_file($image_id)),
                'compare' => 'REGEXP',
            )
        ),
        'orderby' => 'date',
        'order'  => 'DESC',
    ));
    if($f_posts){
        foreach($f_posts as $key => $posts){
            if($posts){
                $wp_post_ids_found[] = $posts;
            }
        }
    }

    $wp_post_ids[] = array();
    $f_posts = get_posts( array(
        'fields' => 'ids',
        'post_type' => $select_post_type,
        'post_status'  => $select_post_status,
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order'  => 'DESC',
    ));
     if($f_posts){
         foreach($f_posts as $key => $posts){
             if($posts){
                 
                 //  We do a loose comparison if the meta value has any keyword of en.
                if(ronik_compare_like( get_post_field('post_content', $posts) , basename(get_attached_file($image_id)))){
                     $wp_post_ids_found[] = $posts;
                }
                if(ronik_compare_like( get_post_field('post_content', $posts) , 'wp-image-'.$image_id )){
                    $wp_post_ids_found[] = $posts;
                }
                if(ronik_compare_like( get_post_field('post_content', $posts) , 'i:'.$image_id.';' )){
                    $wp_post_ids_found[] = $posts;
                }
             }
         }
     }
 




    if($wp_post_ids_found){
        foreach($wp_post_ids_found as $post_ids_found){
            // Lets update the post meta of all posts...
            $postmetas = get_post_meta( $post_ids_found );
            // First we get all the meta values & keys from the current post.
            if($postmetas){
                foreach($postmetas as $meta_key => $meta_value) {
                    $f_meta_val = $meta_value[0];
                    //  We do a loose comparison if the meta value has any keyword of en.
                    error_log(print_r($f_meta_val, true));

                    // global $wpdb;
                    // $table_name = 'wp_postmeta';
                    // $wpdb->query($wpdb->prepare("UPDATE $table_name SET time='$current_timestamp' WHERE userid=$userid"));
                    // WHERE `meta_value` REGEXP '(?:^|\\W)3962(?:$|\\W)'


                    // $results = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_content LIKE '%<!--:en-->%' ORDER BY post_title ASC", ARRAY_A );


                    // if(ronik_compare_like($f_meta_val ,  'i:'.$image_id.';' )){
                    
                    // }

                    $replaced = preg_replace('/"\K[^"]*?feedsportal.*?(?=")/', '', $yourstring);


                    
                    if(ronik_compare_like($f_meta_val ,  'i:'.$image_id.';' )){

                        // $string = explode('[:zh]', (explode('[:en]', $f_meta_val)[1]))[0];
                        // update_post_meta($f_post_id, $meta_key, wp_slash($string));
                    }




                }
            }
        }
    }





    // error_log(print_r('wp_post_ids_found', true));
    // error_log(print_r($wp_post_ids_found, true));



    // error_log(print_r('SWAP', true));
    // error_log(print_r($_POST['id'], true));
    // error_log(print_r($_POST['mediaSwapFileId'], true));



    
?>