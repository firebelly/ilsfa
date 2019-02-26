<?php while (have_posts()) : the_post(); ?>
  <?php
  $post_meta = get_post_meta($post->ID);
  get_template_part('templates/page', 'header-tertiary');
  ?>
  <div class="page-content">
    <div class="user-content">
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
        <?php if (!empty($post_meta['_cmb2_post_resources'])): ?>
          <?php \Firebelly\Utils\get_template_part_with_vars('templates/resources', 'list', ['resources_list' => $post_meta['_cmb2_post_resources'][0]]); ?>
        <?php endif; ?>
      </article>
    </div>
  </div>
<?php endwhile; ?>
