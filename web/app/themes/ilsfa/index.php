<?php
// Announcements page for header overrides
$announcements_page = get_post(get_option('page_for_posts'));
?>

<?php \Firebelly\Utils\get_template_part_with_vars('templates/page', 'header-tertiary', [ 'post' => $announcements_page, 'nojumpto' => 1 ]); ?>

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

<?= \Firebelly\Utils\pagination(); ?>
