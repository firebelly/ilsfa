<?php
namespace Firebelly\Ajax;

/**
 * Add variables to global js scope
 */
add_action('wp_enqueue_scripts', function() {
	wp_localize_script('sage/js', 'wp_ajax_url', admin_url('admin-ajax.php'));
	wp_localize_script('sage/js', 'mapbox_key', getenv('MAPBOX_KEY'));
	wp_localize_script('sage/js', 'mapbox_style', getenv('MAPBOX_STYLE'));
}, 100);

/**
 * Silly ajax helper, returns true if xmlhttprequest
 */
function is_ajax() {
  return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

/**
 * Load More Posts handler
 */
function load_more_posts() {
  $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
  $post_type = !empty($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'organization';
  $per_page = !empty($_REQUEST['per_page']) ? $_REQUEST['per_page'] : get_option('posts_per_page');
  $order = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : 'asc';
  $region = !empty($_REQUEST['region']) ? $_REQUEST['region'] : '';
  $offset = ($page-1) * $per_page;
  $args = [
    'offset'      => $offset,
    'numberposts' => $per_page,
    'order'       => $order,
    'region'      => $region,
  ];
  if ($post_type == 'organization') {
    $args['org_category'] = !empty($_REQUEST['org_category']) ? $_REQUEST['org_category'] : '';
  	$args['type'] = !empty($_REQUEST['org_type']) ? $_REQUEST['org_type'] : 'grassroots-education';
    echo \Firebelly\PostTypes\Organization\get_organizations($args);
  } else if ($post_type == 'event') {
  	echo \Firebelly\PostTypes\Event\get_events($args);
  } else {
  	// Not currently used
  	echo 'Bad Request.';
  }
}
add_action('wp_ajax_load_more_posts', __NAMESPACE__ . '\\load_more_posts' );
add_action('wp_ajax_nopriv_load_more_posts', __NAMESPACE__ . '\\load_more_posts');
