<?php
// Add Media Cleaner page.
add_submenu_page(
    'options-ronik-base', // parent page slug
    'Media Cleaner',
    'Media Cleaner',
    'manage_options',
    'options-ronik-base_media_cleaner', //
    'ronikbase_media_cleaner_callback',
    4 // menu position
);

function ronikbase_media_cleaner_callback(){
    ?>
        <style>
            table tr, table td, table th{
                width: 100%;
                margin-right: 5px;
                margin-bottom: 5px;
            }
            table tr td{
                text-overflow: ellipsis;
                overflow: hidden;
                white-space: nowrap;
            }
            table td img{
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        </style>
        <div id="ronik-base_media_cleaner">Media Cleaner</div>
    <?php
    $rbp_media_cleaner_media_data = get_option('rbp_media_cleaner_media_data');

    if($rbp_media_cleaner_media_data){
        $rbp_media_cleaner_media_data_filter = isset($_GET['filter_size']) ? $_GET['filter_size'] : 0;

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


        error_log(print_r(array_keys($f_filter_collector_high), true));

        $f_filter_collector_low = array_reverse(array_keys($f_filter_collector_high));

        if($rbp_media_cleaner_media_data_filter){
            if( $rbp_media_cleaner_media_data_filter == 'high' ){
                $rbp_media_cleaner_media_data = array_keys($f_filter_collector_high);
            } else {
                $rbp_media_cleaner_media_data = $f_filter_collector_low;
            }
        }


        $rbp_media_cleaner_media_data_page = isset($_GET['page_number']) ? $_GET['page_number'] : 0;
        $rbp_media_cleaner_media_data_count = count($rbp_media_cleaner_media_data);
        $page_counter = 20;
        $page_counter_offset = $page_counter*$rbp_media_cleaner_media_data_page;
        $output = array_slice($rbp_media_cleaner_media_data, $page_counter_offset, $page_counter);
        $f_slug = '/wp-admin/admin.php?page=options-ronik-base_media_cleaner';
    }

    if($rbp_media_cleaner_media_data){ ?>
        <?php if($rbp_media_cleaner_media_data_page){ ?>
            <a href="<?= $f_slug.'&page_number='.($rbp_media_cleaner_media_data_page-1).'&filter_size='.$rbp_media_cleaner_media_data_filter; ?>">Previous</a>
        <?php } ?>
        <?php if( $rbp_media_cleaner_media_data_page+1 <= floor($rbp_media_cleaner_media_data_count/$page_counter)){ ?>
            <a href="<?php echo $f_slug.'&page_number='.($rbp_media_cleaner_media_data_page+1).'&filter_size='.$rbp_media_cleaner_media_data_filter; ?>">Next</a>
        <?php } ?>


        <a href="<?= $f_slug.'&filter_size=high'.'&page_number='.$rbp_media_cleaner_media_data_page; ?>">Filter High</a>
        <a href="<?= $f_slug.'&filter_size=low'.'&page_number='.$rbp_media_cleaner_media_data_page; ?>">Filter Low</a>

        <table style="width:100%;display: flex;flex-wrap: wrap;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
            <tbody style="width:100%;display: flex;flex-wrap: wrap;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                <tr style="width:100%;display: flex;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                    <th style="max-width: 5%;">Trash</th>

                    <th style="max-width: 15%;">Thumbnail Image</th>
                    <th style="max-width: 5%;">File Type</th>
                    <th style="max-width: 5%;">File Size</th>
                    <th style="max-width: 5%;">Image ID</th>
                    <th style="max-width: 5%;">Image Edit</th>
                    <th style="max-width: 40%;">Image Url</th>
                    <th style="max-width: 20%;">Temporarily Preserve Image <br> <sup>Clicking the button will not delete the image it will just exclude the selected image from the media list temporarily.</sup></th>
                </tr>
                <?php
                echo count($rbp_media_cleaner_media_data);
                    foreach ( $output as $image_id ){       
                        $upload_dir = wp_upload_dir();
                        $attachment_metadata = wp_get_attachment_metadata( $image_id);

                        if( isset($attachment_metadata['filesize']) && $attachment_metadata['filesize']){
                            $media_size = formatSizeUnits($attachment_metadata['filesize']);
                        } else {
                            if(filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] )){
                                $media_size = formatSizeUnits(filesize( $upload_dir['basedir'].'/'.$attachment_metadata['file'] ) );
                            } else {
                                $media_size = "File Size Not found!";
                            }
                        }

                        if( isset($attachment_metadata['file']) && $attachment_metadata['file']){
                            $media_file = $upload_dir['basedir'].'/'.$attachment_metadata['file'];
                        } else {
                            $media_file = "Not found";
                        }

                        if( isset($attachment_metadata['file']) && $attachment_metadata['file']){
                            $media_file_type = wp_get_image_mime($upload_dir['basedir'].'/'.$attachment_metadata['file']);
                        } else {
                            $media_file_type = "Not found";
                        }
                    ?>
                        <tr data-media-id="<?= $image_id; ?>" style="width:100%;display: flex;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                            <td style="max-width: 5%;">Trash</td>

                            <td style="max-width: 15%;"><?= wp_get_attachment_image(  $image_id , 'small', false, array('data-id'=>$image_id, 'data-class'=>'image-target')  );  ?></td>
                            <td style="max-width: 5%;" class="file-type"><?= $media_file_type; ?> </td>
                            <td  style="max-width: 5%;" class="file-size"><?= $media_size; ?> </td>
                            <td  style="max-width: 5%;"><?=  $image_id; ?> </td>
                            <td  style="max-width: 5%;"> <a target="_blank" style="color:#6700ff;" href="/wp-admin/post.php?post=<?=  $image_id; ?>&action=edit">Edit</a> </td>
                            <td  style="max-width: 40%;"><?= $media_file; ?> </td>
                            <td  style="max-width: 20%;"><button style="background-color: #6700ff;" data-preserve-media="<?= $image_id; ?>">Preserve Row</button></td>
                        </tr>
                        
                    <?php }
                ?>
            </tbody>
        </table>
    <?php 
    }
        
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
    // $attached_images = get_children( $args ); 
    $attached_images = get_posts( $args ); 

    if( $attached_images){ ?>
        <br>
        <h1>Preserved Images</h1>
        <table style="width:100%;display: flex;flex-wrap: wrap;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
            <tbody style="width:100%;display: flex;flex-wrap: wrap;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                <tr style="width:100%;display: flex;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                    <th style="max-width: 20%;">Thumbnail Image</th>
                    <th style="max-width: 5%;">File Type</th>
                    <th style="max-width: 5%;">File Size</th>
                    <th style="max-width: 5%;">Image ID</th>
                    <th style="max-width: 5%;">Image Edit</th>
                    <th style="max-width: 40%;">Image Url</th>
                    <th style="max-width: 20%;">Temporarily Preserve Image <br> <sup>Clicking the button will not delete the image it will just exclude the selected image from the media list temporarily.</sup></th>
                </tr>
                <?php foreach ( $attached_images as $image ){        
                    $upload_dir = wp_upload_dir();
                    $attachment_metadata = wp_get_attachment_metadata( $image);
                    if($attachment_metadata['rbp_media_cleaner_isdetached'] == 'rbp_media_cleaner_isdetached_temp-saved'){ ?>
                        <tr data-media-id="<?= $image; ?>" style="width:100%;display: flex;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                            <td style="max-width: 20%;"><?= wp_get_attachment_image(  $image  , 'small'   );  ?></td>
                            <td style="max-width: 5%;" class="file-type"><?= wp_get_image_mime($upload_dir['basedir'].'/'.$attachment_metadata['file']); ?> </td>
                            <td  style="max-width: 5%;" class="file-size"><?= formatSizeUnits($attachment_metadata['filesize']); ?> </td>
                            <td  style="max-width: 5%;"><?=  $image; ?> </td>
                            <td  style="max-width: 5%;"> <a target="_blank" style="color:#6700ff;" href="/wp-admin/post.php?post=<?=  $image_id; ?>&action=edit">Edit</a> </td>
                            <td  style="max-width: 40%;"><?= $upload_dir['basedir'].'/'.$attachment_metadata['file']; ?> </td>
                            <td  style="max-width: 20%;"><button style="background-color: #6700ff;" data-unpreserve-media="<?= $image; ?>">Un Preserve Row</button></td>
                        </tr>
                    <?php }
                } ?>              
            </tbody>
        </table>
    <?php }
}