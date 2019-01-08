<?php
global $wp_query;
$total_results = $wp_query->found_posts;
?>
<header class="page-header -wide -text-only">
  <div class="page-intro">
    <div class="grid">
      <div class="page-titles">
        <h3>Showing results for</h3>
        <h1>&ldquo;<?= get_search_query() ?>&rdquo;</h1>
      </div>
    </div>

    <div class="page-content grid">
      <div class="one-half -left">
        <?php if (have_posts()) : ?>
          <h1><?= $total_results ?> Results Found</h1>
        <?php else: ?>
          <h1>No Results Found</h1>
        <?php endif; ?>
        <?= get_search_form() ?>
      </div>
      <div class="one-half -right">
        <?php while (have_posts()) : the_post(); ?>
          <?php get_template_part('templates/content', 'search'); ?>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</header>

<div class="column-wrap">
  <?= \Firebelly\Utils\pagination(); ?>
</div>
