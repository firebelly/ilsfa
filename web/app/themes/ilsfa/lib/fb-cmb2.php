<?php
/**
 * CMB2 custom fields
 */

namespace Firebelly\CMB2;

/**
 * Get post options for CMB2 select
 */
function get_post_options($query_args) {
  $args = wp_parse_args($query_args, array(
    'post_type'   => 'post',
    'numberposts' => -1,
    'post_parent' => 0,
  ));
  $posts = get_posts($args);
  return wp_list_pluck($posts, 'post_title', 'ID');
}
/**
 * Example how to use:
 * in cmb2 field: `'options_cb' => '\Firebelly\CMB2\get_people'`
 * then add function below
 */

// function get_people() {
//     return get_post_options(['post_type' => 'person']);
// }


/**
 * Exclude metabox on specific slugs
 * @param  object $cmb CMB2 object
 * @return bool        True/false whether to show the metabox
 */
function exclude_for_slugs($cmb) {
  $slugs_to_exclude = $cmb->prop('exclude_slugs', []);
  $post_slug = get_post_field('post_name', $cmb->object_id());
  $excluded = in_array($post_slug, $slugs_to_exclude, true);
  return !$excluded;
}

/**
 * Show metabox on specific slugs
 * @param  object $cmb CMB2 object
 * @return bool        True/false whether to show the metabox
 */
function show_for_slugs($cmb) {
  $slugs_to_show = $cmb->prop('show_slugs', []);
  $post_slug = get_post_field('post_name', $cmb->object_id());
  $show = in_array($post_slug, $slugs_to_show, true);
  return $show;
}


/**
 * Returns options markup for a state select field.
 * @param  mixed $value Selected/saved state
 * @return string       html string containing all state options
 */
function cmb2_get_state_options( $value = false ) {
  $state_list = array( ''=>'Choose State...', 'AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California','CO'=>'Colorado','CT'=>'Connecticut','DE'=>'Delaware','DC'=>'District Of Columbia','FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland','MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi','MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire','NJ'=>'New Jersey','NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio','OK'=>'Oklahoma','OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina','SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia','WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming' );

  $state_options = '';
  foreach ( $state_list as $abrev => $state ) {
    $state_options .= '<option value="'. $abrev .'" '. selected( $value, $abrev, false ) .'>'. $state .'</option>';
  }

  return $state_options;
}

/**
 * Render Address Field
 */
function cmb2_render_address_field_callback( $field_object, $value, $object_id, $object_type, $field_type_object ) {

  // make sure we specify each part of the value we need.
  $value = wp_parse_args( $value, array(
    'address-1' => '',
    'address-2' => '',
    'city'      => '',
    'state'     => '',
    'zip'       => '',
  ) );

  ?>
  <div><p><label for="<?php echo $field_type_object->_id( '_address_1' ); ?>">Address 1</label></p>
    <?php echo $field_type_object->input( array(
      'name'  => $field_type_object->_name( '[address-1]' ),
      'id'    => $field_type_object->_id( '_address_1' ),
      'value' => $value['address-1'],
    ) ); ?>
  </div>
  <div><p><label for="<?php echo $field_type_object->_id( '_address_2' ); ?>'">Address 2</label></p>
    <?php echo $field_type_object->input( array(
      'name'  => $field_type_object->_name( '[address-2]' ),
      'id'    => $field_type_object->_id( '_address_2' ),
      'value' => $value['address-2'],
    ) ); ?>
  </div>
  <div class="alignleft"><p><label for="<?php echo $field_type_object->_id( '_city' ); ?>'">City</label></p>
    <?php echo $field_type_object->input( array(
      'class' => 'cmb_text_small',
      'name'  => $field_type_object->_name( '[city]' ),
      'id'    => $field_type_object->_id( '_city' ),
      'value' => $value['city'],
    ) ); ?>
  </div>
  <div class="alignleft"><p><label for="<?php echo $field_type_object->_id( '_state' ); ?>'">State</label></p>
    <?php echo $field_type_object->select( array(
      'name'    => $field_type_object->_name( '[state]' ),
      'id'      => $field_type_object->_id( '_state' ),
      'options' => cmb2_get_state_options( $value['state'] )
    ) ); ?>
  </div>
  <div class="alignleft"><p><label for="<?php echo $field_type_object->_id( '_zip' ); ?>'">Zip</label></p>
    <?php echo $field_type_object->input( array(
      'class' => 'cmb_text_small',
      'name'  => $field_type_object->_name( '[zip]' ),
      'id'    => $field_type_object->_id( '_zip' ),
      'value' => $value['zip'],
    ) ); ?>
  </div>
  <?php
  echo $field_type_object->_desc( true );

}
add_filter( 'cmb2_render_address', __NAMESPACE__ . '\\cmb2_render_address_field_callback', 10, 5 );

/**
 * Only return default value if we don't have a post ID (in the 'post' query variable)
 * (from https://github.com/CMB2/CMB2/wiki/Tips-&-Tricks#setting-a-default-value-for-a-checkbox)
 *
 * @param  bool  $default On/Off (true/false)
 * @return mixed          Returns true or '', the blank default
 */
function set_checkbox_default_for_new_post($default) {
  return isset($_GET['post'] ) ? '' : ($default ? (string) $default : '');
}
