<header class="site-header">
  <div class="wrap">
    <div class="brand"><a href="<?= esc_url(home_url('/')); ?>">
      <svg class="icon logo-icon" aria-hidden="true"><use xlink:href="#logo-icon"/></svg>
      <svg class="icon logo-wordmark" aria-hidden="true"><use xlink:href="#logo-wordmark"/></svg>
      <svg class="icon logo-wordmark-mobile" aria-hidden="true"><use xlink:href="#logo-wordmark-mobile"/></svg>
      <h1 class="sr-only"><span class="name"><?= get_bloginfo('name'); ?></span></h1>
    </a></div>
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
      <a class="search-toggle button -icon -round -small" href="/search/">
        <svg class="icon icon-search" aria-hidden="true"><use xlink:href="#icon-search"/></svg>
      </a>
      <a class="menu-toggle" href="#">
        <svg class="icon icon-hamburger" aria-hidden="true"><use xlink:href="#icon-hamburger"/></svg>
        <svg class="icon icon-cross" aria-hidden="true"><use xlink:href="#icon-cross"/></svg>
      </a>
    </nav>
  </div>
</header>
