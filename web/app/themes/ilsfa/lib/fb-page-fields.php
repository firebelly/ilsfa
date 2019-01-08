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
   * Page footer outro
   */
  $page_footer_outro = new_cmb2_box([
    'id'            => $prefix . 'page_footer_outro',
    'title'         => __( 'Footer Outro', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'priority'      => 'default',
    'show_names'    => true,
  ]);
  $page_footer_outro->add_field([
    'name' => esc_html__( 'Intro Title', 'cmb2' ),
    'id'   => $prefix .'footer_outro',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 6,
    ],
  ]);

  /**
    * Homepage fields
    */
  $homepage_overview_blocks = new_cmb2_box([
    'id'            => $prefix . 'homepage_overview_blocks',
    'title'         => esc_html__( 'Overview Blocks', 'cmb2' ),
    'object_types'  => ['page'],
    'show_on'       => ['key' => 'page-template', 'value' => 'front-page.php'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $group_field_id = $homepage_overview_blocks->add_field([
    'id'              => 'overview_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Block {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Block', 'cmb2' ),
      'remove_button' => __( 'Remove Block', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $homepage_overview_blocks->add_group_field( $group_field_id, [
    'name' => 'Body',
    'id'   => 'body',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 6,
    ],
  ]);
  $homepage_overview_blocks->add_group_field( $group_field_id, [
    'name' => 'Image',
    'id'   => 'image',
    'type' => 'file',
  ]);

  $homepage_highlight_blocks = new_cmb2_box([
    'id'            => $prefix . 'homepage_highlight_blocks',
    'title'         => esc_html__( 'Highlight Blocks', 'cmb2' ),
    'object_types'  => ['page'],
    'show_on'       => ['key' => 'page-template', 'value' => 'front-page.php'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $group_field_id = $homepage_highlight_blocks->add_field([
    'id'              => 'highlight_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Block {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Block', 'cmb2' ),
      'remove_button' => __( 'Remove Block', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $homepage_highlight_blocks->add_group_field( $group_field_id, [
    'name'    => 'Icon',
    'id'      => 'icon',
    'type'    => 'select',
    'options' => array(
      'standard' => __( 'Vendors Icon', 'cmb2' ),
      'custom'   => __( 'Equitable Icon', 'cmb2' ),
      'none'     => __( 'Savings Icon', 'cmb2' ),
    ),  ]);
  $homepage_highlight_blocks->add_group_field( $group_field_id, [
    'name' => 'Body',
    'id'   => 'body',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 6,
    ],
  ]);

  $homepage_action_blocks = new_cmb2_box([
    'id'            => $prefix . 'homepage_action_blocks',
    'title'         => esc_html__( 'Action Blocks', 'cmb2' ),
    'object_types'  => ['page'],
    'show_on'       => ['key' => 'page-template', 'value' => 'front-page.php'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $group_field_id = $homepage_action_blocks->add_field([
    'id'              => 'action_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Block {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Block', 'cmb2' ),
      'remove_button' => __( 'Remove Block', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Subhead',
    'id'   => 'subhead',
    'type' => 'text',
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Header',
    'id'   => 'header',
    'type' => 'text',
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Link',
    'id'   => 'url',
    'type' => 'text',
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

// Remove page body support
add_action('init', __NAMESPACE__ . '\\init_remove_support',100);
function init_remove_support() {
   remove_post_type_support( 'page', 'editor');
}
