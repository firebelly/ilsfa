<?php
/**
 * Program post type
 */

namespace Firebelly\PostTypes\Program;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$programs = new PostType('program', [
  'supports'   => ['title', 'editor', 'thumbnail'],
  'taxonomies' => ['program_type'],
  'rewrite'    => ['with_front' => false],
]);

// Custom taxonomies
$program_type = new Taxonomy('program_type');
$program_type->register();

$programs->register();

/**
 * CMB2 custom fields
 */
function metaboxes() {
  $prefix = '_cmb2_';

  // Basic Info
  $program_info = new_cmb2_box([
    'id'            => $prefix . 'program_info',
    'title'         => __( 'Program Info', 'cmb2' ),
    'object_types'  => ['program'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $program_info->add_field([
    'name'      => 'Stat Figure',
    'id'        => $prefix . 'stat_figure',
    'type'      => 'text',
    'desc'      => 'e.g. 99%',
  ]);
  $program_info->add_field([
    'name'      => 'Stat Label',
    'id'        => $prefix . 'stat',
    'type'      => 'text',
    // 'desc'      => '',
  ]);
  $program_info->add_field([
    'name'      => 'Income Requirements',
    'id'        => $prefix . 'income_requirements',
    'type'      => 'text',
    'desc'      => 'e.g. 2 or more units must earn $31,667 or less per year',
  ]);
  $program_info->add_field([
    'name'      => 'Household Size',
    'id'        => $prefix . 'household_size',
    'type'      => 'text',
    'desc'      => 'e.g. 2–4 units',
  ]);
  $program_info->add_field([
    'name'      => 'Installation Cost',
    'id'        => $prefix . 'installation_cost',
    'type'      => 'text',
    'desc'      => 'e.g. No upfront installation costs',
  ]);
  $program_info->add_field([
    'name'      => 'Savings',
    'id'        => $prefix . 'savings',
    'type'      => 'text',
    'desc'      => 'e.g. 50% or greater savings on monthly energy bills',
  ]);

  // Application block
  $application_prompt = new_cmb2_box([
    'id'            => $prefix . 'application_prompt',
    'title'         => __( 'Application Prompt', 'cmb2' ),
    'object_types'  => ['program'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $application_prompt->add_field([
    'name'      => 'Body',
    'id'        => $prefix . 'application_body',
    'type'      => 'wysiwyg',
    'options'   => [
      'textarea_rows' => 10,
    ],
  ]);
  $application_prompt->add_field([
    'name'      => 'Prompt Label',
    'id'        => $prefix . 'application_prompt',
    'type'      => 'text',
    'desc'      => 'e.g. Apply',
  ]);
  $application_prompt->add_field([
    'name'      => 'Prompt URL',
    'id'        => $prefix . 'application_url',
    'type'      => 'text',
    'desc'      => 'e.g. https://foo.com/',
  ]);
  $application_prompt->add_field([
    'name'      => 'Supporting Copy',
    'id'        => $prefix . 'application_supporting_copy',
    'type'      => 'wysiwyg',
    // 'desc'      => 'e.g. https://foo.com/',
    'options'   => [
      'textarea_rows' => 5,
    ],
  ]);

  // Contact & Vendors
  $pre_footer = new_cmb2_box([
    'id'            => $prefix . 'pre_footer',
    'title'         => __( 'Contact Info & Vendors/Tools Intro', 'cmb2' ),
    'object_types'  => ['program'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $pre_footer->add_field([
    'name'      => 'Contact Info',
    'id'        => $prefix . 'contact_info',
    'type'      => 'wysiwyg',
    'options'   => [
      'textarea_rows' => 5,
    ],
  ]);
  $pre_footer->add_field([
    'name'      => 'Vendors & Tools Intro',
    'id'        => $prefix . 'vendors_intro',
    'type'      => 'wysiwyg',
    'options'   => [
      'textarea_rows' => 5,
    ],
  ]);

  // Vendor tools
  $vendors_tools = new_cmb2_box([
    'id'            => $prefix . 'vendors_tools',
    'title'         => esc_html__( 'Vendors & Tools', 'cmb2' ),
    'object_types'  => ['program'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $group_field_id = $vendors_tools->add_field([
    'id'              => $prefix .'intro_links',
    'type'            => 'group',
    'options'         => [
      'group_title'   => __( 'Vendor/Tool {#}', 'cmb2' ),
      'add_button'    => __( 'Add Another Vendor/Tool', 'cmb2' ),
      'remove_button' => __( 'Remove Vendor/Tool', 'cmb2' ),
      'sortable'      => true,
    ],
  ]);
  $vendors_tools->add_group_field( $group_field_id, [
    'name' => 'Vendor/Tool Title',
    'id'   => 'title',
    'type' => 'text',
  ]);
  $vendors_tools->add_group_field( $group_field_id, [
    'name' => 'URL',
    'id'   => 'url',
    'type' => 'text',
    'desc' => 'Use this if linking out',
  ]);
  $vendors_tools->add_group_field( $group_field_id, [
    'name' => 'Resource',
    'id'   => 'resource',
    'type' => 'file_list',
    'desc' => 'Use this field if link to downloadable resource',
  ]);
}
add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );

/**
 * Get program posts
 */
function get_program($opts=[]) {
  $args = [
    'numberposts' => (!empty($opts['numberposts']) ? $opts['numberposts'] : -1),
    'post_type'   => 'program',
  ];
  if (!empty($opts['category'])) {
    $args['tax_query'] = [
      [
        'taxonomy' => 'program_type',
        'field' => 'slug',
        'terms' => $opts['category']
      ]
    ];
  }

  // Display all matching posts using article-{$post_type}.php
  $programs_posts = get_posts($args);
  if (!$programs_posts) return false;
  $output = '';
  foreach ($programs_posts as $program_post) {
    ob_start();
    include(locate_template('templates/article-program.php'));
    $output .= ob_get_clean();
  }
  return $output;
}
