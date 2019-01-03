<?php
/**
 * Announcement post type
 */

namespace Firebelly\PostTypes\Announcement;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$announcements = new PostType('announcement', [
  'supports'   => ['title', 'editor', 'thumbnail'],
  'taxonomies' => ['announcement_type'],
  'rewrite'    => ['with_front' => false],
]);
$announcements->register();

/**
 * CMB2 custom fields
 */
function metaboxes() {
  $prefix = '_cmb2_';

  // Basic Info
  $announcement_info = new_cmb2_box([
    'id'            => $prefix . 'announcement_info',
    'title'         => __( 'Announcement Info', 'cmb2' ),
    'object_types'  => ['announcement'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $announcement_info->add_field([
    'name'      => 'Resources',
    'id'        => $prefix . 'resources',
    'type'      => 'file_list',
    'desc'      => 'Associated files and videos',
  ]);
}
add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );

/**
 * Get announcement posts
 */
function get_announcement($opts=[]) {
  $args = [
    'numberposts' => (!empty($opts['numberposts']) ? $opts['numberposts'] : -1),
    'post_type'   => 'announcement',
  ];

  // Display all matching posts using article-{$post_type}.php
  $announcements_posts = get_posts($args);
  if (!$announcements_posts) return false;
  $output = '';
  foreach ($announcements_posts as $announcement_post) {
    ob_start();
    include(locate_template('templates/article-announcement.php'));
    $output .= ob_get_clean();
  }
  return $output;
}
