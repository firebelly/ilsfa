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