/*
 * Firebelly custom admin behavior
 */

// Good design for good reason for good namespace
var FB_admin = (function($) {

  var _updateTimer,
  _submitDivHeight;

  function _init() {

    // Support for cmd-s to trigger save in WP
    $(window).bind('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.which==83 && $('#publish,#submit').length){
        e.preventDefault();
        $('#publish,#submit').click();
      }
    });

    // Hack the update from bottom plugin to show it earlier
    _submitDivHeight = $('#submitdiv').height();
    $(window).scroll(function(){
      clearTimeout(_updateTimer);
      _updateTimer = setTimeout(function() {
        $('#updatefrombottom').toggle( $(window).scrollTop() > _submitDivHeight );
      }, 150);
    });

    // Custom tabbed groups in CMB2 (see https://gist.github.com/natebeaty/39672b0d96eedf621dadf24c8ddc9a32)
    $('body').on('click', '.tabs-nav a', function(e) {
      e.preventDefault();
      var $tabs = $(this).closest('.cmb2-tabs');
      $tabs.find('.tabs-nav li,.tab-content').removeClass('current');
      $(this).parent('li').addClass('current');
      $tabs.find($(this).attr('href').replace('#', '.')).addClass('current');
    });
  }

  // Salesforce Importer
  $('#salesforce-import-form').on('submit', function(e) {
    e.preventDefault();
    window.scrollTo(0,0);
    $('#salesforce-import-submit').prop('disabled', true).val('Please wait...');

    // Show spinner + Working text after submitting
    var $log = $('#salesforce-import-form .log-output')
    $log.html('<p><img src="/wp/wp-admin/images/spinner-2x.gif" style="display:inline-block; width:20px; height:auto;"> Working... (be patient, can take a while if there are new events)</p>');

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: $(this).serialize(),
      success: function(data) {
        // Display log messages from import script
        $log.html(data.html_log)
        $('#salesforce-import-submit').prop('disabled', false).val('Run Importer');
      }
    });

  });

  // Public functions
  return {
    init: _init
  };

})(jQuery);

// Fire up the mothership
jQuery(document).ready(FB_admin.init);
