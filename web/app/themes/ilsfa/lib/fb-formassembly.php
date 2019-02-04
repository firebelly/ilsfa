<?php
namespace Firebelly\FormAssembly;

/**
 * Get FormAssembly form
 */
function get_form($id) {
  $client = new \GuzzleHttp\Client();
  $formassemblyDomain = get_formassembly_domain();
  try {
    if (!empty($_GET['tfa_next'])) {
      $res = $client->request('GET', 'https://'.$formassemblyDomain.'/rest'.$_GET['tfa_next']);
    } else {
      $res = $client->request('GET', 'https://'.$formassemblyDomain.'/rest/forms/view/'.$id);
    }
    $data = $res->getBody()->getContents();
    // Strip out everything but the <form> element
    $data = preg_replace('/(.*)<form/s','<form',$data);
    $data = preg_replace('/<\/form>(.*)/s','</form>',$data);
    // Remove <br>'s
    $data = preg_replace('/<br( \/)?>/','',$data);
    // Remove <p>'s
    $data = preg_replace('/(<p>)|(<\/p>)/','',$data);
    // Remove newlines
    $data = trim(preg_replace('/\s+/', ' ', $data));

    return '<div class="formassembly-form" data-id="'.$id.'">'.$data.'<div class="form-response"></div></div>';

  } catch (\Exception $e) {
    return 'Form '.$id.' not found.';
  }
}

/**
 * Get formassembly_domain from Site Options or revert to base domain
 */
function get_formassembly_domain() {
  $formassemblyDomain = \Firebelly\SiteOptions\get_option('formassembly_domain');
  if (empty($formassemblyDomain)) {
    $formassemblyDomain = 'www.tfaforms.com';
  }
  return $formassemblyDomain;
}

/**
 * AJAX form submits
 */

function formassembly_submit() {
  // https://elevateenergy.tfaforms.net/responses/processor?faIframeUniqueId=1eppzxigdh&hostURL=http://ilsfa.localhost:3000/programs/distributed-generation/
  $postVars = $_POST;
  unset($postVars['action']);
  unset($postVars['formAction']);
  $client = new \GuzzleHttp\Client();
  try {
    $res = $client->post($_POST['formAction'], [
      'form_params' => $postVars,
    ]);
    $response = [
      'success' => 1,
      'response' => $res->getBody()->getContents(),
    ];

  } catch (\GuzzleHttp\Exception\ClientException $e) {

    print_r($e->getResponse()->getBody()->getContents());

  } catch (\Exception $e) {
    $response = [
      'success' => 0,
      'message' => 'Error submitting form: '.$e->getMessage(),
    ];
  }
  wp_send_json($response);
}
add_action('wp_ajax_formassembly_submit', __NAMESPACE__ . '\\formassembly_submit');
add_action('wp_ajax_nopriv_formassembly_submit', __NAMESPACE__ . '\\formassembly_submit');

/**
 * FormAssembly shortcode
 */
add_shortcode('formassembly', __NAMESPACE__ . '\shortcode_formassembly');
function shortcode_formassembly($atts) {
  $atts = shortcode_atts([
    'id' => '',
    'formid' => '',
    'iframe' => '',
  ], $atts, 'formassembly');

  // Get domain from Site Options
  $formassemblyDomain = get_formassembly_domain();

  // Support for wp-formassembly plugin's syntax [formassembly formid=xxx]
  if (!empty($atts['formid'])) {
    $atts['id'] = $atts['formid'];
  }

  // Spit out iframe?
  if (!empty($atts['iframe'])) {
    return '<div class="formassembly-iframe"><iframe width="100%" frameborder="0" src="https://'.$formassemblyDomain.'/forms/view/'.$atts['id'].'"></iframe><script src="//elevateenergy.tfaforms.net/js/iframe_resize_helper.js"></script></div>';
  }
  return get_form($atts['id']);
}
