<?php
/**
 * Organization post type
 */

namespace Firebelly\PostTypes\Organization;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$organizations = new PostType('organization', [
  'supports'   => ['title'],
  'taxonomies' => ['organization_type'],
  'rewrite'    => ['with_front' => false],
]);

// Custom taxonomies
$organization_type = new Taxonomy('organization_type');
$organization_type->register();

$organizations->register();

/**
 * CMB2 custom fields
 */
function metaboxes() {
  $prefix = '_cmb2_';

  // Organization info
  $org_info = new_cmb2_box([
    'id'            => $prefix . 'org_info',
    'title'         => __( 'Organization Info', 'cmb2' ),
    'object_types'  => ['organization'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $org_info->add_field([
    'name'       => 'Address',
    'id'         => $prefix . 'address',
    'type'       => 'address',
  ]);
  $org_info->add_field([
    'name'      => 'Email',
    'id'        => $prefix . 'email',
    'type'      => 'text',
  ]);
  $org_info->add_field([
    'name'      => 'Phone',
    'id'        => $prefix . 'phone',
    'type'      => 'text',
    'column'    => [
      'position' => 2,
    ]
  ]);
  $org_info->add_field([
    'name'      => 'Website',
    'id'        => $prefix . 'website',
    'type'      => 'text_url',
    'column'    => [
      'position' => 3,
    ]
  ]);

  // Organization Directory page fields
  $organization_directory = new_cmb2_box([
    'id'            => $prefix . 'organization_directory',
    'title'         => esc_html__( 'Organization Directory', 'cmb2' ),
    'object_types'  => ['page'],
    'context'       => 'top',
    'context'       => 'normal',
    'show_slugs'    => ['grassroots-education'], // 'job-training'
    'show_on_cb'    => '\Firebelly\CMB2\show_for_slugs',
    'priority'      => 'high',
  ]);
  $organization_directory->add_field([
    'name' => esc_html__( 'Intro', 'cmb2' ),
    'id'   => $prefix .'organization_directory_intro',
    'type' => 'wysiwyg',
    'options' => [
      'textarea_rows' => 4,
    ],
  ]);
}
add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );

/**
 * Get organization posts
 */
function get_organizations($opts=[]) {
  $args = [
    'numberposts' => (!empty($opts['numberposts']) ? $opts['numberposts'] : -1),
    'orderby' => 'title',
    'order' => (!empty($opts['order']) && strtolower($opts['order']) != 'asc' ? 'DESC' : 'ASC'),
    'post_type'   => 'organization',
  ];
  if (!empty($opts['type'])) {
    $args['tax_query'] = [
      [
        'taxonomy' => 'organization_type',
        'field' => 'slug',
        'terms' => $opts['type']
      ]
    ];
  }

  $organizations_posts = get_posts($args);
  if (!$organizations_posts) {
    return false;
  }

  // Just return array of posts?
  if (!empty($opts['output']) && $opts['output'] == 'array') {
    return $organizations_posts;
  }

  // Display all matching posts using article-{$post_type}.php
  $output = '';
  foreach ($organizations_posts as $organization_post) {
    ob_start();
    include(locate_template('templates/article-organization.php'));
    $output .= ob_get_clean();
  }
  return $output;
}

/**
 * Add query vars for filtering/sorting
 */
function add_query_vars_filter($vars){
  $vars[] = 'org_sort';
  return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\add_query_vars_filter');