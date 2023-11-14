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
        echo '<div id="ronik-base_media_cleaner">Media Cleaner</div>';
        $rbp_media_cleaner_counter = get_option('rbp_media_cleaner_counter') ? get_option('rbp_media_cleaner_counter') : "";	
        $rbp_media_cleaner_increment = update_option('rbp_media_cleaner_increment', 1);	
        echo $rbp_media_cleaner_counter; 
        if($rbp_media_cleaner_counter){
            $numbers = range(0, ($rbp_media_cleaner_counter - 1));
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
            <table style="width:100%;display: flex;flex-wrap: wrap;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                <tbody style="width:100%;display: flex;flex-wrap: wrap;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                    <!-- <input class="ronik-user-exporter_increment" value="<?= $rbp_media_cleaner_increment; ?>"> -->
                    <tr style="width:100%;display: flex;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                        <th  style="max-width: 10%;">Thumbnail Image</th>
                        <th  style="max-width: 5%;">File Type</th>
                        <th style="max-width: 5%;">File Size</th>
                        <th style="max-width: 5%;">Image ID</th>
                        <th style="max-width: 50%;">Image Url</th>
                        <th style="max-width: 15%;">Temporarily Preserve Image <br> <sup>Clicking the button will not delete the image it will just exclude the selected image from the media list temporarily.</sup></th>
                    </tr>
                    <?php foreach($numbers as $number){
                        $rbp_media_cleaner_file_size = get_option('rbp_media_cleaner_'.$number.'_file_size') ? get_option('rbp_media_cleaner_'.$number.'_file_size') : "";
                        $rbp_media_cleaner_image_id = get_option('rbp_media_cleaner_'.$number.'_image_id') ? get_option('rbp_media_cleaner_'.$number.'_image_id') : "";
                        $rbp_media_cleaner_image_url = get_option('rbp_media_cleaner_'.$number.'_image_url') ? get_option('rbp_media_cleaner_'.$number.'_image_url') : "";

                        if($rbp_media_cleaner_image_id){ ?>
                            <tr data-media-id="<?= $rbp_media_cleaner_image_id; ?>" style="width:100%;display: flex;width: 100%;position: relative;overflow: hidden;margin: 0 auto;border-spacing: 0;">
                                <td style="max-width: 10%;"><?= wp_get_attachment_image( $rbp_media_cleaner_image_id  );  ?></td>
                                <td style="max-width: 5%;" class="file-type"><?= wp_get_image_mime($rbp_media_cleaner_image_url); ?> </td>
                                <td  style="max-width: 5%;" class="file-size"><?= formatSizeUnits($rbp_media_cleaner_file_size); ?> </td>
                                <td  style="max-width: 5%;"><?= $rbp_media_cleaner_image_id; ?> </td>
                                <td  style="max-width: 50%;"><?= $rbp_media_cleaner_image_url; ?> </td>
                                <td  style="max-width: 15%;"><button style="background-color: #6700ff;" data-media-image-id="<?= $rbp_media_cleaner_image_id; ?>" data-media-row="<?= $number; ?>">Preserve Row</button></td>
                            </tr>
                        <?php }?>
                            
                    <?php } ?>
                </tbody>
            </table>
        <?php }
    }