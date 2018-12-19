<?php
/**
 * CMB2 custom fields
 */

namespace Firebelly\CMB2;

/**
 * Get post options for CMB2 select
 */
function get_post_options($query_args) {
    $args = wp_parse_args($query_args, array(
        'post_type'   => 'post',
        'numberposts' => -1,
        'post_parent' => 0,
    ));
    $posts = get_posts($args);
    return wp_list_pluck($posts, 'post_title', 'ID');
}
/**
 * Example how to use:
 * in cmb2 field: `'options_cb' => '\Firebelly\CMB2\get_people'`
 * then add function below
 */

// function get_people() {
//     return get_post_options(['post_type' => 'person']);
// }


/**
 * Exclude metabox on specific slugs
 * @param  object $cmb CMB2 object
 * @return bool        True/false whether to show the metabox
 */
function exclude_for_slugs($cmb) {
  $slugs_to_exclude = $cmb->prop('exclude_slugs', []);
  $post_slug = get_post_field('post_name', $cmb->object_id());
  $excluded = in_array($post_slug, $slugs_to_exclude, true);
  return !$excluded;
}

/**
 * Show metabox on specific slugs
 * @param  object $cmb CMB2 object
 * @return bool        True/false whether to show the metabox
 */
function show_for_slugs($cmb) {
  $slugs_to_show = $cmb->prop('show_slugs', []);
  $post_slug = get_post_field('post_name', $cmb->object_id());
  $show = in_array($post_slug, $slugs_to_show, true);
  return $show;
}
