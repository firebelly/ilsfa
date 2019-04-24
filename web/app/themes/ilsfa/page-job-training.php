<?php
/*
  Template name: Job Training
*/

// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Get query vars and build args for pulling organizations
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$per_page = get_option('posts_per_page');
$sort = get_query_var('sort', 'asc');
$region = get_query_var('region', '');
$org_type = 'job-training';
$args = [
  'type'     => $org_type,
  'order'    => $sort,
  'region'   => $region,
];

// Get post count for load more
$total_posts = \Firebelly\PostTypes\Organization\get_organizations(array_merge($args, ['countposts' => 1]));
$total_pages = ($total_posts > 0) ? ceil($total_posts / $per_page) : 1;
// Actually get posts
$organizations = \Firebelly\PostTypes\Organization\get_organizations($args);
?>

<?php
get_template_part('templates/page', 'header');
?>

<div class="page-content -breakout-images">
  <div class="user-content">
    <?= apply_filters('the_content', $post->post_content); ?>
  </div>
</div>

<?php \Firebelly\Utils\get_template_part_with_vars('templates/organizations', 'listing', [
  'organizations' => $organizations,
  'post_meta'     => $post_meta,
  'paged'         => $paged,
  'per_page'      => $per_page,
  'total_pages'   => $total_pages,
  'sort'          => $sort,
  'org_type'      => $org_type,
  'region'        => $region,
]); ?>
