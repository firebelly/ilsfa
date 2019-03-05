<?php if (!empty($organizations) || !empty($post_meta['_cmb2_organization_directory_intro'])): ?>
<div class="organizations-listing" data-load-more-parent>
  <?php if (!empty($post_meta['_cmb2_organization_directory_intro'])): ?>
    <div class="user-content" id="organizations">
      <?= apply_filters('the_content', $post_meta['_cmb2_organization_directory_intro'][0]) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($organizations)): ?>
    <div class="grid filters">
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
      <div class="one-half -tail">
        <h4>Filter By Category</h4>
        <div class="select-wrap">
          <svg class="icon icon-arrow-dropdown" aria-hidden="true"><use xlink:href="#icon-arrow-dropdown"/></svg>
          <select name="org_filter" class="jumpselect">
            <option <?= $org_filter == '' ? 'selected ' : '' ?>value="<?= add_query_arg('org_filter', '') ?>#organizations">* All *</option>
            <?php
            $organization_categories = get_terms(['taxonomy' => 'organization_category', 'hide_empty' => false]);
            foreach ($organization_categories as $term): ?>
              <option <?= $org_filter == $term->slug ? 'selected ' : '' ?>value="<?= add_query_arg('org_filter', $term->slug) ?>#organizations"><?= $term->name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <ul class="cards compact-grid -three-per masonry" data-load-more-container>
    <?= $organizations ?>
    </ul>

    <?php if ($total_pages>1): ?>
      <div class="grid">
        <div class="one-half">
          <div class="load-more" data-post-type="organization" data-page-at="<?= $paged ?>" data-per-page="<?= $per_page ?>" data-total-pages="<?= $total_pages ?>" data-org-sort="<?= $org_sort ?>" data-org-type="<?= $org_type ?>" data-org-filter="<?= $org_filter ?>">
            <a class="button -wide -icon-right" href="#">
              Load More <svg class="icon icon-plus" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
