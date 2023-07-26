<?php
if (function_exists('acf_add_local_field_group')) :

	acf_add_local_field_group(array(
		'key' => 'group_1_rbp',
		'title' => 'Ronik Media Cleaner Dashboard',
		'fields' => array(

			array(
				'key' => 'field_1a_rbp',
				'label' => 'Mime Type',
				'name' => 'page_media_cleaner_post_mime_type_field_ronikdesign',
				'aria-label' => '',
				'type' => 'checkbox',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'jpg' => 'JPG',
					'gif' => 'Gif',
					'png' => 'PNG',
					'pdf' => 'PDF',
					'video' => 'Video',
					'misc' => 'Misc',
				),
				'default_value' => array(
					0 => 'jpg',
				),
				'return_format' => 'both',
				'multi_min' => 1,
				'multi_max' => '',
				'allow_custom' => 0,
				'layout' => 'vertical',
				'toggle' => 0,
				'save_custom' => 0,
			),

			array(
				'key' => 'field_1b_rbp',
				'label' => 'Post Type',
				'name' => 'page_media_cleaner_post_type_field_ronikdesign',
				'aria-label' => '',
				'type' => 'select',
				'instructions' => 'Please select all the post types that contain any of the media. This will increase the accuracy of the unused images. The less post types selected will increase a chance of a failure.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'choices' => array(),
				'default_value' => false,
				'return_format' => 'value',
				'multiple' => 1,
				'dyn_post_loader' => 1,
				'allow_null' => 0,
				'ui' => 1,
				'ajax' => 0,
				'placeholder' => '',
			),

			array(
				'key' => 'field_1c_rbp',
				'label' => 'Number Posts Field',
				'name' => 'page_media_cleaner_numberposts_field_ronikdesign',
				'type' => 'number',
				'instructions' => 'This will pull the overall image number per sequence. ** A Higher Value will increase the chance of an unsuccessful response.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'default_value' => 5,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 0,
				'max' => 150,
				'step' => '5',
			),
			array(
				'key' => 'field_1d_rbp',
				'label' => 'Offset Page Field',
				'name' => 'page_media_cleaner_offset_field_ronikdesign',
				'type' => 'number',
				'instructions' => 'This will offset the page field amount. Default ratio is 0 start / 5 end. If no images are found please increment to the next number.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'default_value' => 5,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 0,
				'max' => 30000,
				'step' => '5',
			),
			array(
				'key' => 'field_1e_rbp',
				'label' => 'Media Cleaner Field',
				'name' => 'page_media_cleaner_field',
				'type' => 'repeater',
				'instructions' => 'Media Cleaner will go through all unattached JPG, PNG, and GIF files. </br>
				Based on media size this may take a while. Please click the "Init Unused Media Migration" then review the selected images for deletion. </br>
				Then click "Init Deletion of Unused Media". Please backup site before clicking the button! </br>
				Keep in mind that if any pages or post are in the trash. The images that are attached to those pages will be deleted. </br>
				Also please keep in mind that the older the website the higher possibility of a huge number of images being detached.
				',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => '',
				'min' => 0,
				'max' => 0,
				'layout' => 'block',
				'button_label' => '',
				'sub_fields' => array(
					array(
						'key' => 'field_1ea_rbp',
						'label' => 'File Size',
						'name' => 'file_size',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '25',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'array',
						'append' => 'MB',
					),
					array(
						'key' => 'field_1eb_rbp',
						'label' => 'Image ID',
						'name' => 'image_id',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '15',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'array',
						'prepend' => '',
						'append' => '',
					),
					array(
						'key' => 'field_1ec_rbp',
						'label' => 'Image URL',
						'name' => 'image_url',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '30',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'array',
					),
					array(
						'key' => 'field_1ed_rbp',
						'label' => 'Thumbnail Preview',
						'name' => 'thumbnail_preview',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '25',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
				),
			),
			array(
				'key' => 'field_1f_rbp',
				'label' => '',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => 'page_media_cleaner_field',
				),
				'message' => '',
				'new_lines' => 'wpautop',
				'esc_html' => 0,
			),

		),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'ronik-base-settings',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

endif;