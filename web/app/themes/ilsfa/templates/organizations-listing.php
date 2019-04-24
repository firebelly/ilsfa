<?php
  // Get org_regions used by org_type
  $org_post_ids = \Firebelly\PostTypes\Organization\get_organizations([
    'numberposts' => -1,
    'fields'      => 'ids',
    'output'      => 'array',
    'type'        => $org_type
  ]);

  if (is_array($org_post_ids)) {
    $regions = \Firebelly\Utils\get_active_terms_for_posts($org_post_ids, 'region');
  } else {
    $regions = [];
  }

?>

<?php if (!empty($organizations) || !empty($post_meta['_cmb2_organization_directory_intro'])): ?>
<div class="organizations-listing" data-load-more-parent>
  <div class="grid" id="map-sticky-parent">
    <div class="one-half filters">
      <?php if (!empty($post_meta['_cmb2_organization_directory_intro'])): ?>
        <div class="user-content" id="organizations">
          <?= apply_filters('the_content', $post_meta['_cmb2_organization_directory_intro'][0]) ?>
        </div>
      <?php endif; ?>

      <h4>Sort By</h4>
      <div class="select-wrap">
        <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
        <select name="sort" class="jumpselect">
          <?php foreach ([
            'asc' => 'Name (A-Z)',
            'desc' => 'Name (Z-A)',
          ] as $value => $title): ?>
            <option <?= $sort == $value ? 'selected ' : '' ?>value="<?= add_query_arg('sort', $value) ?>#organizations"><?= $title ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <h4>Region</h4>
      <div class="select-wrap">
        <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
        <select name="region" class="jumpselect">
          <option <?= $region == '' ? 'selected ' : '' ?>value="<?= add_query_arg('region', '') ?>#organizations">* All *</option>
          <?php
          foreach ($regions as $term): ?>
            <option <?= $region == $term->slug ? 'selected ' : '' ?>value="<?= add_query_arg('region', $term->slug) ?>#organizations"><?= $term->name ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <ul class="cards" data-load-more-container>
      <?= $organizations ?>
      </ul>

      <?php if ($total_pages>1): ?>
          <div class="load-more" data-post-type="organization" data-page-at="<?= $paged ?>" data-per-page="<?= $per_page ?>" data-total-pages="<?= $total_pages ?>" data-org-sort="<?= $sort ?>" data-org-type="<?= $org_type ?>" data-org-region="<?= $region ?>">
            <a class="button -wide -icon-right" href="#">
              Load More <svg class="icon icon-plus" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
            </a>
          </div>
      <?php endif; ?>
    </div>

    <div class="one-half -tail map-column">
      <div class="map-container">
        <div id="map"></div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
