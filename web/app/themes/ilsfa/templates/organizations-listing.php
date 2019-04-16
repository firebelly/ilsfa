<?php
  // Get org_regions used by org_type
  $org_post_ids = \Firebelly\PostTypes\Organization\get_organizations([
    'numberposts' => -1,
    'fields'      => 'ids',
    'output'      => 'array',
    'type'        => $org_type
  ]);

  if (is_array($org_post_ids)) {
    // Find regions that use those post IDs
    $org_region_ids = $wpdb->get_col("
    SELECT t.term_id FROM {$wpdb->terms} AS t
          INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
          INNER JOIN {$wpdb->term_relationships} AS r ON r.term_taxonomy_id = tt.term_taxonomy_id
          WHERE tt.taxonomy IN('organization_region')
          AND r.object_id IN (".implode(',', $org_post_ids).")
          GROUP BY t.term_id
    ");

    // Pull those regions for filtering
    $organization_regions = get_terms([
      'taxonomy' => 'organization_region',
      'include'  => $org_region_ids
    ]);
  } else {
    $organization_regions = [];
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

      <?php if (!empty($organizations)): ?>
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
        <h4>Region</h4>
        <div class="select-wrap">
          <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
          <select name="org_region" class="jumpselect">
            <option <?= $org_region == '' ? 'selected ' : '' ?>value="<?= add_query_arg('org_region', '') ?>#organizations">* All *</option>
            <?php
            foreach ($organization_regions as $term): ?>
              <option <?= $org_region == $term->slug ? 'selected ' : '' ?>value="<?= add_query_arg('org_region', $term->slug) ?>#organizations"><?= $term->name ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <ul class="cards" data-load-more-container>
        <?= $organizations ?>
        </ul>

        <?php if ($total_pages>1): ?>
            <div class="load-more" data-post-type="organization" data-page-at="<?= $paged ?>" data-per-page="<?= $per_page ?>" data-total-pages="<?= $total_pages ?>" data-org-sort="<?= $org_sort ?>" data-org-type="<?= $org_type ?>" data-org-region="<?= $org_region ?>">
              <a class="button -wide -icon-right" href="#">
                Load More <svg class="icon icon-plus" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
              </a>
            </div>
        <?php endif; ?>
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
