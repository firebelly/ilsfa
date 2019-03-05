<?php
/**
 * Organization post type
 */

namespace Firebelly\PostTypes\Organization;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$organizations = new PostType('organization', [
  'supports'   => ['title'],
  'taxonomies' => ['organization_type', 'organization_category'],
  'rewrite'    => ['with_front' => false],
]);

// Custom taxonomies
$organization_type = new Taxonomy('organization_type');
$organization_type->register();

$organization_category = new Taxonomy([
  'name'     => 'organization_category',
  'slug'     => 'organization_category',
  'plural'   => 'Organization Categories',
]);
$organization_category->register();

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
    'name'       => 'Description',
    'id'         => $prefix . 'description',
    'type'       => 'wysiwyg',
    'options'    => [
      'textarea_rows' => 10,
    ],
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

}
add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );

/**
 * Get organization posts
 */
function get_organizations($opts=[]) {
  $args = [
    'numberposts' => (!empty($opts['numberposts']) ? $opts['numberposts'] : get_option('posts_per_page')),
    'offset' => (!empty($opts['offset']) ? $opts['offset'] : 0),
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
  if (!empty($opts['category'])) {
    $args['tax_query'] = [
      [
        'taxonomy' => 'organization_category',
        'field' => 'slug',
        'terms' => $opts['category']
      ]
    ];
  }

  $organizations_posts = get_posts($args);
  if (!$organizations_posts) {
    return '<p class="nothing-found">No posts found.</p>';
  }

  // Just count posts (used for load-more buttons)
  if (!empty($opts['countposts'])) {
    $args = array_merge($args, [
      'posts_per_page' => -1,
      'fields' => 'ids',
    ]);
    $count_query = new \WP_Query($args);
    return $count_query->found_posts;
  }

  // Just return array of posts?
  if (!empty($opts['output']) && $opts['output'] == 'array') {
    return $organizations_posts;
  }

  // Display all matching posts using article-{$post_type}.php
  $output = '';
  foreach ($organizations_posts as $organization_post) {
    $output .= '<li class="item">';
    ob_start();
    include(locate_template('templates/article-organization.php'));
    $output .= ob_get_clean();
    $output .= '</li>';
  }
  return $output;
}

/**
 * Add query vars for filtering/sorting
 */
function add_query_vars_filter($vars){
  $vars[] = 'org_sort';
  $vars[] = 'org_filter';
  return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\add_query_vars_filter');

function load_more_organizations() {
  $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
  $per_page = !empty($_REQUEST['per_page']) ? $_REQUEST['per_page'] : get_option('posts_per_page');
  $order = !empty($_REQUEST['org_sort']) ? $_REQUEST['org_sort'] : 'asc';
  $category = !empty($_REQUEST['org_filter']) ? $_REQUEST['org_filter'] : '';
  $type = !empty($_REQUEST['org_type']) ? $_REQUEST['org_type'] : 'grassroots-education';
  $offset = ($page-1) * $per_page;
  $args = [
    'offset'      => $offset,
    'numberposts' => $per_page,
    'order'       => $order,
    'category'    => $category,
    'type'        => $type,
  ];
  echo get_organizations($args);
}
add_action('wp_ajax_load_more_organizations', __NAMESPACE__ . '\\load_more_organizations' );
add_action('wp_ajax_nopriv_load_more_organizations', __NAMESPACE__ . '\\load_more_organizations');
