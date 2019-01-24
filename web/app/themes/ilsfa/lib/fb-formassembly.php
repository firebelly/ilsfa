<?php
namespace Firebelly\FormAssembly;

/**
 * Get FormAssembly form
 */
function get_form($id) {
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
    'iframe' => '',
  ], $atts, 'formassembly');
  if ($atts['iframe']) {
    return '<div class="formassembly-iframe"><iframe width="100%" frameborder="0" src="https://elevateenergy.tfaforms.net/forms/view/'.$atts['id'].'"></iframe><script src="//elevateenergy.tfaforms.net/js/iframe_resize_helper.js"></script></div>';
  }
  return get_form($atts['id']);
}
