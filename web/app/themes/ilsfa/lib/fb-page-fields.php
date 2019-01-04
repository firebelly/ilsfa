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
    'name' => esc_html__( 'Supporting Statement', 'cmb2' ),
    'id'   => $prefix .'intro_supporting_statement',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 8,
    ],
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

  /**
    * For IL Residents fields
    */
  // $residents_fields = new_cmb2_box([
  //   'id'            => 'secondary_content',
  //   'title'         => __( 'For Residents', 'cmb2' ),
  //   'object_types'  => ['page'],
  //   'context'       => 'normal',
  //   'show_slugs'    => array('for-il-residents'),
  //   'show_on_cb'    => '\Firebelly\CMB2\show_for_slugs',
  //   'priority'      => 'high',
  //   'show_names'    => true,
  // ]);
  // $residents_fields->add_field([
  //   'name' => esc_html__( 'Supporting Statement', 'cmb2' ),
  //   'id'   => $prefix .'intro_supporting_statement',
  //   'type' => 'wysiwyg',
  //   'options' => [
  //     'textarea_rows' => 8,
  //   ],
  // ]);

  /**
    * For Vendors fields
    */
  // $residents_fields = new_cmb2_box([
  //   'id'            => 'secondary_content',
  //   'title'         => __( 'For Vendors', 'cmb2' ),
  //   'object_types'  => ['page'],
  //   'context'       => 'normal',
  //   'show_slugs'    => array('for-vendors'),
  //   'show_on_cb'    => '\Firebelly\CMB2\show_for_slugs',
  //   'priority'      => 'high',
  //   'show_names'    => true,
  // ]);
  // $residents_fields->add_field([
  //   'name' => esc_html__( 'Supporting Statement', 'cmb2' ),
  //   'id'   => $prefix .'intro_supporting_statement',
  //   'type' => 'wysiwyg',
  //   'options' => [
  //     'textarea_rows' => 8,
  //   ],
  // ]);

}

function sanitize_text_callback( $value, $field_args, $field ) {
  $value = strip_tags( $value, '<b><strong><i><em>' );
  return $value;
}
