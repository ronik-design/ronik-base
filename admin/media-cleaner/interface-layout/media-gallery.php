<?php

function rbp_delete_attachment( $post_id ) {			
    error_log(print_r( $post_id, true));

    // get attached file dir
    $file = get_attached_file( $post_id );

    // get file name of attachment to be deleted
    $file_name = wp_basename( $file );
}
add_action( 'delete_attachment', 'rbp_delete_attachment' );





// Image Dimensions.
function rmc_media_column_dimensions( $cols ) {
    $cols["rmc_filesize"] = "File Size";
    return $cols;
}
// Image Isdetached.
function rmc_media_column_isdetached( $cols ) {
    $cols["rmc_detached"] = "Ronik Media Cleaner Detached";

    $cols["rmc_location"] = "Media Location";

    return $cols;
}
add_filter( 'manage_media_columns', 'rmc_media_column_isdetached' );
add_filter( 'manage_media_columns', 'rmc_media_column_dimensions' );

function rmc_media_column_value( $column_name, $id ) {
    $data = wp_get_attachment_metadata( $id ); // get the data structured
    if( $column_name == 'rmc_detached' ){
        $f_detector = get_option('rbp_media_cleaner_counter');
        $f_sync = get_option('rbp_media_cleaner_sync-time');

        

        if (strtotime('-1 day') > strtotime($f_sync)) {
            echo 'Please re-synchronization. <br>Due to outdated synchronization. <br><a href="/wp-admin/admin.php?page=options-ronik-base_media_cleaner">Re-sync</a>';
            echo '<br /><br />';
        }
    ?>

    <?php if($f_detector && $f_detector > 0){
        
            if( isset($data['rbp_media_cleaner_isdetached'])  ){
                if($data['rbp_media_cleaner_isdetached'] == 'rbp_media_cleaner_isdetached_true'){
                    echo 'Media Detection last ran: <br>'.$f_sync.'<br><br>
                    <strong style="color:green;font-style:italics">Safe to Remove.</strong>';
                } else {
                    if($data['rbp_media_cleaner_isdetached'] == 'rbp_media_cleaner_isdetached_temp-saved'){
                        echo 'Media Detection last ran: <br>'.$f_sync.'<br><br>
                        <strong style="color:orange;font-style:italics">Preserved Temporarily: <br>Safe to Remove.</strong>';
                    } else {
                        echo 'Media Detection last ran: <br>'.$f_sync.'<br><br>
                        <strong style="color:red;font-style:italics">Not Safe to Remove.</strong>';
                    }
                }
            } else {
                echo 'Media Detection last ran: <br>'.$f_sync.'<br><br>
                <strong style="color:red;font-style:italics">Not Safe to Remove.</strong>';
            }						
        } else {
            echo 'Please visit the media cleaner to start synchronization. <a href="/wp-admin/admin.php?page=options-ronik-base_media_cleaner">TEST</a>';
        }
    }
    if( $column_name == 'rmc_filesize' ){
        echo formatSizeUnits($data['filesize']);
    }
}
add_action( 'manage_media_custom_column', 'rmc_media_column_value', 10, 2 );


function mediacleaner_add_dropdown(){
    $scr = get_current_screen();
    if ( $scr->base !== 'upload' ) return;

    $rbp_media_cleaner_filter  = isset($_GET['rbp_media_cleaner_filter']) ? $_GET['rbp_media_cleaner_filter'] : '';
    $isdetached_selected_1 = $rbp_media_cleaner_filter == 'rbp_media_cleaner_isdetached_true' ? 'selected="selected"' : '';
    $isdetached_selected_2 = $rbp_media_cleaner_filter == 'rbp_media_cleaner_isdetached_false' ? 'selected="selected"' : '';
    $isdetached_selected_3 = $rbp_media_cleaner_filter == 'rbp_media_cleaner_isdetached_preserved' ? 'selected="selected"' : '';

    $rbp_media_cleaner_size_filter  = isset($_GET['rbp_media_cleaner_size_filter']) ? $_GET['rbp_media_cleaner_size_filter'] : '';
    $isdetached_selected_1 = $rbp_media_cleaner_size_filter == 'rbp_media_cleaner_size_large' ? 'selected="selected"' : '';
    $isdetached_selected_2 = $rbp_media_cleaner_size_filter == 'rbp_media_cleaner_size_small' ? 'selected="selected"' : '';

    echo '
        <select name="rbp_media_cleaner_filter" id="rbp_media_cleaner_filter" class="">
            <option value="-1">Is Media Safe to Delete?</option>
            <option value="rbp_media_cleaner_isdetached_true" '.$isdetached_selected_1.'>Safe to Delete</option>
            <option value="rbp_media_cleaner_isdetached_false" '.$isdetached_selected_2.'>Not Safe to Delete</option>
            <option value="rbp_media_cleaner_isdetached_preserved" '.$isdetached_selected_3.'>Preserved Images</option>
        </select>

        <select name="rbp_media_cleaner_size_filter" id="rbp_media_cleaner_size_filter" class="">
            <option value="-1">File Size</option>
            <option value="rbp_media_cleaner_size_large" '.$isdetached_selected_1.'>File Size Large</option>
            <option value="rbp_media_cleaner_size_small" '.$isdetached_selected_2.'>File Size Small</option>
        </select>
    ';
}
add_action('restrict_manage_posts', 'mediacleaner_add_dropdown');


