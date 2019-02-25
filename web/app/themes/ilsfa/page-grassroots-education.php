<?php
/*
  Template name: Grassroots Education
*/

// Get all post_meta
$post_meta = get_post_meta($post->ID);
$org_sort = get_query_var('org_sort', 'asc');
$organizations = \Firebelly\PostTypes\Organization\get_organizations([
  'type'   => 'grassroots-education',
  'output' => 'array',
  'order'  => $org_sort,
]);
?>

<?php
get_template_part('templates/page', 'header');
?>

<div class="page-content -breakout-images">
  <div class="user-content">
    <?= apply_filters('the_content', $post->post_content); ?>
  </div>
</div>

<div class="organizations-listing">
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
    <ul class="cards compact-grid -four-per">
    <?php foreach ($organizations as $organization): ?>
      <li class="item">
        <?php \Firebelly\Utils\get_template_part_with_vars('templates/article', 'organization', ['organization_post' => $organization]); ?>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
