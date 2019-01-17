<?php

namespace Firebelly\Utils;

/**
 * Bump up # search results
 */
function search_queries( $query ) {
  if ( !is_admin() && is_search() ) {
    $query->set( 'posts_per_page', 40 );
  }
  return $query;
}
add_filter( 'pre_get_posts', __NAMESPACE__ . '\\search_queries' );

/**
 * Custom li'l excerpt function
 */
function get_excerpt( $post, $length=15, $force_content=false ) {
  $excerpt = trim($post->post_excerpt);
  if (!$excerpt || $force_content) {
    $excerpt = $post->post_content;
    $excerpt = strip_shortcodes( $excerpt );
    $excerpt = apply_filters( 'the_content', $excerpt );
    $excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
    $excerpt_length = apply_filters( 'excerpt_length', $length );
    $excerpt = wp_trim_words( $excerpt, $excerpt_length );
  }
  return $excerpt;
}

/**
 * Get top ancestor for post
 */
function get_top_ancestor($post){
  if (!$post) return;
  $ancestors = $post->ancestors;
  if ($ancestors) {
    return end($ancestors);
  } else {
    return $post->ID;
  }
}

/**
 * Get first term for post
 */
function get_first_term($post, $taxonomy='category') {
  $return = false;
  if ($terms = get_the_terms($post->ID, $taxonomy))
    $return = array_pop($terms);
  return $return;
}

/**
 * Get page content from slug
 */
function get_page_content($slug) {
  $return = false;
  if ($page = get_page_by_path($slug))
    $return = apply_filters('the_content', $page->post_content);
  return $return;
}

/**
 * Get category for post
 */
function get_category($post) {
  if ($category = get_the_category($post)) {
    return $category[0];
  } else return false;
}

/**
 * Get num_pages for category given slug + per_page
 */
function get_total_pages($category, $per_page) {
  $cat_info = get_category_by_slug($category);
  $num_pages = ceil($cat_info->count / $per_page);
  return $num_pages;
}

/**
 * Support for sending vars to get_template_part()
 * usage: \Firebelly\Utils\get_template_part_with_vars('templates/page', 'header', ['foo' => 'bar']);
 * (from https://github.com/JolekPress/Get-Template-Part-With-Variables)
 */
function get_template_part_with_vars($slug, $name = null, array $namedVariables = []) {
  // Taken from standard get_template_part function
  \do_action("get_template_part_{$slug}", $slug, $name);

  $templates = array();
  $name = (string)$name;
  if ('' !== $name)
      $templates[] = "{$slug}-{$name}.php";

  $templates[] = "{$slug}.php";

  $template = \locate_template($templates, false, false);

  if (empty($template)) {
    return;
  }

  // @see load_template (wp-includes/template.php) - these are needed for WordPress to work.
  global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

  if (is_array($wp_query->query_vars)) {
    \extract($wp_query->query_vars, EXTR_SKIP);
  }

  if (isset($s)) {
      $s = \esc_attr($s);
  }
  // End standard WordPress behavior

  foreach ($namedVariables as $variableName => $value) {
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\x7f-\xff]*/', $variableName)) {
      trigger_error('Variable names must be valid. Skipping "' . $variableName . '" because it is not a valid variable name.');
      continue;
    }

    // Allowing var overrides to set $post, let's see if it causes issues –nate
    // if (isset($$variableName)) {
    //   trigger_error("{$variableName} already existed, probably set by WordPress, so it wasn't set to {$value} like you wanted. Instead it is set to: " . print_r($$variableName, true));
    //   continue;
    // }

    $$variableName = $value;
  }

  require $template;
}

/**
 * Custom pagination
 * @param  array  $args array of optional overrides
 * @return string       html output
 */
function pagination($args=[]) {
  $defaults = array(
    'type'      => 'array',
    'nav_class' => 'pagination',
    'prev_text' => __('Prev'),
    'next_text' => __('Next'),
    'li_class'  => ''
  );
  $args = wp_parse_args( $args, $defaults );
  $page_links = paginate_links( $args );
  if ( $page_links ) {
    $r = '';
    $ul_class = empty($args['ul_class']) ? '' : ' ' . $args['ul_class'];
    $r .= '<nav class="'. $args['nav_class'] .'" aria-label="navigation">' . "\n\t";
    $r .= '<ul>' . "\n";
    foreach ($page_links as $link) {
      $li_classes = !empty($args['li_class']) ? explode(' ', $args['li_class']) : [];
      strpos($link, 'current') !== false ? array_push($li_classes, 'active') : ( strpos($link, 'dots') !== false ? array_push($li_classes, 'disabled') : '' );
      $class = empty($li_classes) ? '' : ' class="' . join(" ", $li_classes) . '"';
      $after_number = !preg_match('/prev|next|dots/', $link) ? ',' : '';
      $r .= "\t\t" . '<li' . $class . '>' . $link . $after_number . '</li>' . "\n";
      // If dots link, remove previous comma
      if (strpos($link, 'dots') !== false) {
        $r = strrev(implode(strrev(''), explode(strrev(','), strrev($r), 2)));
      }
    }
    $r .= "\t</ul>";
    $r .= "\n</nav>";
    // Remove last comma
    $r = strrev(implode(strrev(''), explode(strrev(','), strrev($r), 2)));
    return $r;
  }
}

/**
 * Edit post link for various front end areas
 */
function admin_edit_link($post_or_term) {
  if (!empty($post_or_term->term_id)) {
    $link = get_edit_term_link($post_or_term->term_id);
  } else {
    $link = get_edit_post_link($post_or_term->ID);
  }
  return !empty($link) ? '<a class="edit-link" href="'.$link.'">Edit</a>' : '';
}

/**
 * Edit post link for cronjobs
 */
function cronjob_edit_link($id=0, $context = 'display') {
  if (!$post = get_post($id))
    return;
  $action = '&action=edit';

  $post_type_object = get_post_type_object($post->post_type);
  if (!$post_type_object)
    return;

  return apply_filters('get_edit_post_link', admin_url(sprintf($post_type_object->_edit_link . $action, $post->ID)), $post->ID, $context);
}
