<?php
/**
 * Extra fields for Pages
 */

namespace Firebelly\Fields\Pages;

add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );
function metaboxes() {
  $prefix = '_cmb2_';

  /**
    * Page intro fields
    */
  $page_intro = new_cmb2_box([
    'id'            => $prefix . 'page_intro',
    'title'         => esc_html__( 'Page Intro', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $page_intro->add_field([
    'name' => esc_html__( 'Intro Title', 'cmb2' ),
    'id'   => $prefix .'intro_title',
    'type' => 'text',
  ]);
  $page_intro->add_field([
    'name' => esc_html__( 'Intro Quote', 'cmb2' ),
    'id'   => $prefix .'intro_quote',
    'type' => 'textarea_small',
  ]);

  /**
    * Homepage fields
    */
  $homepage_fields = new_cmb2_box([
    'id'            => 'secondary_content',
    'title'         => __( 'Custom Featured Block', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_on'       => ['key' => 'page-template', 'value' => 'front-page.php'],
    'priority'      => 'high',
    'show_names'    => true,
  ]);
  $homepage_fields->add_field([
    'name'    => esc_html__( 'Custom Featured Image', 'cmb2' ),
    'id'      => $prefix . 'custom_featured_image',
    'type'    => 'file',
    'options' => [
      'url'   => false, // Hide the text input for the url
    ],
  ]);
  $homepage_fields->add_field([
    'name' => esc_html__( 'Custom Featured Title', 'cmb2' ),
    'id'   => $prefix . 'custom_featured_title',
    'type' => 'text',
  ]);
  $homepage_fields->add_field([
    'name' => esc_html__( 'Custom Featured Body', 'cmb2' ),
    'id'   => $prefix . 'custom_featured_body',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 8,
    ],
  ]);
  $homepage_fields->add_field([
    'name' => esc_html__( 'Custom Featured Link', 'cmb2' ),
    'id'   => $prefix . 'custom_featured_link',
    'type' => 'text_url',
    'desc' => 'e.g. http://foo.com/',
  ]);

  /**
    * Donate page fields
    */
  $donate_multiple_fields = new_cmb2_box([
    'id'            => 'donation_multiple_fields',
    'title'         => __( 'Monthly Donation Options', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_on'       => ['key' => 'page-template', 'value' => 'page-donate.php'],
    'priority'      => 'core',
    'show_names'    => true,
  ]);
  $group_field_id = $donate_multiple_fields->add_field( array(
		'id'              => 'donation_multiple_options',
		'type'            => 'group',
		'show_on'         => ['key' => 'page-template', 'value' => 'page-donate.php'],
		'options'         => array(
      'group_title'   => __( 'Option {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Option', 'cmb2' ),
      'remove_button' => __( 'Remove Option', 'cmb2' ),
      'sortable'      => true,
    ),
  ) );
  $donate_multiple_fields->add_group_field( $group_field_id, array(
    'name' => 'Amount',
    'id'   => 'amount',
    'type' => 'text',
    'row_classes' => '-half',
  ) );
  $donate_multiple_fields->add_group_field( $group_field_id, array(
    'name' => 'Description',
    'id'   => 'description',
    'type' => 'text',
  'row_classes' => '-half',
  ) );

  $donate_gift_fields = new_cmb2_box([
    'id'            => 'donation_gift_fields',
    'title'         => __( 'Multiple Donation Gift Options', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_on'       => ['key' => 'page-template', 'value' => 'page-donate.php'],
    'priority'      => 'core',
    'show_names'    => true,
  ]);
  $group_field_id = $donate_gift_fields->add_field( array(
		'id'              => 'donation_gift_options',
		'type'            => 'group',
		'show_on'         => ['key' => 'page-template', 'value' => 'page-donate.php'],
		'options'         => array(
      'group_title'   => __( 'Option {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
      'add_button'    => __( 'Add Another Option', 'cmb2' ),
      'remove_button' => __( 'Remove Option', 'cmb2' ),
      'sortable'      => true,
    ),
  ) );
  $donate_gift_fields->add_group_field( $group_field_id, array(
    'name' => 'Description',
    'id'   => 'description',
    'type' => 'text',
  ) );

  $donate_single_fields = new_cmb2_box([
    'id'            => 'donation_single_fields',
    'title'         => __( 'Single Donation Options', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_on'       => ['key' => 'page-template', 'value' => 'page-donate.php'],
    'priority'      => 'core',
    'show_names'    => true,
  ]);
  $group_field_id = $donate_single_fields->add_field( array(
		'id'              => 'donation_single_options',
		'type'            => 'group',
		'show_on'         => ['key' => 'page-template', 'value' => 'page-donate.php'],
		'options'         => array(
      'group_title'   => __( 'Option {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
      'add_button'    => __( 'Add Another Option', 'cmb2' ),
      'remove_button' => __( 'Remove Option', 'cmb2' ),
      'sortable'      => true,
    ),
  ) );
  $donate_single_fields->add_group_field( $group_field_id, array(
		'name'        => 'Amount',
		'id'          => 'amount',
		'type'        => 'text',
		'row_classes' => '-half',
  ) );
  $donate_single_fields->add_group_field( $group_field_id, array(
		'name'            => 'Description',
		'id'              => 'description',
		'sanitization_cb' => __NAMESPACE__.'\sanitize_text_callback',
		'type'            => 'text',
		'row_classes'     => '-half',
  ) );
}

function sanitize_text_callback( $value, $field_args, $field ) {
  $value = strip_tags( $value, '<b><strong><i><em>' );
  return $value;
}
