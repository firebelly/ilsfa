<?php
/**
 * Salesforce Importer - ILSFA - Firebelly 2019
 */

class SalesforceImporter {
  private $log = [
    'error' => [],
    'notice' => [],
    'stats' => [],
    'num_skipped' => 0,
    'num_updated' => 0,
    'num_imported' => 0,
  ];
  private $taxonomy_cache = [];
  private $prefix = '_cmb2_';
  private $salesforce;

  function do_import() {
    $page = 1;
    $time_start = microtime(true);

    // Connect to Salesforce API
    try {
      $salesforce = new \SalesforceRestAPI\SalesforceAPI(
        getenv('SALESFORCE_URL'),
        '45.0',
        getenv('SALESFORCE_CLIENT_KEY'),
        getenv('SALESFORCE_CLIENT_SECRET')
      );
      $salesforce->login(
        getenv('SALESFORCE_USERNAME'),
        getenv('SALESFORCE_PASSWORD'),
        getenv('SALESFORCE_SECURITY_TOKEN')
      );

    } catch(Exception $e) {
      $this->log['error'][] = 'Error connecting to Salesforce to fetch events: ' . $e->getMessage();
      wp_mail( 'developer@firebellydesign.com', 'ILSFA error', 'Error fetching events: ' . $e->getMessage() );
    }

    // Pull events from Salesforce
    try {

      $response = $salesforce->searchSOQL("SELECT id, CreatedDate, LastModifiedDate, Name, State__c, City__c, Zip_Code__c, Event_Date__c, Event_Details__c, Event_Name_Grassroot__c, Event_Topic__c, Region__c, Event_Organization2__r.Name FROM ISEIF_and_EI2_Events__c WHERE Event_Date__c != null AND Event_Name_Grassroot__c != null AND is_Community__c = true AND (Event_Status__c = 'Event Scheduled' OR Event_Status__c = 'Event Complete')", true);

      $this->log['notice'][] = 'Salesforce API: '.$response['totalSize'].' total events found';

      // Process events
      $this->process_events($response['records']);

    } catch ( Exception $e ) {
      $this->log['error'][] = 'Error fetching events: ' . $e->getMessage();
      wp_mail( 'developer@firebellydesign.com', 'ILSFA error', 'Error fetching events: ' . $e->getMessage() );
    }

    // Build summary notices
    if ($this->log['num_skipped'])
      $this->log['notice'][] = sprintf("Skipped %s entries", $this->log['num_skipped']);

    if ($this->log['num_updated'])
      $this->log['notice'][] = sprintf("Updated %s entries", $this->log['num_updated']);

    if ($this->log['num_imported'])
      $this->log['notice'][] = sprintf("Imported %s entries", $this->log['num_imported']);

    $exec_time = microtime(true) - $time_start;
    $this->log['stats']['exec_time'] = sprintf("%.2f", $exec_time);

    // Build HTML version of log for js and email
    $html_log = '';
    if (!empty($this->log['notice'])) {
      $html_log .= '<h3>Notices:</h3><ul><li>' . join('</li><li>', $this->log['notice']) . '</li></ul>';
    }
    if (!empty($this->log['error'])) {
      $html_log .= '<h3>Errors:</h3><ul><li>' . join('</li><li>', $this->log['error']) . '</li></ul>';
    }
    $html_log .= '<p><b>Import took ' . $this->log['stats']['exec_time'] . ' seconds.</b></p>';
    $this->log['html_log'] = $html_log;

    // Send email report?
    $salesforce_notifications_email = \Firebelly\SiteOptions\get_option('salesforce_notifications_email');
    if ($this->log['num_imported'] > 0 && !empty($salesforce_notifications_email)) {
      add_filter('wp_mail_content_type', ['SalesforceImporter', 'set_html_email']);
      wp_mail($salesforce_notifications_email, 'ILSFA Salesforce Import '.date('Y-m-d'), $this->log['html_log']);
      remove_filter('wp_mail_content_type', ['SalesforceImporter', 'set_html_email']);
    }

    return $this->log;
  }

  /**
   * Temporarily set email type as html
   */
  public static function set_html_email() {
    return 'text/html';
  }

