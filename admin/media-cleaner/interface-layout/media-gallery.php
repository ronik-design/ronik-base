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
                        <strong style="color:orange;font-style:italics">Preserved Temporarily: Safe to Remove.</strong>';
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


function media_add_author_dropdown(){
    $scr = get_current_screen();
    if ( $scr->base !== 'upload' ) return;


    $rbp_media_cleaner_filter   = filter_var((isset($_GET['rbp_media_cleaner_filter'])) ? $_GET['rbp_media_cleaner_filter'] : '', FILTER_VALIDATE_URL);
    $selected_1 = $rbp_media_cleaner_filter == 'rbp_media_cleaner_isdetached_true' ? 'selected="selected"' : '';
    $selected_2 = $rbp_media_cleaner_filter == 'rbp_media_cleaner_isdetached_false' ? 'selected="selected"' : '';
    echo '
        <select name="rbp_media_cleaner_filter" id="rbp_media_cleaner_filter" class="">
            <option value="-1">Is Media Safe to Delete?</option>
            <option value="rbp_media_cleaner_isdetached_true" '.$selected_1.'>Safe to Delete</option>
            <option value="rbp_media_cleaner_isdetached_false" '.$selected_2.'>Not Safe to Delete</option>
        </select>
    ';
}
add_action('restrict_manage_posts', 'media_add_author_dropdown');

function author_filter($query) {
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
                    'key' => '_wp_attachment_metadata',
                    'value' => 'rbp_media_cleaner_isdetached_true',
                    'compare' => 'NOT LIKE', // works!
                );
            }
        }
        // Set the meta query to the complete, altered query
        $query->set('meta_query',$meta_query);
    }
}
add_action('pre_get_posts','author_filter');