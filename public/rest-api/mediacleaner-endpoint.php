<?php 


function ronikdesignsbase_mediacleaner_data( $data ) {
    // Get all the data IDS
    $rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');
    if(!$rbp_media_cleaner_media_data){
        $rbp_media_cleaner_media_data = array();
    }
    // Creates an encoded svg for src, lazy loading.
    function km_svgplaceholder($imgacf=null) {
        $iacf = $imgacf;
        if($iacf){
            if($iacf['width']){
                $width = $iacf['width'];
            }
            if($iacf['height']){
                $height = $iacf['height'];
            }
            $viewbox = "width='{$width}' height='{$height}' viewBox='0 0 {$width} {$height}'";
        } else{
            $viewbox = "viewBox='0 0 100 100'";
        }
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' {$viewbox}%3E%3C/svg%3E";
    }
    
    function localValidator(){
        // Check for local string in host.
        $is_local =  str_contains( $_SERVER['HTTP_HOST'] , 'local');
        // If false we check to see if the REMOTE_ADDR is the default local host ip address..
        if(!$is_local){
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                $is_local = true;
            } else {
                $is_local = false;
            }
        }
        return $is_local;
    }


    function id_reformatter_media_data($mediacollector_data){
        $f_reformatted_collector = array();
        $upload_dir = wp_upload_dir();
        $is_local = localValidator();

        if($mediacollector_data){
            foreach($mediacollector_data as $i => $image_id){
                $f_reformatted_collector[$i]['id'] = $image_id;
                $f_img_attached = (wp_get_attachment_image_src($image_id) ? wp_get_attachment_image_src($image_id) : 100);


                $f_image_path = get_attached_file($image_id);
                error_log(print_r(  '$f_image_url' , true));
                error_log(print_r(  $f_image_path , true));






                if(file_exists($f_image_path)){
                    $f_image_url = wp_get_attachment_image_url($image_id , 'small' );
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
                        'src' => km_svgplaceholder(),
                        'data-width' => (isset($f_img_attached[1]) && $f_img_attached[1] ? $f_img_attached[1] : '50' ),
                        'data-height '=> (isset($f_img_attached[2]) && $f_img_attached[2] ? $f_img_attached[2] : '50' ),
                        'data-type' => (!$is_local && isset($f_img_attached[0]) && $f_img_attached[0] ? wp_get_image_mime( $f_img_attached[0] ) : '')
                    )  
                );
                // $f_img_mod = preg_replace(array('/width="[^"]*"/', '/height="[^"]*"/'), '', $f_img);

                if(!$f_img){
                    $f_img = '<img src="data:image/svg+xml,%3Csvg xmlns=&#039;http://www.w3.org/2000/svg&#039; viewBox=&#039;0 0 100 100&#039;%3E%3C/svg%3E" class=" lzy_img reveal-disabled"  decoding="async" data-src="https://copley.divof/wp-content/plugins/ronik-base/admin/media-cleaner/image/not_found.jpg" data-id="'.$image_id.'" data-class="image-target" data-width="1" data-height ="1" data-type="" />';
                }
                


                $f_img_mod2 = preg_replace(array('/srcset="[^"]*"/'), '', $f_img);
                $f_img_mod3 = preg_replace(array('/sizes="[^"]*"/'), '', $f_img_mod2);
                $f_img_mod4 = preg_replace(array('/alt="[^"]*"/'), '', $f_img_mod3);
                $f_reformatted_collector[$i]['img-thumb'] = $f_img_mod4;

                error_log(print_r('$f_reformatted_collector', true));

                error_log(print_r($image_id, true));

                error_log(print_r($f_reformatted_collector, true));



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



    if($data['slug'] == 'large' || $data['slug'] == 'small'){
        if($rbp_media_cleaner_media_data){
            $f_filter_collector = array();
            foreach ( $rbp_media_cleaner_media_data as $i => $image_id ){    
                $upload_dir = wp_upload_dir();
                $attachment_metadata = wp_get_attachment_metadata( $image_id);
                if( isset($attachment_metadata['filesize']) && $attachment_metadata['filesize']){
                    $media_size = ($attachment_metadata['filesize']);
                } else {
                    if( isset( $upload_dir['basedir']) && isset($attachment_metadata['file']) ){
                        if(file_exists( $upload_dir['basedir'].'/'.$attachment_metadata['file'] )){
                            if(filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] )){
                                $media_size = (filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] ) );
                            } else {
                                $media_size = 0;
                            }
                        } else {
                            $media_size = 0;
                        }
                    } else {
                        $media_size = 0;
                    }
                }
                $f_filter_collector[$image_id] = intval($media_size);
            }
            arsort($f_filter_collector, SORT_NATURAL);
            $f_filter_collector_high = array();
            foreach ($f_filter_collector as $key => $val) {
                $f_filter_collector_high[$key] = $val;
            }
        }
    }
    // mediacollector large to small
    if($data['slug'] == 'large'){         
        if(isset($f_filter_collector_high) && $f_filter_collector_high){            
            $rbp_media_cleaner_media_data = array_keys($f_filter_collector_high);
            return id_reformatter_media_data($rbp_media_cleaner_media_data);
        }
    }
    // mediacollector small to large
    if($data['slug'] == 'small'){
        if(isset($f_filter_collector_high) && $f_filter_collector_high){
            $f_filter_collector_low = array_reverse(array_keys($f_filter_collector_high));
            $rbp_media_cleaner_media_data = $f_filter_collector_low;
            return id_reformatter_media_data($rbp_media_cleaner_media_data);
        }
    }
    // mediacollector
    if($data['slug'] == 'all'){
        $rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');
        if(!$rbp_media_cleaner_media_data){
            $rbp_media_cleaner_media_data = array();
        }
        return id_reformatter_media_data($rbp_media_cleaner_media_data);
    }

    // Temp Saved mediacollector
    if($data['slug'] == 'tempsaved'){
        $args = array( 
            'post_mime_type' => 'image',
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

register_rest_route( 'mediacleaner/v1', '/mediacollector/(?P<slug>\w+)', array(
    'methods' => 'GET',
    'callback' => 'ronikdesignsbase_mediacleaner_data',
));
