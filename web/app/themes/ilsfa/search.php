<?php
global $wp_query;
$total_results = $wp_query->found_posts;
?>
<header class="page-header tertiary">
  <div class="title-wrap">
    <h1 class="page-title"><?= $total_results ?> results for <br>&ldquo;<?= get_search_query() ?>&rdquo;</h1>
  </div>
</header>

<div class="search-large">
  <?= get_search_form() ?>
</div>

<div class="page-content">
  <?php while (have_posts()) : the_post(); ?>
    <?php get_template_part('templates/article', 'search-result'); ?>
  <?php endwhile; ?>
</div>

<?= \Firebelly\Utils\pagination(); ?>
