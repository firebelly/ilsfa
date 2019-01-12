<?php
/**
 * Event post type
 */

namespace Firebelly\PostTypes\Event;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$cpt = new PostType(['name' => 'event', 'slug' => 'event'], [
  'taxonomies' => ['event_type', 'event_series'],
  'supports'   => ['title', 'editor', 'thumbnail'],
  'has_archive' => true,
  'rewrite'    => ['with_front' => false, 'slug' => 'events'],
]);

$cpt->columns()->add([
    'date_start' => __('Date Start'),
    'date_end' => __('Date End'),
    'time' => __('Time'),
    'featured' => __('Featured'),
]);
$cpt->columns()->hide(['event_type', 'date', 'featured']);
$cpt->columns()->sortable([
    'date_start' => ['_cmb2_date_start', true],
    'date_end' => ['_cmb2_date_end', true]
]);
$cpt->columns()->populate('date_start', function($column, $post_id) {
  if ($val = get_post_meta($post_id, '_cmb2_date_start', true)) {
    echo date('Y-m-d', $val);
  } else {
    echo 'n/a';
  }
});
$cpt->columns()->populate('date_end', function($column, $post_id) {
  if ($val = get_post_meta($post_id, '_cmb2_date_end', true)) {
    echo date('Y-m-d', $val);
  } else {
    echo 'n/a';
  }
});
$cpt->columns()->populate('time', function($column, $post_id) {
  if ($val = get_post_meta($post_id, '_cmb2_date_start', true)) {
    echo date('g:ia', $val);
  } else {
    echo 'n/a';
  }
});
$cpt->columns()->populate('featured', function($column, $post_id) {
  echo (get_post_meta($post_id, '_cmb2_featured', true)) ? '&check;' : '';
});

// Add some admin filters
$cpt->filters(['event_series', 'event_type']);
$cpt->register();

/**
 * CMB2 custom fields
 */
add_filter( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );
function metaboxes() {
  $prefix = '_cmb2_';

  $event_info = new_cmb2_box([
    'id'            => 'event_info',
    'title'         => __( 'Event Info', 'cmb2' ),
    'object_types'  => ['event'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $event_info->add_field([
    'name'      => 'Venue',
    'id'        => $prefix . 'venue',
    'type'      => 'text',
  ]);
  $event_info->add_field([
    'name'      => 'Address',
    'id'        => $prefix . 'address',
    'type'      => 'address',
  ]);
  $event_info->add_field([
    'name'        => 'Event URL',
    'id'          => $prefix . 'event_url',
    'type'        => 'text',
    // 'description' => 'e.g. https://www.eventbrite.com/e/xxxx',
  ]);

  $event_when = new_cmb2_box([
    'id'            => 'event_when',
    'title'         => __( 'Event Dates', 'cmb2' ),
    'object_types'  => ['event'],
    'context'       => 'normal',
    'priority'      => 'high',
  ]);
  $event_when->add_field([
    'name'      => 'Start Date',
    'id'        => $prefix . 'date_start',
    'type'      => 'text_datetime_timestamp',
  ]);
  $event_when->add_field([
    'name'      => 'End Date',
    'id'        => $prefix . 'date_end',
    'type'      => 'text_datetime_timestamp',
  ]);
}

/**
 * Get Events
 */
function get_events($options=[]) {
  if (empty($options['num_posts'])) $options['num_posts'] = get_option('posts_per_page');
  if (!empty($_REQUEST['past_events'])) $options['past_events'] = 1; // support for AJAX requests
  $args = [
    'numberposts' => $options['num_posts'],
    'post_type'   => 'event',
    'meta_key'    => '_cmb2_date_start',
    'orderby'     => 'meta_value_num',
  ];

  // Make sure we're only pulling upcoming or past events
  $args['order'] = !empty($options['past_events']) ? 'DESC' : 'ASC';
  $args['meta_query'] = [
    [
      'key'     => '_cmb2_date_end',
      'value'   => current_time('timestamp'),
      'compare' => (!empty($options['past_events']) ? '<=' : '>')
    ],
  ];

  $event_posts = get_posts($args);
  if (!$event_posts) return false;
  $output = '';

  // Just return array of posts?
  if ($options['output'] == 'array') {
    return $event_posts;
  }

  // Display all matching events using article-event.php
  foreach ($event_posts as $event_post):
    ob_start();
    include(locate_template('templates/article-event.php'));
    $output .= ob_get_clean();
  endforeach;
  return $output;
}


/**
 * Daily cronjob to import new Eventbrite events
 */
add_action('wp', __NAMESPACE__ . '\\activate_eventbrite_import');
function activate_eventbrite_import() {
  if (!wp_next_scheduled('eventbrite_import')) {
    wp_schedule_event(current_time('timestamp'), 'twicedaily', 'eventbrite_import');
  }
}
add_action( 'eventbrite_import', __NAMESPACE__ . '\eventbrite_import' );

/**
 * Alter WP query for Event archive pages to sort by date_start
 */
function event_query($query){
  global $wp_the_query;
  if ($wp_the_query === $query && !is_admin() && is_post_type_archive('event')) {
    $query->set('orderby', 'meta_value_num');
    $query->set('meta_key', '_cmb2_date_start');
    $query->set('order', 'ASC');
  }
}
add_action('pre_get_posts', __NAMESPACE__ . '\\event_query');

/**
 * Get dates for event
 */
function get_dates($post) {
  if (empty($post->meta)) $post->meta = get_post_meta($post->ID);
  $output = '<div class="date">';
  if (!empty($post->meta['_cmb2_date_start'])) {
    $output .= '<time datetime="' . date('Y-m-d', $post->meta['_cmb2_date_start'][0]) . '">' . date('m/d/y', $post->meta['_cmb2_date_start'][0]) . '</time>';
  }
  if (!empty($post->meta['_cmb2_date_end']) && (empty($post->meta['_cmb2_date_start']) || date('Y-m-d', $post->meta['_cmb2_date_end'][0]) != date('Y-m-d', $post->meta['_cmb2_date_start'][0]))) {
    if (!empty($post->meta['_cmb2_date_start'])) $output .= ' – ';
    $output .= '<time datetime="' . date('Y-m-d', $post->meta['_cmb2_date_end'][0]) . '">' . date('m/d/y', $post->meta['_cmb2_date_end'][0]) . '</time>';
  }
  if (!empty($post->meta['_cmb2_time'])) {
    $output .= ' <span class="timespan">' . $post->meta['_cmb2_time'][0] . '</span>';
  }
  $output .= '</div>';
  return $output;
}
