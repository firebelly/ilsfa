<?php while (have_posts()) : the_post(); ?>
  <?php
  $post_meta = get_post_meta($post->ID);
  get_template_part('templates/page', 'header-tertiary');
  ?>
  <div class="page-content user-content">

    <article <?php post_class(); ?>>
      <div class="entry-meta">
        <?php get_template_part('templates/entry-meta'); ?>
      </div>
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
      <footer>
        <?php wp_link_pages(['before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']); ?>
      </footer>
      <?php comments_template('/templates/comments.php'); ?>
      <?php \Firebelly\Utils\get_template_part_with_vars('templates/resources', 'list', ['post_meta' => $post_meta]); ?>
    </article>

  </div>
<?php endwhile; ?>
