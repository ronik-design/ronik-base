<?php 
    // Add Optimizationr page.
    add_submenu_page(
        'options-ronik-base', // parent page slug
        'Optimization',
        'Optimization',
        'manage_options',
        'options-ronik-base_optimization', //
        'ronikbase_optimization_callback',
        5 // menu position
    );
    function ronikbase_optimization_callback(){
        echo '
            <div id="ronik-base_optimization">Optimization</div>
        ';
    }