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
      $wpAdminBar,
      $jumpTo,
      scrollToBodyAnimating = false,
      headerOffset;

  function _init() {
    // Cache some common DOM queries
    $body = $(document.body);
    $window = $(window);
    $document = $(document);
    $siteHeader = $('header.site-header');
    $jumpTo = $('.jumpto');
    $wpAdminBar = $('#wpadminbar');

    // DOM is loaded
    $body.addClass('loaded');

    // Tooltipz
    $('.tooltip,.tooltip a').tooltipster({
      side: 'bottom',
      theme: 'ilsfa',
      distance: 0,
      interactive: true,
      trigger: 'custom',
      triggerOpen: {
        mouseenter: true,
        tap: true
      },
      triggerClose: {
        mouseleave: true,
        scroll: true,
        tap: true
      }
    });

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
    $('a.smoothscroll,.smoothscroll a').click(function(e) {
      var href = $(this).attr('href');
      var anchor = href.replace(/([^#]+#)/,'');
      if (href !== anchor) {
        return;
      }
      e.preventDefault();
      var el = $(href) ? $(href) : $('a[name='+href.replace('#','')+']');
      console.log(href, el);
      _scrollBody($(href));
    });

    _initNav();
    _initSearch();
    _initJumpTo();
    _initForms();

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
        $jumpTo.addClass('loaded').find('.jumpto-title,.jumpto-toggle').on('click', function(e) {
          e.preventDefault();
          var dir = $jumpTo.hasClass('-active') ? 'slideUp' : 'slideDown';
          $jumpTo.toggleClass('-active');
          $jumpTo.find('ul').velocity(dir, {
            duration: 150
          });
        });
        // Build jumpto nav with various links found
        $.each(jumpToLinks, function(i,el) {
          $('<li>'+el.title+'</li>').appendTo($jumpTo.find('ul')).on('click', function(e) {
            e.preventDefault();
            _scrollBody(el.el);
            if (!breakpoints.md) {
              $jumpTo.find('.jumpto-toggle').trigger('click', e);
            }
          }).hide().velocity('transition.slideLeftIn', {
            easing: 'easeOutSine',
            duration: 200,
            delay: (i-1) * 50,
            display: 'inline-block'
          });
        });

        // Sticky jumpto
        var waypoint = new Waypoint.Sticky({
          element: $jumpTo[0],
          wrapper: '<div class="jumpto-sticky-wrapper" />',
          handler: function(direction) {
            _resize();
          },
          offset: headerOffset
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

    $('.formassembly-form').each(function() {
      var $formWrapper = $(this);
      var $form = $formWrapper.find('form');

      // Add elapsedTime hidden input
      var $elapsedTime = $('<input type="hidden" value="" name="tfa_dbElapsedJsTime">').appendTo($form);
      var formTimeStart = Math.floor(new Date().getTime() / 1000);

      // FormAssembly multiple radio/checkbox in vertical list
      // $form.find('.choices.vertical').each(function() {
      //   var $this = $(this);
      //   $this.find('input[type=checkbox]').before('<div class="control-indicator"></div>');
      // });

      // Massage some various FA fields to get our styles
      $('.formassembly-form .oneField:not([role=group])').addClass('input-wrap');
      // Make required fields HTML5 required
      $('.formassembly-form input.required').attr('required',true);

      // Handle submit of form
      $form.on('submit', function(e) {
        e.preventDefault();

        if ($form.hasClass('working')) {
          return false;
        }

        // Update elapsedTime var for FA
        $elapsedTime.val(Math.floor(new Date().getTime() / 1000) - formTimeStart);

        $form.addClass('working');
        $.ajax({
          url: wp_ajax_url,
          data: $form.serialize() + '&action=formassembly_submit&formAction=' + $form.attr('action'),
          method: 'POST',
          dataType: 'json'
        })
        .done(function(data) {
          if (data.success === 1) {
            $formWrapper.find('.wFormContainer').velocity('slideUp');
            $formWrapper.addClass('success').find('.form-response').removeClass('error').html('<h3>Thank you!</h3><p>Your entry was submitted successfully.</p>');
          } else {
            $formWrapper.find('.form-response').addClass('error').html('<p>Error: ' + data.message + '</p>');
          }
        })
        .fail(function() {
          $formWrapper.find('.form-response').addClass('error').html('<p>There was an error submitting the form. Please try again.</p>');
        })
        .always(function() {
          $form.removeClass('working');
        });
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
    // Set flag that we're animating body to avoid uncollapsing the nav from a jumpto link
    scrollToBodyAnimating = true;
    var offset = 20 + headerOffset;
    if ($('.jumpto').length) {
      offset = offset + $('.jumpto').outerHeight();
    }
    $(element).velocity('scroll', {
      duration: duration,
      delay: delay,
      offset: -offset,
      complete: function(els) {
        setTimeout(function() {
          scrollToBodyAnimating = false;
        }, 150);
      }
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
    var scrollUpDelta = 150;
    var scrollDownDelta = 100;

    $(window).scroll(function(event){
      didScroll = true;
    });

    setInterval(function() {
      if (didScroll) {
        // Add body.has-scrolled for various CSS (mostly mobile when logged into WP)
        $body.addClass('has-scrolled');
        hasScrolled();
        didScroll = false;
      }
    }, 150);

    function hasScrolled() {
      var st = $window.scrollTop();

      // Remove body.has-scrolled if scrolled to top
      if (st < scrollDownDelta) {
        $body.removeClass('has-scrolled');
      }

      // Make sure they scrolled more than scrollUpDelta
      if(Math.abs(lastScrollTop - st) <= scrollDownDelta) {
        return;
      }

      if (st > lastScrollTop) {
        // Scrolling Down
        $siteHeader.removeClass('nav-down').addClass('collapsed');
        _resize();
      } else {
        // Scrolling Up
        if(!scrollToBodyAnimating && (st <= scrollUpDelta || Math.abs(lastScrollTop - st) >= scrollUpDelta)) {
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
