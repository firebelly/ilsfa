<?php
namespace Firebelly\FormAssembly;

/**
 * Get FormAssembly form
 */
function get_form($id) {
  // return '<iframe width="100%" height="600px" src="https://elevateenergy.tfaforms.net/forms/view/'.$id.'"></iframe>';
  $client = new \GuzzleHttp\Client();
  try {
    $res = $client->request('GET', 'https://elevateenergy.tfaforms.net/rest/forms/view/'.$id);
    $data = $res->getBody()->getContents();
    // Strip everything before "<!-- FORM: BODY SECTION -->"
    $data = preg_replace('/((.*)<\!\-\- FORM: BODY SECTION \-\->)/s','',$data);
    // Remove <br>'s
    $data = preg_replace('/<br( \/)?>/','',$data);
    // Remove <p>'s
    $data = preg_replace('/(<p>)|(<\/p>)/','',$data);
    // Remove newlines
    $data = trim(preg_replace('/\s+/', ' ', $data));

    return '<div class="formassembly-form" data-id="'.$id.'">'.$data.'</div>';

  } catch (\Exception $e) {
    return 'Form '.$id.' not found.';
  }
}

/**
 * AJAX form submits
 */
function formassembly_submit() {
}
add_action( 'wp_ajax_formassembly_submit', __NAMESPACE__ . '\\formassembly_submit' );
add_action( 'wp_ajax_nopriv_formassembly_submit', __NAMESPACE__ . '\\formassembly_submit' );


/**
 * FormAssembly shortcode
 */
add_shortcode('formassembly', __NAMESPACE__ . '\shortcode_formassembly');
function shortcode_formassembly($atts) {
  $atts = shortcode_atts([
    'id' => '',
  ], $atts, 'formassembly');
  return get_form($atts['id']);
}
