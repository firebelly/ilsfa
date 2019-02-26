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
      headerOffset,
      currentDomain = document.location.protocol + '//' + document.location.hostname;

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

    // Selects that update the URL when changed
    $('select.jumpselect').on('change', function() {
      window.location = this.options[this.selectedIndex].value;
    });

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

    // Track GA event for outbound links
    $('a[href^="http"]:not([href*="' + currentDomain + '"])').on('click', function(e) {
        if (typeof ga === 'undefined') {
          return;
        }
        e.preventDefault();
        ga('send', 'event', 'outbound', 'click', this.href, {
          'transport': 'beacon',
          'hitCallback': function() {
            document.location = this.href;
          }
        });
    });

    // Null links
    $('body').on('click', 'a[href="#"]', function(e) {
      e.preventDefault();
    });

    // Smoothscroll links
    $('a.smoothscroll,.smoothscroll a').click(function(e) {
      var href = $(this).attr('href').replace(/(https?:)?\/\//,'');
      var anchor = href.replace(/^[^#]/,'');
      // Is this a link + an anchor?
      if (href !== anchor) {
        // Is this anchor on another page? Just return normal link behavior and location.hash will be handled on load
        if (location.pathname !== href.replace(anchor,'')) {
          return;
        }
      }
      e.preventDefault();
      var el = $(anchor);
      // Support for oldskool anchor links
      if ($('a[name=' + anchor.replace('#','') + ']').length) {
        el = $('a[name=' + anchor.replace('#','') + ']');
      }
      if (el.length) {
        _scrollBody(el[0]);
      }
    });

    _initNav();
    _initSearch();
    _initJumpTo();
    _initForms();
    _initLoadMore();
    _initOrganizations();

    // Scroll down to hash after page load
    $(window).load(function() {
      if (window.location.hash) {
        _scrollBody(window.location.hash);
      }
    });

  } // end init()

  // Mobile expand/collapse behavior for Organization cards
  function _initOrganizations() {
    $('.organizations-listing li').find('a.toggler,h3').on('click', function(e) {
      e.preventDefault();
      var $parent = $(this).parents('li.item:first');
      $parent.toggleClass('active');
      $('.compact-grid').masonry();
    });
  }

  // Slugify a string, e.g. "The Foo Bar" -> "the-foo-bar"
  function _slugify(text) {
    text = text.replace(/[^a-zA-Z0-9\-\s]/g,"");
    text = text.toLowerCase();
    text = text.replace(/\s/g,'-');
    return text;
  }

  // Populate jumpto nav links and add behavior to scrollbody on click
  function _initJumpTo() {
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

  // Scroll body to an element using Velocity
  function _scrollBody(element, duration, delay) {
    if (typeof duration === 'undefined') {
      duration = 500;
    }
    if (typeof delay === 'undefined') {
      delay = 0;
    }

    // Sending '#hash' instead of jquery element?
    if (typeof element === 'string') {
      var anchor = element;
      // Support for a[name=foo] anchors
      element = $(anchor+',a[name="'+anchor.replace('#','')+'"]');
      if (element.length === 0) {
        return;
      }
    }

    // Hide nav
    _hideMobileNav();

    // Set flag that we're animating body to avoid uncollapsing the nav from a jumpto link
    scrollToBodyAnimating = true;
    // Add a bit of breaking room to offset
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
          // Set animating flag to false for scroll behavior to kick back in
          scrollToBodyAnimating = false;
        }, 150);
      }
    }, 'easeOutSine');
  }

  // Search toggler in nav
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

  function _initLoadMore() {
    $document.on('click', '.load-more a', function(e) {
      e.preventDefault();
      var $load_more = $(this).closest('.load-more');
      var post_type = $load_more.attr('data-post-type') ? $load_more.attr('data-post-type') : 'news';
      var page = parseInt($load_more.attr('data-page-at'));
      var per_page = parseInt($load_more.attr('data-per-page'));
      var $more_container = $load_more.parents('[data-load-more-parent]').find('[data-load-more-container]');
      loadingTimer = setTimeout(function() {
        $more_container.addClass('loading');
      }, 500);

      $.ajax({
          url: wp_ajax_url,
          method: 'post',
          data: {
              action: 'load_more_organizations',
              post_type: post_type,
              page: page+1,
              per_page: per_page,
              org_sort: $load_more.attr('data-org-sort'),
              org_type: $load_more.attr('data-org-type')
          },
          success: function(data) {
            var $data = $(data);

            if (loadingTimer) {
              clearTimeout(loadingTimer);
            }
            $more_container.append($data).removeClass('loading');

            $load_more.attr('data-page-at', page+1);
            $more_container.masonry('appended', $data, true);

            // Hide load more if last page
            if ($load_more.attr('data-total-pages') <= page + 1) {
              $load_more.addClass('hidden');
            }
          }
      });
    });
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
