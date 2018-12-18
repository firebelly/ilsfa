<?php
/**
 * Example post type
 */

namespace Firebelly\PostTypes\Example;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes

$examples = new PostType('example', [
  'supports'   => ['title', 'editor', 'thumbnail'],
  'rewrite'    => ['with_front' => false],
]);
$examples->register();

/**
 * CMB2 custom fields
 */
function metaboxes() {
  $prefix = '_cmb2_';

  $example_info = new_cmb2_box([
    'id'            => $prefix . 'example_info',
    'title'         => __( 'Example Info', 'cmb2' ),
    'object_types'  => ['example'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $example_info->add_field([
    'name'      => 'Type',
    'id'        => $prefix . 'example_type',
    'type'      => 'text_medium',
    'column'    => array( // adds this field to admin columns
      'position' => 2,
      'name'     => 'Type',
    ),
    // 'desc'      => 'Help text',
  ]);

}
add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );

/**
 * Get examples
 */
function get_examples($options=[]) {
  if (empty($options['num_posts'])) $options['num_posts'] = -1;
  $args = [
    'numberposts' => $options['num_posts'],
    'post_type'   => 'example',
  ];

  // Display all matching posts using article-{$post_type}.php
  $examples_posts = get_posts($args);
  if (!$examples_posts) return false;
  $output = '';
  foreach ($examples_posts as $example_post):
    $example_post->column_width = $options['column-width'];
    ob_start();
    include(locate_template('templates/article-example.php'));
    $output .= ob_get_clean();
  endforeach;
  return $output;
}
