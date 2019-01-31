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
  // https://elevateenergy.tfaforms.net/responses/processor?faIframeUniqueId=1eppzxigdh&hostURL=http://ilsfa.localhost:3000/programs/distributed-generation/

  $client = new \GuzzleHttp\Client();
  try {
    $res = $client->request('POST', $_POST['formAction'], [], [
      'form_params' => [
        'tfa_1' => $_POST['tfa_1'],
        'tfa_2' => $_POST['tfa_2'],
        'tfa_3' => $_POST['tfa_3'],
        'tfa_4' => $_POST['tfa_4'],
        'tfa_16' => $_POST['tfa_16'],
        'tfa_dbCounters' => $_POST['tfa_dbCounters'],
        'tfa_dbFormId' => $_POST['tfa_dbFormId'],
        'tfa_dbResponseId' => $_POST['tfa_dbResponseId'],
        'tfa_dbControl' => $_POST['tfa_dbControl'],
        'tfa_dbTimeStarted' => $_POST['tfa_dbTimeStarted'],
        'tfa_dbVersionId' => $_POST['tfa_dbVersionId'],
        'tfa_switchedoff' => $_POST['tfa_switchedoff'],
      ]
    ]);
print_r($res); exit;
    $response = [
      'success' => 1,
      'data' => print_r($res, 1)
    ];

  } catch (\GuzzleHttp\Exception\ClientException $e) {

    print_r($e->getResponse()->getBody()->getContents());

  } catch (\Exception $e) {
print_r($e->getMessage()); exit;
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

  // Support for wp-formassembly plugin's syntax
  $atts['id'] = $atts['formid'];

  if ($atts['iframe']) {
    return '<div class="formassembly-iframe"><iframe width="100%" frameborder="0" src="https://elevateenergy.tfaforms.net/forms/view/'.$atts['id'].'"></iframe><script src="//elevateenergy.tfaforms.net/js/iframe_resize_helper.js"></script></div>';
  }
  return get_form($atts['id']);
}
