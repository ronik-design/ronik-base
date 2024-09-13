<?php 
function ronikdesignsbase_mediacleaner_data( $data ) {
    // Update the memory option.
    $helper = new RonikBaseHelper;
    $helper->ronikdesigns_increase_memory();
    
    // Get all the data IDS
    $rbp_media_cleaner_media_data = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
    if(!$rbp_media_cleaner_media_data){
        $rbp_media_cleaner_media_data = array();
    }


    function id_reformatter_media_data($mediacollector_data){
        $helper = new RonikBaseHelper;

        $f_reformatted_collector = array();
        $upload_dir = wp_upload_dir();
        $is_local = $helper->localValidator();

        if($mediacollector_data){
            foreach($mediacollector_data as $i => $image_id){
                $f_reformatted_collector[$i]['id'] = $image_id;
                $f_img_attached = (wp_get_attachment_image_src($image_id) ? wp_get_attachment_image_src($image_id) : 100);
                $f_image_path = get_attached_file($image_id);
                if(file_exists($f_image_path)){
                    $f_image_url = wp_get_attachment_image_url($image_id , 'thumbnail' );
                } else {
                    $f_image_url = str_replace("public/rest-api/", "", plugin_dir_url( __FILE__ )).'admin/media-cleaner/image/not_found.jpg';
                }
                // This is mostly for compiling the image tag.
                $f_img = wp_get_attachment_image( 
                    $image_id, 
                    'small', 
                    false, 
                    array(
                        'data-src' => $f_image_url, 
                        'data-id' => $image_id, 
                        'data-class' => 'image-target',
                        'class' => ' lzy_img reveal-disabled',
                        'src' => $helper->ronikdesignsbase_svgplaceholder(),
                        'data-width' => (isset($f_img_attached[1]) && $f_img_attached[1] ? $f_img_attached[1] : '50' ),
                        'data-height '=> (isset($f_img_attached[2]) && $f_img_attached[2] ? $f_img_attached[2] : '50' ),
                        'data-type' => (!$is_local && isset($f_img_attached[0]) && $f_img_attached[0] ? wp_get_image_mime( $f_img_attached[0] ) : '')
                    )  
                );
                // $f_img_mod = preg_replace(array('/width="[^"]*"/', '/height="[^"]*"/'), '', $f_img);
                if(!$f_img){
                    $f_img = '<img src="data:image/svg+xml,%3Csvg xmlns=&#039;http://www.w3.org/2000/svg&#039; viewBox=&#039;0 0 100 100&#039;%3E%3C/svg%3E" class=" lzy_img reveal-disabled"  decoding="async" data-src="/wp-content/plugins/ronik-base/admin/media-cleaner/image/not_found.jpg" data-id="'.$image_id.'" data-class="image-target" data-width="1" data-height ="1" data-type="" />';
                }
                $f_img_mod2 = preg_replace(array('/srcset="[^"]*"/'), '', $f_img);
                $f_img_mod3 = preg_replace(array('/sizes="[^"]*"/'), '', $f_img_mod2);
                $f_img_mod4 = preg_replace(array('/alt="[^"]*"/'), '', $f_img_mod3);
                $f_reformatted_collector[$i]['img-thumb'] = $f_img_mod4;

                $attachment_metadata = wp_get_attachment_metadata( $image_id);
                if( isset($attachment_metadata['filesize']) && $attachment_metadata['filesize']){
                    $media_size = formatSizeUnits($attachment_metadata['filesize']);
                } else {
                    if( isset($attachment_metadata['file']) && $attachment_metadata['file']){
                        
                        if(filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] )){
                            $media_size = formatSizeUnits(filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] ) );
                        } else {
                            // $media_size = "File Size Not found!";
                            $filePath = get_attached_file($image_id);
                            if( $filePath ){
                                $media_size = formatSizeUnits(filesize( $filePath ) );
                            } else {
                                $media_size = "File Size Not found!";
                            }
                        }
                    } else {
                        $filePath = get_attached_file($image_id);
                        if( $filePath ){
                            $media_size = formatSizeUnits(filesize( $filePath ) );
                        } else {
                            $media_size = "File Size Not found!";
                        }
                    }
                }
                $f_reformatted_collector[$i]['media_size'] = $media_size;
                if( isset($attachment_metadata['file']) && $attachment_metadata['file']){
                    $media_file = $upload_dir['basedir'].'/'.$attachment_metadata['file'];
                    $media_file = strstr($upload_dir['basedir'].'/'.$attachment_metadata['file'], '/uploads/');
                } else {
                    // $media_file_type = "Not found";
                    $filePath = get_attached_file($image_id);
                    if( $filePath ){
                        $media_file = $filePath;
                    } else {
                        $media_file = "Not found";
                    }
                }
                $f_reformatted_collector[$i]['media_file'] = $media_file;
                if( isset($attachment_metadata['file']) && $attachment_metadata['file']){
                    $media_file_type = wp_get_image_mime($upload_dir['basedir'].'/'.$attachment_metadata['file']);
                    if( !$media_file_type ){
                        // $media_file_type = "Not found";
                        $fileType = wp_check_filetype(get_attached_file($image_id));
                        if( $fileType ){
                            $media_file_type = $fileType['ext'];
                        } else {
                            $media_file_type = "Not found";
                        }
                    }

                } else {
                    $fileType = wp_check_filetype(get_attached_file($image_id));
                    if( $fileType ){
                        $media_file_type = $fileType['ext'];
                    } else {
                        $media_file_type = "Not found";
                    }
                }
                
                $f_reformatted_collector[$i]['media_file_type'] = $media_file_type;
            }
        }
        return $f_reformatted_collector;
    }

    function mediaDataSizeFormatter($rbp_media_cleaner_media_data, $slug){
        $f_filter_collector = array();
        foreach ( $rbp_media_cleaner_media_data as $i => $image_id ){    
            $upload_dir = wp_upload_dir();
            $attachment_metadata = wp_get_attachment_metadata( $image_id );
            if( isset($attachment_metadata['filesize']) && $attachment_metadata['filesize']){
                $media_size = ($attachment_metadata['filesize']);
            } else {
                if( isset( $upload_dir['basedir']) && isset($attachment_metadata['file']) ){
                    if(file_exists( $upload_dir['basedir'].'/'.$attachment_metadata['file'] )){
                        if(filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] )){
                            $media_size = (filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] ) );
                        } else {
                            $media_size = (filesize(get_attached_file($image_id)) );
                        }
                    } else {
                        $media_size = (filesize(get_attached_file($image_id)) );
                    }
                } else {
                    $media_size = (filesize(get_attached_file($image_id)) );
                }
            }
            $f_filter_collector[$image_id] = intval($media_size);
            clearstatcache();
        }
        arsort($f_filter_collector, SORT_NATURAL);
        $f_filter_collector_high = array();
        foreach ($f_filter_collector as $key => $val) {
            $f_filter_collector_high[$key] = $val;
        }
        // return $f_filter_collector_high;

        // mediacollector large to small
        if($slug == 'large'){         
            if(isset($f_filter_collector_high) && $f_filter_collector_high){            
                $rbp_media_cleaner_media_data = array_keys($f_filter_collector_high);
                return id_reformatter_media_data($rbp_media_cleaner_media_data);
            }
        }
        // mediacollector small to large
        if($slug == 'small'){
            if(isset($f_filter_collector_high) && $f_filter_collector_high){
                $f_filter_collector_low = array_reverse(array_keys($f_filter_collector_high));
                $rbp_media_cleaner_media_data = $f_filter_collector_low;
                return id_reformatter_media_data($rbp_media_cleaner_media_data);
            }
        }
        return id_reformatter_media_data($rbp_media_cleaner_media_data);
    }

    $filters = $data->get_param( 'filter' );

    if((str_contains($filters, 'all') || empty($filters)) && $data['slug'] == 'large' || $data['slug'] == 'small'){    
        if( !str_contains($filters, 'gif') && !str_contains($filters, 'jpg') && !str_contains($filters, 'png') && !str_contains($filters, 'video')  && !str_contains($filters, 'misc') ){
            if($data['slug'] == 'large' || $data['slug'] == 'small'){
                if($rbp_media_cleaner_media_data){
                    return mediaDataSizeFormatter($rbp_media_cleaner_media_data, $data['slug']);
                }
            }
        }
    } 
    
    // mediacollector
    if($filters){
        $rmc_media_cleaner_media_data_collectors_image_id_array = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
        
        if(!is_array($rmc_media_cleaner_media_data_collectors_image_id_array)){
            return 'no-images';
        }

        $filters_array = explode("?" , $filters);
        if($filters_array){
            foreach($filters_array as $filter_array){
                $select_attachment_type = cleaner_post_mime_type($filter_array);
                foreach ($rmc_media_cleaner_media_data_collectors_image_id_array as $image_id_array){			
                    if(array_search( get_post_mime_type($image_id_array) , $select_attachment_type)){
                        $rbp_media_cleaner_media_data_collector_refomat_specific_id[] = $image_id_array;
                    }
                }
            }
            if(isset($rbp_media_cleaner_media_data_collector_refomat_specific_id)){
                if( (!str_contains($filters, 'all') || !empty($filters)) ){   
                    return mediaDataSizeFormatter( $rbp_media_cleaner_media_data_collector_refomat_specific_id, $data['slug'] );
                }
                return id_reformatter_media_data($rbp_media_cleaner_media_data_collector_refomat_specific_id);
            } else{
                return 'no-images';
            }
        }
        return 'no-images';
    }

    if($data['slug'] == 'all' && str_contains($filters, 'all') ){
        $rbp_media_cleaner_media_data = get_transient( 'rmc_media_cleaner_media_data_collectors_image_id_array_finalized' );
        // $rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');
        if(!$rbp_media_cleaner_media_data){
            $rbp_media_cleaner_media_data = array();
        }
        return id_reformatter_media_data($rbp_media_cleaner_media_data);
    }

    // Temp Saved mediacollector
    if($data['slug'] == 'tempsaved'){
        $select_attachment_type['svg'] = 'image/svg+xml';
        $select_attachment_type['jpg'] = "image/jpg";
        $select_attachment_type['jpeg'] = "image/jpeg";
        $select_attachment_type['jpe'] = "image/jpe";
        $select_attachment_type['gif'] = "image/gif";
        $select_attachment_type['png'] = "image/png";
        $select_attachment_type['pdf'] = "application/pdf";
        $select_attachment_type['asf|asx'] = "video/x-ms-asf";
        $select_attachment_type['wmv'] = "video/x-ms-wmv";
        $select_attachment_type['wmx'] = "video/x-ms-wmx";
        $select_attachment_type['wm'] = "video/x-ms-wm";
        $select_attachment_type['avi'] = "video/avi";
        $select_attachment_type['divx'] = "video/divx";
        $select_attachment_type['flv'] = "video/x-flv";
        $select_attachment_type['mov|qt'] = "video/quicktime";
        $select_attachment_type['mpeg|mpg|mpe'] = "video/mpeg";
        $select_attachment_type['mp4|m4v'] = "video/mp4";
        $select_attachment_type['ogv'] = "video/ogg";
        $select_attachment_type['webm'] = "video/webm";
        $select_attachment_type['mkv'] = "video/x-matroska";
        $select_attachment_type['js'] = "application/javascript";
        $select_attachment_type['pdf'] = "application/pdf";
        $select_attachment_type['tar'] = "application/x-tar";
        $select_attachment_type['zip'] = "application/zip";
        $select_attachment_type['gz|gzip'] = "application/x-gzip";
        $select_attachment_type['rar'] = "application/rar";
        $select_attachment_type['txt|asc|c|cc|h|srt'] = "text/plain";
        $select_attachment_type['csv'] = "text/csv";
        $select_attachment_type['webp'] = "image/webp";
        $args = array( 
            'post_mime_type' => $select_attachment_type,
            'numberposts'    => -1,
            'post_parent'    => get_the_ID(),
            'post_type'      => 'attachment',
            'fields'        => 'ids',
    
            'meta_query' => array(
                array(
                    'key'     => '_wp_attachment_metadata',
                    'value'   => 'rbp_media_cleaner_isdetached_temp-saved',
                    'compare' => 'LIKE'
                )
            )
        );
        return id_reformatter_media_data(get_posts( $args ));
    }
}

function my_custom_permission_check() {
    return current_user_can('manage_options'); // Adjust capability as needed
}

register_rest_route( 'mediacleaner/v1', '/mediacollector/(?P<slug>\w+)', array(
    'methods' => 'GET',
    'callback' => 'ronikdesignsbase_mediacleaner_data',
    // 'permission_callback' => 'my_custom_permission_check', // Restrict access based on custom logic
));
