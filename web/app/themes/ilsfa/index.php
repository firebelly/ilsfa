<?php get_template_part('templates/page', 'header-tertiary'); ?>

<?php if (!have_posts()) : ?>
  <div class="alert alert-warning">
    <?php _e('Sorry, no results were found.', 'sage'); ?>
  </div>
  <?php get_search_form(); ?>
<?php endif; ?>

<div class="page-content">
  <div class="user-content">
    <?php while (have_posts()) : the_post(); ?>
      <?php \Firebelly\Utils\get_template_part_with_vars('templates/article', get_post_type(), [get_post_type().'_post' => $post]); ?>
    <?php endwhile; ?>
  </div>
</div>

<?php the_posts_navigation(); ?>
