<?php

/**
 * Adds a custom setting to the ACF 'select' field type to enable dynamic post loader.
 *
 * @param array $field The field settings array.
 */
function ronikdesigns_acf_post_load($field) {
    // Add a custom setting for the 'select' field type to enable dynamic post loader
    acf_render_field_setting($field, array(
        'label'    => 'Dynamic Post Loader',
        'name'     => 'dyn_post_loader',
        'type'     => 'true_false',
        'ui'       => 1,
    ));
}

// Hook to ACF to add custom settings to 'select' field type
add_action('acf/render_field_settings/type=select', 'ronikdesigns_acf_post_load', 10);

/**
 * Filters the choices for ACF 'select' fields to dynamically load post types.
 *
 * @param array $field The field settings array.
 * @return array The updated field settings array with dynamic choices.
 */
function ronikdesigns_acf_post_loader($field) {
    // Check if the 'dyn_post_loader' setting is enabled
    if (isset($field['dyn_post_loader']) && $field['dyn_post_loader']) {
        // Get public post types excluding built-in types
        $args = array(
            'public'   => true,
            '_builtin' => false,
        );
        $post_types = get_post_types($args);

        // Add 'page' and 'post' to the list of choices
        $post_types['page'] = 'Page';
        $post_types['post'] = 'Post';

        // Set the choices for the field
        $field['choices'] = $post_types;
    }

    // Return the modified field settings
    return $field;
}

// Hook to ACF to modify the choices of 'select' fields based on dynamic post loader setting
add_filter('acf/load_field/type=select', 'ronikdesigns_acf_post_loader', 11);
