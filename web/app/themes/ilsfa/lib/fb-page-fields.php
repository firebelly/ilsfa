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
    'object_types'  => ['page','program','post'],
    'context'       => 'top',
    'priority'      => 'high',
  ]);
  $page_intro->add_field([
    'name' => esc_html__( 'Intro Title', 'cmb2' ),
    'id'   => $prefix .'intro_title',
    'type' => 'textarea',
    'attributes' => [
      'rows' => '2'
    ],
    'desc' => 'If left blank, uses post title',
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
   * Page/Program footer outro
   */
  $page_footer_outro = new_cmb2_box([
    'id'            => $prefix . 'page_footer_outro',
    'title'         => __( 'Footer Outro', 'cmb2' ),
    'object_types'  => ['page','program'],
    'context'       => 'normal',
    'priority'      => 'default',
    'show_names'    => false,
  ]);
  $page_footer_outro->add_field([
    'id'   => $prefix .'footer_outro',
    'type' => 'wysiwyg',
    'desc' => 'To add FormAssembly form, use [formassembly id=8675309 iframe=1]',
    'options' => [
      'textarea_rows' => 10,
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
    'id'              => $prefix . 'overview_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Overview Block {#}', 'cmb2' ),
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
    'id'              => $prefix . 'highlight_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Highlight Block {#}', 'cmb2' ),
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
      'vendors'     => __( 'Vendors Icon', 'cmb2' ),
      'equitable'   => __( 'Equitable Icon', 'cmb2' ),
      'savings'     => __( 'Savings Icon', 'cmb2' ),
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
  $homepage_action_blocks->add_field([
    'name' => esc_html__( 'Headline', 'cmb2' ),
    'id'   => $prefix .'action_blocks_headline',
    'type' => 'text',
  ]);
  $homepage_action_blocks->add_field([
    'name' => esc_html__( 'Image Background', 'cmb2' ),
    'id'   => $prefix .'action_blocks_background',
    'type' => 'file',
  ]);
  $group_field_id = $homepage_action_blocks->add_field([
    'id'              => $prefix . 'action_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Action Block {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Block', 'cmb2' ),
      'remove_button' => __( 'Remove Block', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Subhead',
    'id'   => 'subhead',
    'type' => 'text',
    'attributes' => [
      'placeholder' => 'Realize Savings',
    ],
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Header',
    'id'   => 'header',
    'type' => 'text',
    'attributes' => [
      'placeholder' => 'Discover solar power\'s benefits',
    ],
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Link',
    'id'   => 'url',
    'type' => 'text',
    'attributes' => [
      'placeholder' => 'https://foo.com/ or /foo/',
    ],
  ]);
  $homepage_action_blocks->add_group_field( $group_field_id, [
    'name' => 'Disabled Text',
    'id'   => 'disabled_text',
    'type' => 'text',
    'desc' => 'If set, will disable block and show this text in the button, e.g. "Coming April"',
  ]);


  /**
    * For IL Residents fields
    */
  $residents_fields = new_cmb2_box([
    'id'            => $prefix . 'residents_fields',
    'title'         => __( 'Elibility Blocks', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_slugs'    => array('for-il-residents'),
    'show_on_cb'    => '\Firebelly\CMB2\show_for_slugs',
    'priority'      => 'high',
    'show_names'    => true,
  ]);
  $group_field_id = $residents_fields->add_field([
    'id'              => $prefix . 'eligibility_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Elibility Block {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Block', 'cmb2' ),
      'remove_button' => __( 'Remove Block', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $residents_fields->add_group_field( $group_field_id, [
    'name' => 'Headline',
    'id'   => 'headline',
    'type' => 'text',
  ]);
  $residents_fields->add_group_field( $group_field_id, [
    'name' => 'Body',
    'id'   => 'body',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 6,
    ],
  ]);
  // Programs
  $residents_programs = new_cmb2_box([
    'id'            => $prefix . 'residents_programs',
    'title'         => __( 'Programs Block', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_slugs'    => array('for-il-residents'),
    'show_on_cb'    => '\Firebelly\CMB2\show_for_slugs',
    'priority'      => 'high',
    'show_names'    => true,
  ]);
  $residents_programs->add_field([
    'name' => esc_html__( 'Image Background', 'cmb2' ),
    'id'   => $prefix .'programs_background',
    'type' => 'file',
  ]);


  /**
  * For Vendors fields
  */
  $vendor_fields = new_cmb2_box([
    'id'            => $prefix . 'vendor_fields',
    'title'         => __( 'Vendor Requirements', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'normal',
    'show_slugs'    => array('for-vendors'),
    'show_on_cb'    => '\Firebelly\CMB2\show_for_slugs',
    'priority'      => 'high',
    'show_names'    => true,
  ]);
  $vendor_fields->add_field([
    'name' => esc_html__( 'Image Background', 'cmb2' ),
    'id'   => $prefix .'vendor_requirements_background',
    'type' => 'file',
  ]);
  $group_field_id = $vendor_fields->add_field([
    'id'              => $prefix . 'vendor_requirements_blocks',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Vendor Requirement {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Block', 'cmb2' ),
      'remove_button' => __( 'Remove Block', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $vendor_fields->add_group_field( $group_field_id, [
    'name' => 'Headline',
    'id'   => 'headline',
    'type' => 'text',
  ]);
  $vendor_fields->add_group_field( $group_field_id, [
    'name' => 'Body',
    'id'   => 'body',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 6,
    ],
  ]);

}

function sanitize_text_callback( $value, $field_args, $field ) {
  $value = strip_tags( $value, '<b><strong><i><em>' );
  return $value;
}

// Remove page body support
// add_action('init', __NAMESPACE__ . '\\init_remove_support',100);
// function init_remove_support() {
//    remove_post_type_support( 'page', 'editor');
// }

// Create 'top' section for metaboxes and move that to the top of admin edit page (above body)
add_action('edit_form_after_title', function() {
  global $post, $wp_meta_boxes;
  do_meta_boxes(get_current_screen(), 'top', $post);
  unset($wp_meta_boxes[get_post_type($post)]['top']);
});
