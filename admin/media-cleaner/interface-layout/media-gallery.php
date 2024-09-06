<?php

function rbp_delete_attachment( $post_id ) {			
    error_log(print_r( $post_id, true));
    // get attached file dir
    $file = get_attached_file( $post_id );  
    // get file name of attachment to be deleted
    $file_name = wp_basename( $file );
}
add_action( 'delete_attachment', 'rbp_delete_attachment' );

// Image Isdetached.
function rmc_media_column_isdetached( $cols ) {
    $cols["rmc_detached"] = "Ronik Media Cleaner Detached";
    $cols["rmc_swap"] = "Media Swap";
    return $cols;
}
add_filter( 'manage_media_columns', 'rmc_media_column_isdetached' );
// add_filter( 'manage_media_columns', 'rmc_media_column_dimensions' );

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
            echo formatSizeUnits($data['filesize']) . ' <br>';
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

    if( $column_name == 'rmc_swap' ) { ?>
        <script type="text/javascript">
            jQuery("#media-swap").unbind().click(function(e){
                e.preventDefault();
                const f_wpwrap = document.querySelector("#wpwrap");
                const f_wpcontent = document.querySelector("#wpcontent");
                f_wpwrap.classList.add('loader')
                f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');

                const handlePostDataTest = async ( mediaSwapFileId, $id ) => {
                    const data = new FormData();
                        data.append( 'action', 'rmc_ajax_media_swap' );
                        data.append( 'nonce', wpVars.nonce );
                        data.append( 'mediaSwapFileId',  mediaSwapFileId );
                        data.append( 'id',  $id );

                    fetch(wpVars.ajaxURL, {
                        method: "POST",
                        credentials: 'same-origin',
                        body: data
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data) {
                            console.log(data);
                            if((data.data['pageCounter'] == '0') && (data.data['pageTotalCounter'] == 1)){
                                alert('Synchronization is complete! Page will auto reload.');
                                location.reload();
                            } else {
                                console.log(data.data['response']);

                                if(data.data['response'] == 'Reload'){
                                    setTimeout(function(){
                                        alert('Synchronization is complete! Page will auto reload.');
                                        location.reload();
                                    }, 50);
                                }
                                if(data.data['response'] == 'Done'){
                                    f_increment = f_increment + 1;
                                    
                                    // setTimeout(function(){
                                    //     // Lets remove the form
                                    //     handlePostDataTest('fetch-media', 'all', f_increment);
                                    // }, 50);
                                }
                            }
                        }
                    })
                    .catch((error) => {
                        console.log('[WP Pageviews Plugin]');
                        console.error(error);
                    });
                }
                let counter = 0;
                console.log('Ajax request sent.');

                handlePostDataTest( jQuery('#media-swap__file-id').attr('value'),  jQuery(this).closest('#the-list').find('.author-self.status-inherit').find('.check-column input').val() )                                            
            });
        </script>

        <?php 
            $f_mediaSwapFileTimestamp = get_post_meta($id , 'mediaSwapFileTimestamp', true );
            echo 'Media ID: ' . $id . '<br>';
            echo 'Media Swap Status: <br>';
            if($f_mediaSwapFileTimestamp){
                echo 'Last Swapped: <br>' . date("F j, Y, g:i a", $f_mediaSwapFileTimestamp);
            } else {
                echo 'Never Swapped File';
            }

        
            echo '
                <table class="compat-attachment-fields">
                    <tbody>
                    <tr class="acf-field acf-field-image acf-field-swap" data-name="test" data-type="image" data-key="field_swap">
                        <td class="acf-input">
                            <div class="acf-image-uploader" data-preview_size="medium" data-library="all" data-mime_types="" data-uploader="wp">
                                <input type="hidden" name="acf[field_swap]" id="media-swap__file-id" id="media-swap__id" value="">	
                                <div class="show-if-value image-wrap" style="max-width: 300px">
                                <img src="" alt="" data-name="image" style="max-height: 300px;">
                                <div class="acf-actions -hover">
                                    <a class="acf-icon -pencil dark" data-name="edit" href="#" title="Edit"></a>
                                    <a class="acf-icon -cancel dark" data-name="remove" href="#" title="Remove"></a>
                                </div>
                                </div>
                                <div class="hide-if-value">
                                <p>No image selected <a data-name="add" class="acf-button button" href="#">Add Image</a></p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="compat-field-acf-blank"><td></td></tr>
                    </tbody>
                </table>
                <button id="media-swap">Swap Media</button>
            ';
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
        // // Reset filter.
        // if ( isset($_GET['rbp_media_cleaner_size_filter']) ) {
        //     $mishaValue = $_GET['rbp_media_cleaner_size_filter'];

        //     $meta_query = array(
        //         'relation' => 'OR',
        //         array(
        //             'key' => '_wp_attachment_image_filesize',
        //             'compare' => 'NOT EXISTS',
        //         ),
        //         array(
        //             'key' => '_wp_attachment_image_filesize',
        //         ),
        //     );
    
        //     if ($_GET['rbp_media_cleaner_size_filter'] == 'rbp_media_cleaner_size_large') {
        //         $query->set( 'meta_query', $meta_query );
        //         $query->set('order', 'dsc');
        //         $query->set( 'orderby', 'meta_value' );

        //         // $query->set('orderby', '_wp_attachment_image_filesize');

        //     } else if ($_GET['rbp_media_cleaner_size_filter'] == 'rbp_media_cleaner_size_small'){

        //         $query->set( 'meta_query', $meta_query );
        //         $query->set('order', 'asc');
        //         $query->set( 'orderby', 'meta_value' );
        //     }
        //     // $query->set('order', 'asc');
        // }



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