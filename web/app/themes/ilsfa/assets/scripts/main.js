// ILSFA - Firebelly 2019
/*jshint latedef:false*/

// Good Design for Good Reason for Good Namespace
var ILSFA = (function($) {

  var breakpoints = [],
      breakpointClasses = ['xl','lg','nav','md','sm','xs'],
      $body,
      $window,
      $document,
      $siteHeader,
      $main,
      $wpAdminBar,
      $jumpTo,
      delayedResizeTimer,
      headerOffset,
      loadingTimer;

  function _init() {
    // Cache some common DOM queries
    $body = $(document.body);
    $window = $(window);
    $document = $(document);
    $siteHeader = $('header.site-header');
    $jumpTo = $('.jumpto');
    $wpAdminBar = $('#wpadminbar');
    $main = $('main.main');

    // DOM is loaded
    $body.addClass('loaded');

    // Set breakpoint vars
    _resize();

    // Collapse desktop nav on scroll down, uncollapse scrolling up
    _initNavCollapse();

    // Fit them vids!
    $('main').fitVids();

    // Compact grids = masonry (tried using CSS grid but performance was terrible)
    if ($('.compact-grid').length) {
      $('.compact-grid').masonry({
        itemSelector: '.item',
        gutter: 16,
        horizontalOrder: true,
        percentPosition: true
      });
    }

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

    _initNav();
    _initSearch();
    _initJumpTo();
    _initForms();
    // _initLoadMore();

    // Scroll down to hash afer page load
    $(window).load(function() {
      var hash = window.location.hash.replace('#','');
      if (window.location.hash) {
        if ($('[data-jumpto-hash="'+hash+'"]').length) {
          _scrollBody($('[data-jumpto-hash="'+hash+'"]').get(0));
        } else if ($(window.location.hash).length) {
          _scrollBody($(window.location.hash));
        }
      }
    });

  } // end init()

  function _slugify(text) {
    text = text.replace(/[^a-zA-Z0-9\-\s]/g,"");
    text = text.toLowerCase();
    text = text.replace(/\s/g,'-');
    return text;
  }

  function _initJumpTo() {
    // Jump To nav
    if ($jumpTo.length) {
      var title;
      // Clear out dummy li for spacing
      $jumpTo.find('li').remove();
      jumpToLinks = [];

      // Get page-content headers
      $('main .page-content h2').each(function() {
        title = $(this).attr('data-jumpto') || $(this).text();
        $(this).attr('data-jumpto-hash', _slugify(title));
        jumpToLinks.push({title: title, el: this});
      });

      // Find manually added [data-jumpto] areas
      $('[data-jumpto]').each(function() {
        title = $(this).attr('data-jumpto');
        jumpToLinks.push({title: title, el: this});
        $(this).attr('data-jumpto-hash', _slugify(title));
      });

      // Any jump links found?
      if (jumpToLinks.length) {
        $jumpTo.addClass('loaded').find('.jumpto-title').on('click', function(e) {
          $jumpTo.toggleClass('-active');
        });
        // Build jumpto nav with various links found
        $.each(jumpToLinks, function(i,el) {
          $('<li>'+el.title+'</li>').appendTo($jumpTo.find('ul')).on('click', function(e) {
            e.preventDefault();
            _scrollBody(el.el);
          }).hide().velocity('transition.slideLeftIn', { easing: 'easeOutSine', duration: 200, delay: (i-1) * 50, display: 'inline-block' });
        });

        // Sticky jumpto
        var waypoint = new Waypoint.Sticky({
          element: $jumpTo[0],
          wrapper: '<div class="jumpto-sticky-wrapper" />',
          handler: function(direction) {
            _resize();
          },
          offset: 80
        });
      } else {
        // Just remove element if no jumpto links to add
        $jumpTo.remove();
      }
    }
  }

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
      }).each(function() {
        $(this).parents('.input-wrap,.oneField').toggleClass('filled', $(this).val()!=='');
      });
    });

    // Handle submit of form
    $('.formassembly-form form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        $.ajax({
          url: wp_ajax_url,
          data: $form.serialize() + '&action=formassembly_submit&formAction=' + $form.attr('action'),
          method: 'POST',
          dataType: 'json'
        }).done(function(data) {
          console.log(data);
        }).fail(function() {
          console.log('fail!');
        });
    });
  }

  function _scrollBody(element, duration, delay) {
    if (typeof duration === 'undefined') {
      duration = 500;
    }
    if (typeof delay === 'undefined') {
      delay = 0;
    }
    var offset = 20 + headerOffset;
    if ($('.jumpto.stuck').length) {
      offset = offset + $('.jumpto.stuck').outerHeight();
    }
    $(element).velocity('scroll', {
      duration: duration,
      delay: delay,
      offset: -offset
    }, 'easeOutSine');
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

    // We need a separate element to style for interim dividers
    $('<li class="divider-line"></li>').insertBefore('.site-nav li.divider');
  }

  function _toggleMobileNav() {
    $body.toggleClass('menu-open');
    _resize();
  }

  function _hideMobileNav() {
    $body.removeClass('menu-open');
    _resize();
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
    // Check breakpoint indicator in DOM ( :after { content } is controlled by CSS media queries )
    var breakpointIndicatorString = window.getComputedStyle(
      document.querySelector('#breakpoint-indicator'), ':after'
    ).getPropertyValue('content').replace(/['"]/g, '');
    breakpoints = {};
    // Check for breakpoint with `if (breakpoint['md'])`
    for (var i = 0; i < breakpointClasses.length; i++) {
      breakpoints[breakpointClasses[i]] = (breakpointIndicatorString === breakpointClasses[i] || (i>0 && breakpoints[breakpointClasses[i-1]]));
    }

    _setHeaderOffset();
    _setJumpToPosition();

    // // Slower, more resource intensive resize events
    // clearTimeout(delayedResizeTimer);
    // delayedResizeTimer = setTimeout(_delayedResize, 150);
  }
  // Called periodically as window is resized
  function _delayedResize() {
  }

  function _setJumpToPosition() {
    if ($jumpTo.length) {
      if ($jumpTo.is('.stuck')) {
        $jumpTo.css('top', headerOffset);
      } else {
        $jumpTo.css('top', '');
      }
    }
  }

  function _setHeaderOffset() {
    headerOffset = $siteHeader.outerHeight();
    if ($wpAdminBar.length && $wpAdminBar.css('position') === 'fixed') {
      headerOffset = headerOffset + $wpAdminBar.outerHeight();
    }
  }

  // Collapse desktop nav as you scroll down, but remove collapsed state if you scroll up past threshold
  function _initNavCollapse() {
    var didScroll;
    var lastScrollTop = 0;
    var delta = 100;
    var navbarHeight = $siteHeader.outerHeight();

    $(window).scroll(function(event){
      didScroll = true;
    });

    setInterval(function() {
      // Update navbarHeight
      navbarHeight = $siteHeader.outerHeight();
      if (didScroll) {
        // Add body.has-scrolled for various CSS (mostly mobile when logged into WP)
        $body.addClass('has-scrolled');
        hasScrolled();
        didScroll = false;
      }
    }, 250);

    function hasScrolled() {
      var st = $window.scrollTop();

      // Remove body.has-scrolled if scrolled to top
      if (st < 40) {
        $body.removeClass('has-scrolled');
      }

      // Make sure they scroll more than delta
      if(Math.abs(lastScrollTop - st) <= delta) {
        return;
      }

      // If they scrolled down and are past the navbar, add class .nav-up.
      // This is necessary so you never see what is "behind" the navbar.
      if (st > lastScrollTop && st > navbarHeight) {
        // Scroll Down
        $siteHeader.removeClass('nav-down').addClass('collapsed');
        _resize();
      } else {
        // Scroll Up
        if(st + $window.height() < $document.height()) {
          $siteHeader.removeClass('collapsed').addClass('nav-down');
          _resize();
        }
      }

      lastScrollTop = st;
    }
  }

  // Public functions
  return {
    init: _init,
    resize: _resize,
    scrollBody: function(section, duration, delay) {
      _scrollBody(section, duration, delay);
    }
  };

})(jQuery);

// Fire up the mothership
jQuery(document).ready(ILSFA.init);

// Zig-zag the mothership
jQuery(window).resize(ILSFA.resize);
