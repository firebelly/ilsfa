<?php
/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */
$sage_includes = [
  'lib/assets.php',    // Scripts and stylesheets
  'lib/extras.php',    // Custom functions
  'lib/setup.php',     // Theme setup
  'lib/titles.php',    // Page titles
  'lib/wrapper.php',   // Theme wrapper class
  'lib/customizer.php' // Theme customizer
];

$firebelly_includes = [
  'lib/fb-disable-comments.php',   // Disables WP comments in admin and frontend
  'lib/fb-init.php',               // Various setup and config
  'lib/fb-media.php',              // Media functions (image size definitions, image helpers)
  'lib/fb-utils.php',              // Utility functions
  'lib/fb-ajax.php',               // AJAX functions
  'lib/fb-cmb2.php',               // CMB2 helper functions
  'lib/fb-page-fields.php',        // Extra fields for pages
  'lib/fb-post-fields.php',        // Extra fields for posts + CPTs
  'lib/fb-site-options.php',       // Custom site options page for admin
  // 'lib/cpt-example.php',           // Example CPT
];

$sage_includes = array_merge($sage_includes, $firebelly_includes);

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);
