<?php 
// Sync Bar
/* Your code to add menu on admin bar */
add_action('admin_bar_menu', 'add_item', 100);
function add_item( $admin_bar ){
    global $pagenow;

    $f_detector = get_option('rbp_media_cleaner_counter');
    $f_sync = get_option('rbp_media_cleaner_sync-time');
    // Default status low
    $f_outdated = 'rmc-sync__outdated-low';
    $f_message = '';
    
    if($f_detector && $f_detector > 0){
        $f_message = $f_sync;
        $f_outdated = 'rmc-sync__outdated-low';
    }
    if (strtotime('-1 day') > strtotime($f_sync)) {
        $f_message = 'Outdated synchronization.';
        $f_outdated = 'rmc-sync__outdated-max';
    } elseif (strtotime('-1 hours') > strtotime($f_sync)){
        $f_message = 'Outdated synchronization.';
        $f_outdated = 'rmc-sync__outdated-medium';
    } else{
        $f_message = $f_sync;
        $f_outdated = 'rmc-sync__outdated-low';
    }

    $menu_id = 'rmc';
    $admin_bar->add_menu(
        array(
            'id' => $menu_id, 
            'title' => __('Ronik Media Cleaner'), 
            'href' => '/wp-admin/admin.php?page=options-ronik-base_media_cleaner', 
            'meta' => array(
                'class' => $f_outdated,
                'target' => '',
            )
        )
    );
    $admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Scan All Media'), 'id' => 'rmc-sync', 'href' => '/', 'meta' => array('target' => '_blank')));
    $admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Last Sync: '. $f_message), 'id' => 'rmc-drafts',  'href' => '', 'meta' => array('target' => '_blank')));
}
/* Here you trigger the ajax handler function using jQuery */
add_action( 'admin_footer', 'mc_sync_action_js' );
function mc_sync_action_js() { ?>
    <script type="text/javascript">
        jQuery("#wp-admin-bar-rmc-sync a").unbind().click(function(e){
            e.preventDefault();
            const f_wpwrap = document.querySelector("#wpwrap");
            const f_wpcontent = document.querySelector("#wpcontent");
            f_wpwrap.classList.add('loader')
            f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');

            const handlePostDataTest = async ( userOptions, mimeType, f_increment ) => {
                const data = new FormData();
                    data.append( 'action', 'rmc_ajax_media_cleaner' );
                    data.append( 'nonce', wpVars.nonce );
                    data.append( 'post_overide',  false );
                    data.append( 'user_option',  userOptions );
                    data.append( 'mime_type',  mimeType );
                    data.append( 'increment',  f_increment );
                    data.append( 'sync',  false );
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
                                
                                setTimeout(function(){
                                    // Lets remove the form
                                    handlePostDataTest('fetch-media', 'all', f_increment);
                                }, 50);
                            }
                            if(data.data['response'] == 'Collector-Sync-done'){
                                alert("Sync is completed! Please do not refresh the page!");
                                setTimeout(function(){
                                    location.reload();
                                }, 500);
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
            handlePostDataTest('fetch-media', 'all', 0)          
        });
    </script>
<?php }