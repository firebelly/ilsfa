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
