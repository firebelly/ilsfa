<?php
/**
 * Organization post type
 */

namespace Firebelly\PostTypes\Organization;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$organizations = new PostType('organization', [
  'supports'   => ['title'],
  'taxonomies' => ['organization_type', 'region', 'organization_category'],
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

$region = new Taxonomy('region');
$region->register();

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
    'name'       => 'Show On Map',
    'id'         => $prefix . 'show_on_map',
    'type'       => 'checkbox',
    'default'    => \Firebelly\CMB2\set_checkbox_default_for_new_post(true),
    'desc'       => 'If checked, will show pin on maps',
  ]);
  $org_info->add_field([
    'name'      => 'Lat',
    'id'        => $prefix . 'lat',
    'type'      => 'text_small',
    'save_field'  => false,
    'attributes'  => array(
      'readonly' => 'readonly',
      'disabled' => 'disabled',
    ),
  ]);
  $org_info->add_field([
    'name'      => 'Lng',
    'id'        => $prefix . 'lng',
    'type'      => 'text_small',
    'save_field'  => false,
    'attributes'  => array(
      'readonly' => 'readonly',
      'disabled' => 'disabled',
    ),
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
    'offset'      => (!empty($opts['offset']) ? $opts['offset'] : 0),
    'orderby'     => 'title',
    'order'       => (!empty($opts['order']) && strtolower($opts['order']) != 'asc' ? 'DESC' : 'ASC'),
    'post_type'   => 'organization',
  ];
  if (!empty($opts['fields'])) {
    $args['fields'] = $opts['fields'];
  }
  $args['tax_query'] = [];
  if (!empty($opts['type'])) {
    $args['tax_query'][] =
      [
        'taxonomy' => 'organization_type',
        'field'    => 'slug',
        'terms'    => $opts['type']
      ];
  }
  if (!empty($opts['org_category'])) {
    $args['tax_query'][] =
      [
        'taxonomy' => 'organization_category',
        'field'    => 'slug',
        'terms'    => $opts['org_category']
      ];
  }
  if (!empty($opts['region'])) {
    $args['tax_query'][] =
      [
        'taxonomy' => 'region',
        'field'    => 'slug',
        'terms'    => $opts['region']
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
    $organization_post->meta = get_post_meta($organization_post->ID);
    $show_on_map = !empty($organization_post->meta['_cmb2_show_on_map']) && $organization_post->meta['_cmb2_show_on_map'][0] == 'on';
    $output .= '<li class="item map-point" data-id="'.$organization_post->ID.'" data-url="' . (!empty($organization_post->meta['_cmb2_website']) ? $organization_post->meta['_cmb2_website'][0] : '') . '" data-id="' . $organization_post->ID . '" data-title="' . $organization_post->post_title . '"';
    if ($show_on_map && !empty($organization_post->meta['_cmb2_lat']) && !empty($organization_post->meta['_cmb2_lng'])) {
      $output .= ' data-lat="' . $organization_post->meta['_cmb2_lat'][0] . '" data-lng="' . $organization_post->meta['_cmb2_lng'][0] . '"';
    }
    $output .= '>';
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
  $vars[] = 'sort';
  $vars[] = 'region';
  $vars[] = 'org_category';
  return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\add_query_vars_filter');

/**
  * Redirect single Organization requests to either /grassroots-education/ or /job-training/ landing pages
  */
function redirect_single_organizations() {
  global $wp, $wpdb, $post;
  $request_url = $wp->request;

  // Single organization? Redirect to proper landing page based on type
  if (preg_match('#^organizations/#', $request_url)) {
    if ($type = \Firebelly\Utils\get_first_term($post->ID, 'organization_type')) {
      $new_url = '/' . $type->slug . '/#organizations';
    } else {
      $new_url = '/grassroots-education/#organizations';
    }
    wp_redirect($new_url, 301);
  }
}
add_action('template_redirect', __NAMESPACE__.'\\redirect_single_organizations');

/**
 * Geocode address on save
 */
add_action('save_post_organization', '\Firebelly\PostTypes\Event\geocode_address', 20, 2);