function mediacleaner_filter($query) {
    if ( is_admin() && $query->is_main_query() ) {
        if (!isset($_GET['rbp_media_cleaner_filter'])){
            $query->set('rbp_media_cleaner_filter', '');
        }
        $meta_query[] = array();
        // Reset filter.
        if ( isset($_GET['rbp_media_cleaner_filter']) ) {
            if($_GET['rbp_media_cleaner_filter'] == -1){
                $query->set('rbp_media_cleaner_filter', '');
            }
        }
        // Reset filter.
        if ( isset($_GET['rbp_media_cleaner_filter']) ) {
            if ($_GET['rbp_media_cleaner_filter'] == 'rbp_media_cleaner_isdetached_true') {
                $mishaValue = $_GET['rbp_media_cleaner_filter'];
                $meta_query[] = array(
                    // 'key'     => 'rbp_media_cleaner_isdetached',
                    'value'   => $mishaValue,
                    'compare' => 'LIKE'
                );
            }
        }
        if ( isset($_GET['rbp_media_cleaner_filter']) ) {
            if ($_GET['rbp_media_cleaner_filter'] == 'rbp_media_cleaner_isdetached_false') {
                $meta_query[] = array(
                    'relation'  => 'AND',
                    array (
                        'key'     => '_wp_attachment_metadata',
                        'value'   => 'rbp_media_cleaner_isdetached_temp-saved',
                        'compare' => 'NOT LIKE'
                    ),
                    array (
                        'key'     => '_wp_attachment_metadata',
                        'value'   => 'rbp_media_cleaner_isdetached_true',
                        'compare' => 'NOT LIKE'
                    ),
                );
            }
        }
        if ( isset($_GET['rbp_media_cleaner_filter']) ) {
            if ($_GET['rbp_media_cleaner_filter'] == 'rbp_media_cleaner_isdetached_preserved') {
                $meta_query[] = array(
                    'key' => '_wp_attachment_metadata',
                    'value' => 'rbp_media_cleaner_isdetached_temp-saved',
                    'compare' => 'LIKE', // works!
                );
            }
        }





        if (!isset($_GET['rbp_media_cleaner_size_filter'])){
            $query->set('rbp_media_cleaner_size_filter', '');
        }
        $meta_query[] = array();
        // Reset filter.
        if ( isset($_GET['rbp_media_cleaner_size_filter']) ) {
            if($_GET['rbp_media_cleaner_size_filter'] == -1){
                $query->set('rbp_media_cleaner_size_filter', '');
            }
        }
        // Reset filter.
        if ( isset($_GET['rbp_media_cleaner_size_filter']) ) {
            if ($_GET['rbp_media_cleaner_size_filter'] == 'rbp_media_cleaner_size_large') {
                $mishaValue = $_GET['rbp_media_cleaner_size_filter'];
                // $meta_query[] = array(
                //     'key'     => 'filesize',
                //     // 'value'   => $mishaValue,
                //     // 'compare' => 'LIKE'
                // );

                // $query->set('order', 'asc');
                // $query->set('orderby', '_wp_attachment_image_filesize');



                // $meta_query[] = array(
                //     'relation' => 'OR',
                //     array(
                //         'key' => '_wp_attachment_image_filesize',
                //         'compare' => 'NOT EXISTS',
                //     ),
                //     array(
                //         'key' => '_wp_attachment_image_filesize',
                //     ),
                // );

                $orderby = $query->get( 'orderby' );

                if ( 'filesize' == $orderby ) {
            
                    $meta_query = array(
                        'relation' => 'OR',
                        array(
                            'key' => '_wp_attachment_image_filesize',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key' => '_wp_attachment_image_filesize',
                        ),
                    );
            
                    $query->set( 'meta_query', $meta_query );
                    $query->set( 'orderby', 'meta_value' );
                }

            }
        }











        // Set the meta query to the complete, altered query
        $query->set('meta_query',$meta_query);
    }
}
add_action('pre_get_posts','mediacleaner_filter');


/* ------------------------------ MEDIA METADATA ------------------------------ */
// edits image meta data
add_action( 'add_attachment', 'my_edit_image_meta_data' );
function my_edit_image_meta_data( $post_ID ) {
    // checks if the uploaded file is an image
    if ( wp_attachment_is_image( $post_ID ) ) {
        // finds the total file / image size
        $filesize = filesize( get_attached_file( $post_ID ) );
        // converts bits to mega bytes
        $filesize_convert = $filesize / 1024 / 1024;
        // converts number to format based on locale
        $filesize  = number_format_i18n( $filesize_convert, 3 );
        // creates new meta field with file size of an image
        update_post_meta( $post_ID, '_wp_attachment_image_filesize', $filesize );
    }
}