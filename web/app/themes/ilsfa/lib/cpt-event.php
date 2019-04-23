<?php
/**
 * Event post type
 */

namespace Firebelly\PostTypes\Event;
use PostTypes\PostType; // see https://github.com/jjgrainger/PostTypes
use PostTypes\Taxonomy;

$cpt = new PostType(['name' => 'event', 'slug' => 'event'], [
  'taxonomies' => ['topic', 'region'],
  'supports'   => ['title', 'editor', 'thumbnail'],
  'has_archive' => true,
  'rewrite'    => ['with_front' => false, 'slug' => 'events'],
]);

$topic = new Taxonomy('topic');
$topic->register();

$cpt->columns()->add([
    'date_start' => __('Date Start'),
    // 'date_end' => __('Date End'),
]);
$cpt->columns()->hide(['workshop_type', 'date', 'featured']);
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

// Add some admin filters
$cpt->filters(['topic', 'region']);
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
    'name'      => 'Lat',
    'id'        => $prefix . 'lat',
    'type'      => 'text_small',
    'save_field'  => false,
    'attributes'  => array(
      'readonly' => 'readonly',
      'disabled' => 'disabled',
    ),
  ]);
  $event_info->add_field([
    'name'      => 'Lng',
    'id'        => $prefix . 'lng',
    'type'      => 'text_small',
    'save_field'  => false,
    'attributes'  => array(
      'readonly' => 'readonly',
      'disabled' => 'disabled',
    ),
  ]);
  $event_info->add_field([
    'name'      => 'Salesforce ID',
    'id'        => $prefix . 'salesforce_id',
    'type'      => 'text',
    'save_field'  => false,
    'attributes'  => array(
      'readonly' => 'readonly',
      'disabled' => 'disabled',
    ),
  ]);

  // $event_info->add_field([
  //   'name'        => 'Event URL',
  //   'id'          => $prefix . 'event_url',
  //   'type'        => 'text',
  //   'description' => 'e.g. If set, events will link out to external URL',
  // ]);

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
function get_events($opts=[]) {
  if (empty($opts['num_posts'])) $opts['num_posts'] = get_option('posts_per_page');
  if (!empty($_REQUEST['past_events'])) $opts['past_events'] = 1; // support for AJAX requests
  $args = [
    'numberposts' => $opts['num_posts'],
    'post_type'   => 'event',
    'meta_key'    => '_cmb2_date_start',
    'orderby'     => 'meta_value_num',
  ];

  // Make sure we're only pulling upcoming or past events
  $args['order'] = !empty($opts['past_events']) ? 'DESC' : 'ASC';
  $args['meta_query'] = [
    [
      'key'     => '_cmb2_date_end',
      'value'   => current_time('timestamp'),
      'compare' => (!empty($opts['past_events']) ? '<=' : '>')
    ],
  ];

  $event_posts = get_posts($args);
  if (!$event_posts) {
    return false;
  }

  // Just return array of posts?
  if (!empty($opts['output']) && $opts['output'] == 'array') {
    return $event_posts;
  }

  // Display all matching posts using article-{$post_type}.php
  $output = '';
  foreach ($event_posts as $event_post):
    ob_start();
    include(locate_template('templates/article-event.php'));
    $output .= ob_get_clean();
  endforeach;
  return $output;
}


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
  $output .= '</div>';
  return $output;
}

/**
 * Update lookup table for post geodata, if post_id isn't sent, all posts are updates/inserted into wp_fb_posts_lat_lng
 */
function update_posts_lat_lng($post_id='') {
  global $wpdb;
  // Make sure we have lat/lng cache tables set up
  check_lat_lng_tables();
  $event_cache = [];
  $post_id_sql = empty($post_id) ? '' : ' AND post_id='.(int)$post_id;
  $event_posts = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE meta_key IN ('_cmb2_lat','_cmb2_lng') AND meta_value != '' {$post_id_sql} ORDER BY post_id");
  if ($event_posts) {
    foreach ($event_posts as $event) {
      $event_cache[$event->post_id][$event->meta_key] = $event->meta_value;
    }
    foreach ($event_cache as $event_id=>$arr) {
      $cnt = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM wp_fb_posts_lat_lng WHERE post_id=%d", $event_id) );
      if ($cnt>0) {
        $wpdb->query( $wpdb->prepare("UPDATE wp_fb_posts_lat_lng SET lat=%s, lng=%s WHERE post_id=%d", $arr['_cmb2_lat'], $arr['_cmb2_lng'], $event_id) );
      } else {
        $wpdb->query( $wpdb->prepare("INSERT INTO wp_fb_posts_lat_lng (post_id,lat,lng) VALUES (%d,%s,%s)", $event_id, $arr['_cmb2_lat'], $arr['_cmb2_lng']) );
      }
    }
  }
}

