<?php 
function ronikdesignsbase_optimization_data( $data ) {

}
register_rest_route( 'optimization/v1', '/optimizationcollector/(?P<slug>\w+)', array(
    'methods' => 'GET',
    'callback' => 'ronikdesignsbase_optimization_data',
    'permission_callback' => '__return_true', // Allows public access
));
