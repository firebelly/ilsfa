<?php
/*
  Template name: Grassroots Education
*/

// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Get query vars and build args for pulling organizations
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$per_page = get_option('posts_per_page');
$org_sort = get_query_var('org_sort', 'asc');
$org_type = 'grassroots-education';
$args = [
  'type'  => 'grassroots-education',
  'order' => $org_sort,
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

<div class="organizations-listing" data-load-more-parent>
  <?php if (!empty($post_meta['_cmb2_organization_directory_intro'])): ?>
    <div class="user-content" id="organizations">
      <?= apply_filters('the_content', $post_meta['_cmb2_organization_directory_intro'][0]) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($organizations)): ?>
    <div class="grid">
      <div class="one-half">
        <h4>Sort By</h4>
        <div class="select-wrap">
          <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
          <select name="org_sort" class="jumpselect">
            <?php foreach ([
              'asc' => 'Name (A-Z)',
              'desc' => 'Name (Z-A)',
            ] as $value => $title): ?>
              <option <?= $org_sort == $value ? 'selected ' : '' ?>value="<?= add_query_arg('org_sort', $value) ?>#organizations"><?= $title ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <ul class="cards compact-grid -four-per masonry" data-load-more-container>
    <?= $organizations ?>
    </ul>

    <?php if ($total_pages>1): ?>
      <div class="grid">
        <div class="one-half">
          <div class="load-more" data-post-type="organization" data-page-at="<?= $paged ?>" data-per-page="<?= $per_page ?>" data-total-pages="<?= $total_pages ?>" data-org-sort="<?= $org_sort ?>"> data-org-type="<?= $org_type ?>"><a class="button -wide -icon-right" href="#">Load More <svg class="icon icon-plus" aria-hidden="true"><use xlink:href="#icon-plus"/></svg></a></div>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