/**
 * Geocode address and save in custom fields
 */
function geocode_address($post_id, $internal='') {
  // Called internally for an existing post (e.g. importing events)
  if (!empty($internal)) {
    $address = get_post_meta($post_id, '_cmb2_address', true);
  } else {
    // Use POST in case this is a new post
    if (empty($_POST['_cmb2_address'])) return;
    $address = wp_parse_args($_POST['_cmb2_address'], [
      'address-1' => '',
      'address-2' => '',
      'city'      => '',
      'state'     => '',
      'zip'       => '',
     ]);
  }

  if (!empty($address['address-1'])):
    $address_combined = $address['address-1'] . ' ' . $address['address-2'] . ' ' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'];
    $request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode($address_combined) . '&key=' . getenv('GOOGLE_API_KEY');
    $xml = simplexml_load_file($request_url);
    $status = $xml->status;
    if(strcmp($status, 'OK')===0):
        $lat = $xml->result->geometry->location->lat;
        $lng = $xml->result->geometry->location->lng;
        update_post_meta($post_id, '_cmb2_lat', (string)$lat);
        update_post_meta($post_id, '_cmb2_lng', (string)$lng);
        // Update wp_fb_posts_lat_lng cache table
        update_posts_lat_lng($post_id);
    endif;
  endif;
}
add_action('save_post_event', __NAMESPACE__ . '\\geocode_address', 20, 1);

/**
 * Check for lat/lng cache tables for geocoding lookups and zipcode distance
 */
function check_lat_lng_tables() {
  global $wpdb;
  if (!$wpdb->get_var("SHOW TABLES LIKE 'wp_fb_posts_lat_lng'")) {
    // If table not present, create tables + populate zip data
    require(__DIR__.'/data/events-lat-lng-tables-zip-data.php');
  }
}

/**
 * Show Edit Page link when viewing Events archive
 */
function custom_admin_bar() {
  global $wp_admin_bar, $post;

  // If workshop archive/taxonomy page, add Edit Page link to edit Upcoming Workshops post
  if (!is_admin() && (is_post_type_archive('event'))) {
    $event_post = get_page_by_title('Events');
    $wp_admin_bar->add_menu( array(
      'parent' => false,
      'id' => 'edit',
      'title' => 'Edit Page',
      'href' => get_edit_post_link($event_post->ID),
    ));
  }
}
add_action('wp_before_admin_bar_render', __NAMESPACE__ . '\custom_admin_bar');

/**
 * Daily cronjob to import new Salesforce events
 */
add_action('wp', __NAMESPACE__ . '\\activate_salesforce_import');
function activate_salesforce_import() {
  if (!wp_next_scheduled('salesforce_import')) {
    wp_schedule_event(current_time('timestamp'), 'twicedaily', 'salesforce_import');
  }
}
add_action( 'salesforce_import', __NAMESPACE__ . '\salesforce_import' );

/**
 * Handle AJAX response from Salesforce Import form
 */
add_action('wp_ajax_salesforce_import', __NAMESPACE__ . '\salesforce_import');
function salesforce_import() {
  require_once 'salesforce-importer.php';

  $importer = new \SalesforceImporter;
  $log = $importer->do_import();

  if (\Firebelly\Ajax\is_ajax()) {
    wp_send_json($log);
  }
}

/**
 * Show link to Salesforce Import page
 */
add_action('admin_menu', __NAMESPACE__ . '\salesforce_import_admin_menu');
function salesforce_import_admin_menu() {
  add_submenu_page('edit.php?post_type=event', 'Salesforce Import', 'Salesforce Import', 'manage_options', 'salesforce-import', __NAMESPACE__ . '\salesforce_import_admin_form');
}

/**
 * Basic Salesforce Import admin page
 */
function salesforce_import_admin_form() {
?>
  <div class="wrap">
    <h2>Salesforce Importer</h2>
    <p>This runs every night as an automated cronjob, but you can also run it manually here.</p>
    <form method="post" id="salesforce-import-form" enctype="multipart/form-data" action="<?= admin_url('admin-ajax.php') ?>">
      <div class="progress-bar"><div class="progress-done"></div></div>
      <div class="log-output"></div>
      <input type="hidden" name="action" value="salesforce_import">
      <p class="submit"><input type="submit" class="button" id="salesforce-import-submit" name="submit" value="Run Importer"></p>
    </form>
  </div>
<?php
}