  /**
   * Custom function to get or create a term for a taxonomy and return term_id
   */
  private function get_term($name, $taxonomy) {
    $term_id = '';
    if (empty($this->taxonomy_cache[$taxonomy])) {
      $this->taxonomy_cache[$taxonomy] = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => 0]);
    }
    foreach ($this->taxonomy_cache[$taxonomy] as $term) {
      if (empty($term_id) && $term->name == $name) {
        $term_id = $term->term_id;
      }
    }
    if (empty($term_id)) {
      $new_term = wp_insert_term($name, $taxonomy);
      if (!is_wp_error($new_term)) {
        $term_id = $new_term['term_id'];
        // Refresh taxonomy cache
        $this->taxonomy_cache[$taxonomy] = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => 0]);
      } else {
        $this->log['notice'][] = sprintf('Error inserting term "%s" in taxonomy "%s": %s', $name, $taxonomy, $new_term->get_error_message());
      }
    }
    return $term_id;
  }

  /**
   * Process a batch of Salesforce API events
   */
  function process_events($events) {
    global $wpdb;
    foreach ($events as $event ) {
      // Skip events with no description
      if ($event['Event_Details__c']=='') {
        continue;
      }
      $update_notices = [];

      $event_id = $event_workshop_series = null;
      $event_title = $event['Event_Name_Grassroot__c'];

      $event_exists = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", $this->prefix.'salesforce_id', $event['Id'] ));
      if (!$event_exists) {
        // Get timestamp of date event created
        $publishedAt = strtotime($event['CreatedDate']);

        // Insert workshop post
        $new_post = [
          'post_status' => 'publish',
          'post_type' => 'event',
          'post_author' => 1,
          'post_date' => date('Y-m-d H:i:s', $publishedAt),
          'post_date_gmt' => date('Y-m-d H:i:s', $publishedAt),
          'post_title' => $event_title,
          'post_content' => $event['Event_Details__c'],
        ];
        $event_id = wp_insert_post($new_post);

        if ($event_id) {
          // Set topics
          if (!empty($event['Event_Topic__c'])) {
            $cat_ids = [];
            foreach(explode(';', $event['Event_Topic__c']) as $topic) {
              $cat_ids[] = $this->get_term($topic, 'topic');
            }
            if (!empty($cat_ids)) {
              wp_set_object_terms($event_id, $cat_ids, 'topic');
            }
          }

          // Set region
          if (!empty($event['Region__c'])) {
            $cat_ids = [$this->get_term(trim($event['Region__c']), 'region')];
            wp_set_object_terms($event_id, $cat_ids, 'region');
          }

          $this->log['num_imported']++;
          $this->log['notice'][] = '<h3>New event #'.$event_id.' created for <a target="_blank" href="' . \Firebelly\Utils\cronjob_edit_link($event_id) . '">'.$event_title.'</a></h3>';
        }
      } else {
        $event_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", $this->prefix.'salesforce_id', $event['Id']));
        $this->log['num_updated']++;
      }

      if ($event_id) {
        // Set (or update if existing event) various custom fields
        update_post_meta($event_id, $this->prefix.'salesforce_id', $event['Id'] );
        update_post_meta($event_id, $this->prefix.'salesforce_last_modified', strtotime($event['LastModifiedDate']));
        update_post_meta($event_id, $this->prefix.'date_start', strtotime($event['Event_Date__c']));
        update_post_meta($event_id, $this->prefix.'date_end', strtotime($event['Event_Date__c']));
        if (!empty($event['Event_Organization2__r']['Name'])) {
          update_post_meta($event_id, $this->prefix.'venue', $event['Event_Organization2__r']['Name']);
        }

        // Set address meta fields
        $address = [
          'address-1' => $event['Name'],
          'address-2' => '',
          'city' => $event['City__c'],
          'state' => $event['State__c'],
          'zip' => $event['Zip_Code__c'],
        ];
        update_post_meta($event_id, $this->prefix.'address', $address);
        \Firebelly\PostTypes\Event\geocode_address($event_id, 1);

        if (!empty($update_notices)) {
          $this->log['notice'][] = 'Event #'.$event_id.' <a target="_blank" href="' . \Firebelly\Utils\cronjob_edit_link($event_id) . '">'.$event_title.'</a> updated';
        }
      }
    }
  }
}
