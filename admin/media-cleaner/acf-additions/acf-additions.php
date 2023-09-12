<?php

// Dynamic Post Loader to select.
function ronikdesigns_acf_post_load($field)
{
    // render settings for other field types (hide/show settings based on whether multi-select is enabled)
    acf_render_field_setting($field, array(
        'label'    => 'Dynamic Post Loader to select.',
        'name' => 'dyn_post_loader',
        'type' => 'true_false',
        'ui' => 1,
    ));
}
add_action('acf/render_field_settings/type=select', 'ronikdesigns_acf_post_load' , 10); // add Dynamic Post Loader to select

// Dynamic Post Loader to select.
function ronikdesigns_acf_post_loader($field)
{
    if( !array_key_exists('dyn_post_loader', $field)  ){
        // return the field
        return $field;
    }
    if ($field['dyn_post_loader']) {
        $args = array(
            'public' => true,
            '_builtin' => false
        );
        $post_types = get_post_types($args);
        $post_types['page'] = 'page';
        $post_types['post'] = 'post';
        // array_push($post_types, 'page');
        // array_push($post_types, 'post');

        $choices = array($post_types);
        $field['choices'] = array();
        if ($choices) {
            foreach ($choices as $choice) {
                $field['choices'] = $choice;
            }
        }
    }
    // return the field
    return $field;
}
add_filter('acf/load_field/type=select', 'ronikdesigns_acf_post_loader', 11);