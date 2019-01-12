<header class="site-header">
  <div class="wrap">
    <h1 class="brand"><a href="<?= esc_url(home_url('/')); ?>">
      <svg class="icon logo-icon" aria-hidden="true"><use xlink:href="#logo-icon"/></svg>
      <span class="name"><?= get_bloginfo('name'); ?></span>
    </a></h1>
    <nav class="site-nav">
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'primary-nav']);
      endif;
      if (has_nav_menu('utility_navigation')) :
        wp_nav_menu(['theme_location' => 'utility_navigation', 'menu_class' => 'utility-nav']);
      endif;
      ?>
      <div class="nav-search">
        <?= get_search_form() ?>
      </div>
    </nav>
  </div>
</header>
