<?php
/*
  Template name: Events
*/

// Get all post_meta
$post = get_page_by_path('/events/');
$post_meta = get_post_meta($post->ID);

// Get query vars and build args for pulling events
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$per_page = get_option('posts_per_page');
$sort = get_query_var('sort', 'desc');
$region = get_query_var('region', '');
$args = [
  'order'    => $sort,
  'region'   => $region,
];

// Get post count for load more
$total_posts = \Firebelly\PostTypes\Event\get_events(array_merge($args, ['countposts' => 1]));
$total_pages = ($total_posts > 0) ? ceil($total_posts / $per_page) : 1;

// Actually get posts
$events = \Firebelly\PostTypes\Event\get_events($args);

// Get regions used by events
$event_ids = \Firebelly\PostTypes\Event\get_events([
  'numberposts' => -1,
  'fields'      => 'ids',
  'output'      => 'array',
]);

// Find regions used by events
if (!empty($event_ids)) {
  $regions = \Firebelly\Utils\get_active_terms_for_posts($event_ids, 'region');
} else {
  $regions = [];
}

get_template_part('templates/page', 'header-tertiary');
?>
<div class="events-listing" id="events" data-load-more-parent>
  <?php if (!empty($region) || !empty($event_ids)): ?>
  <div class="grid filters">
    <div class="one-half">
      <h4>Sort By</h4>
      <div class="select-wrap">
        <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
        <select name="sort" class="jumpselect">
          <?php foreach ([
            'desc' => 'Most recent first',
            'asc' => 'Oldest first',
          ] as $value => $title): ?>
            <option <?= $sort == $value ? 'selected ' : '' ?>value="<?= add_query_arg('sort', $value) ?>#events"><?= $title ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="one-half">
      <h4>Region</h4>
      <div class="select-wrap">
        <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
        <select name="region" class="jumpselect">
          <option <?= $region == '' ? 'selected ' : '' ?>value="<?= add_query_arg('region', '') ?>#events">* All *</option>
          <?php
          foreach ($regions as $term): ?>
            <option <?= $region == $term->slug ? 'selected ' : '' ?>value="<?= add_query_arg('region', $term->slug) ?>#events"><?= $term->name ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <ul class="cards compact-grid" data-load-more-container>
  <?= $events ?>
  </ul>

  <?php if ($total_pages>1): ?>
    <div class="grid">
      <div class="one-half">
        <div class="load-more" data-post-type="event" data-page-at="<?= $paged ?>" data-per-page="<?= $per_page ?>" data-total-pages="<?= $total_pages ?>" data-sort="<?= $sort ?>" data-org-type="" data-region="<?= $region ?>">
          <a class="button -wide -icon-right" href="#">
            Load More <svg class="icon icon-plus" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
