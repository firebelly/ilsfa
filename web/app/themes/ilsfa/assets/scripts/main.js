// FBSage - Firebelly 2015
/*jshint latedef:false*/

// Good Design for Good Reason for Good Namespace
var FBSage = (function($) {

  var breakpoint_small = false,
      breakpoint_medium = false,
      breakpoint_large = false,
      breakpoint_array = [480,1000,1200],
      $body,
      $document,
      $siteHeader,
      $main,
      loadingTimer;

  function _init() {
    // Cache some common DOM queries
    $document = $(document);
    $body = $(document.body);
    $siteHeader = $('header.site-header');
    $main = $('main.main');

    // DOM is loaded
    $body.addClass('loaded');

    // Set screen size vars
    _resize();

    // Fit them vids!
    $('main').fitVids();

    // Compact grid js resizing (mini-masonry)
    if ($('.compact-grid').length) {
      _resizeGrids();
      $(window).on('resize', FBSage.resizeGrids);
    }

    _initNav();
    _initSearch();
    // _initLoadMore();

    // Esc handlers
    $(document).keyup(function(e) {
      if (e.keyCode === 27) {
        _hideSearch();
        _hideMobileNav();
      }
    });

    // Null links
    $('body').on('click', 'a[href="#"]', function(e) {
      e.preventDefault();
    });

    // Smoothscroll links
    $('a.smoothscroll').click(function(e) {
      e.preventDefault();
      var href = $(this).attr('href');
      _scrollBody($(href));
    });

    // Scroll down to hash afer page load
    $(window).load(function() {
      if (window.location.hash) {
        _scrollBody($(window.location.hash));
      }
    });

    _initForms();

  } // end init()

  // Form behavior w/ support for FormAssembly
  function _initForms() {
    $('form').each(function() {
      var $form = $(this);

      // FormAssembly multiple radio/checkbox in vertical list
      $form.find('.choices.vertical').each(function() {
        var $this = $(this);
        $this.find('input[type=checkbox]').before('<div class="control-indicator"></div>');
      });
      // Massage some various FA fields to get our styles
      $('.formassembly-form .oneField:not([role=group])').addClass('input-wrap');
      // Make required fields HTML5 required
      $('.formassembly-form input.required').attr('required',true);
      // Handle submit of form
      $('.formassembly-form form').on('submit', function(e) {
        return false;
        // e.preventDefault();
        // var $form = $(this);
        // $.ajax({
        //   url: $form.attr('action'),
        //   data: $form.serialize(),
        //   crossDomain: 1,
        //   method: 'POST',
        //   dataType: 'json'
        // }).done(function(data) {
        //     console.log(data);
        // }).fail(function() {
        //   console.log('fail!');
        // });
    });
      // Add focused + filled classes for styling
      $form.find('input,textarea').on('focus', function() {
        $(this).parents('.input-wrap,.oneField').addClass('focused');
      }).on('blur', function() {
        var $this = $(this);
        $this.parents('.input-wrap,.oneField').removeClass('focused');
        if($this.val()!=='') {
          $this.parents('.input-wrap,.oneField').addClass('filled');
        } else {
          $this.parents('.input-wrap,.oneField').removeClass('filled');
        }
      });
    });
  }

  function _scrollBody(element, duration, delay) {
    if ($('#wpadminbar').length) {
      wpOffset = $('#wpadminbar').height();
    } else {
      wpOffset = 0;
    }
    element.velocity("scroll", {
      duration: duration,
      delay: delay,
      offset: -wpOffset
    }, "easeOutSine");
  }

  function _initSearch() {
    $('a.search-toggle').on('click', function (e) {
      e.preventDefault();
      $body.toggleClass('search-open');
      if ($body.hasClass('search-open')) {
        $('.search-field:first').focus();
      }
    });
    $('a.search-close').on('click', function(e) {
      e.preventDefault();
      _hideSearch();
    });
  }

  function _hideSearch() {
    $body.removeClass('search-open');
    _hideMobileNav();
  }

  // Handles main nav
  function _initNav() {
    // Toggle mobile nav
    $('a.menu-toggle').on('click', _toggleMobileNav);

    // Waypoints
    var waypoint = new Waypoint({
      element: $main[0],
      handler: function(direction) {
        if (direction === 'down') {
          $siteHeader.attr('class', 'site-header scroll-down');
        }
        else if (direction === 'up') {
          $siteHeader.attr('class', 'site-header scroll-up');
        }
      }
    });
  }

  function _toggleMobileNav() {
    $body.toggleClass('menu-open');
  }

  function _hideMobileNav() {
    $body.removeClass('menu-open');
  }

  function _initLoadMore() {
    $document.on('click', '.load-more a', function(e) {
      e.preventDefault();
      var $load_more = $(this).closest('.load-more');
      var post_type = $load_more.attr('data-post-type') ? $load_more.attr('data-post-type') : 'news';
      var page = parseInt($load_more.attr('data-page-at'));
      var per_page = parseInt($load_more.attr('data-per-page'));
      var category = $load_more.attr('data-category');
      var more_container = $load_more.parents('section,main').find('.load-more-container');
      loadingTimer = setTimeout(function() { more_container.addClass('loading'); }, 500);

      $.ajax({
          url: wp_ajax_url,
          method: 'post',
          data: {
              action: 'load_more_posts',
              post_type: post_type,
              page: page+1,
              per_page: per_page,
              category: category
          },
          success: function(data) {
            var $data = $(data);
            if (loadingTimer) { clearTimeout(loadingTimer); }
            more_container.append($data).removeClass('loading');
            if (breakpoint_medium) {
              more_container.masonry('appended', $data, true);
            }
            $load_more.attr('data-page-at', page+1);

            // Hide load more if last page
            if ($load_more.attr('data-total-pages') <= page + 1) {
                $load_more.addClass('hide');
            }
          }
      });
    });
  }

  // Track ajax pages in Analytics
  function _trackPage() {
    if (typeof ga !== 'undefined') { ga('send', 'pageview', document.location.href); }
  }

  // Track events in Analytics
  function _trackEvent(category, action) {
    if (typeof ga !== 'undefined') { ga('send', 'event', category, action); }
  }

  // Called in quick succession as window is resized
  function _resize() {
    screenWidth = document.documentElement.clientWidth;
    breakpoint_small = (screenWidth > breakpoint_array[0]);
    breakpoint_medium = (screenWidth > breakpoint_array[1]);
    breakpoint_large = (screenWidth > breakpoint_array[2]);
  }

  // Compact grid js resizing (mini-masonry)
  function _resizeGrids() {
    var $grid = $('.compact-grid');
    if ($grid.length === 0) {
      return;
    }
    var rowHeight = parseFloat($grid.css('grid-auto-rows'));
    var rowGap = parseFloat($grid.css('grid-row-gap'));
    $grid.css({
      'grid-auto-rows': 'auto',
      'align-items': 'self-start'
    });
    $grid.find('li').each(function(){
      var t = Math.ceil((this.clientHeight + rowGap) / (rowHeight + rowGap));
      this.style.gridRowEnd = 'span ' + t;
    });
    $grid.attr('style','');
  }

  // Public functions
  return {
    init: _init,
    resize: _resize,
    resizeGrids: _resizeGrids,
    scrollBody: function(section, duration, delay) {
      _scrollBody(section, duration, delay);
    }
  };

})(jQuery);

// Fire up the mothership
jQuery(document).ready(FBSage.init);

// Zig-zag the mothership
jQuery(window).resize(FBSage.resize);
