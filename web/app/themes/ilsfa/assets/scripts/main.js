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
      map,
      popup,
      hoveredCluster,
      pointsLayer,
      mapPointsData,
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
    _initMaps();

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
      if (map) {
        map.resize();
      }
    });
  }

  // Init mapbox
  function _initMaps() {
    if($('#map').length) {
      // Mapbox GL will only work in ie11+
      // For ie9 and ie10, we will need to use the old school raster-tile mapbox
      useMapboxGl = mapboxgl.supported();

      // Get the correct CSS
      var mapboxCss = useMapboxGl ? 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.53.1/mapbox-gl.css' : 'https://api.mapbox.com/mapbox.js/v3.2.0/mapbox.css';
      $('head').append('<link href="'+mapboxCss+'" rel="stylesheet" />');

      // Get the correct JS, init maps on load
      var mapboxJs = useMapboxGl ? 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.53.1/mapbox-gl.js' : 'https://api.mapbox.com/mapbox.js/v3.2.0/mapbox.js';
      $.getScript(mapboxJs, function() {
        if (breakpoints.md) {
          _initMap(useMapboxGl, 11, [-87.6568088,41.8909229]);
        }
      });
    }
  }

  function _initMap(useGl, startZoom, startCenter) {

    var mapPoints = [];
    var $mapPoint = $('.map-point');

    // FOR MAPBOX GL (ie11+, and everything else)
    if (useGl) {
      if (typeof mapboxgl === 'undefined') { return; }
      mapboxgl.accessToken = mapbox_key;
      map = new mapboxgl.Map({
        container: 'map',
        scrollZoom: false,
        zoom: startZoom,
        center: startCenter,
        style: 'mapbox://styles/firebellydesign/cju8lp5lo62kn1gmbwlcaljkl',
        fadeDuration: 0
      });

      // Add nav
      var nav = new mapboxgl.NavigationControl();
      map.addControl(nav, 'top-left');

      // Inject svg icons for map controls
      $('#map .mapboxgl-ctrl-zoom-in').html('<svg class="icon icon-plus" aria-hidden="true" role="presentation"><use xlink:href="#icon-plus"/></svg>');
      $('#map .mapboxgl-ctrl-zoom-out').html('<svg class="icon icon-minus" aria-hidden="true" role="presentation"><use xlink:href="#icon-minus"/></svg>');

      // Just init single map?
      if ($mapPoint.length === 0) { return; }

      // Cull map points from DOM
      mapPoints = [];
      $mapPoint.each(function(){
        var $this = $(this);
        $this.addClass('mapped');
        if ($this.attr('data-lat') !== '') {
          mapPoints.push({
            'type': 'Feature',
            'geometry': {
              'type': 'Point',
              'coordinates': [parseFloat($this.attr('data-lng')), parseFloat($this.attr('data-lat'))]
            },
            'properties': {
              'title': $this.attr('data-title'),
              'url': $this.attr('data-url'),
              'id': $this.attr('data-id')
            }
          });
        }
      });

      // No map points with lat/lng found?
      if (mapPoints.length === 0) { return; }

      mapPointsData = {
        'type': 'FeatureCollection',
        'features': mapPoints
      };


      // Center map
      if (mapPoints.length > 1) {
        var bounds = new mapboxgl.LngLatBounds();
        mapPointsData.features.forEach(function(feature) {
          bounds.extend(feature.geometry.coordinates);
        });
        map.fitBounds(bounds, {padding: 100});
      } else {
        map.setCenter(mapPoints[0].geometry.coordinates);
      }

      map.on('load', function () {
        map.addSource('points', {
          'type': 'geojson',
          'data': mapPointsData,
          cluster: true,
          clusterMaxZoom: 14, // Max zoom to cluster points on
          clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
        });

        // Cluster layers
        map.addLayer({
          id: 'clusters',
          type: 'circle',
          source: 'points',
          filter: ['has', 'point_count'],
          paint: {
            'circle-color': ['case',
            ['boolean', ['feature-state', 'hover'], false],
            '#212126',
            '#E04E22',
            ],
            'circle-radius': 20,
          }
        });
        map.addLayer({
          id: 'cluster-count',
          type: 'symbol',
          source: 'points',
          filter: ['has', 'point_count'],
          layout: {
            'text-field': '{point_count_abbreviated}',
            'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
            'text-size': 16,
          },
          paint: {
            'text-color': '#ffffff'
          }
        });

        // Inspect a cluster on click
        map.on('click', 'clusters', function (e) {
          var features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
          var clusterId = features[0].properties.cluster_id;
          map.getSource('points').getClusterExpansionZoom(clusterId, function (err, zoom) {
            if (err) {
              return;
            }

            map.easeTo({
              center: features[0].geometry.coordinates,
              zoom: zoom
            });
          });
        });

        // Show clusters as clickable
        map.on('mouseenter', 'clusters', function () {
          map.getCanvas().style.cursor = 'pointer';
        });
        map.on('mouseleave', 'clusters', function () {
          map.getCanvas().style.cursor = '';
        });

        // Add points as a layer
        map.addLayer({
          'id': 'points',
          'type': 'symbol',
          'source': 'points',
          filter: ['!', ['has', 'point_count']],
          'layout': {
            // 'text-field': '{title}',
            'icon-image': 'icon-map-pin',
            // 'icon-allow-overlap': true
          }
        });

        // Add points as a hover layer with "map-pin-hover" icon
        map.addLayer({
          'id': 'points-hover',
          'type': 'symbol',
          'source': 'points',
          'layout': {
            'icon-image': 'icon-map-pin-hover',
            // 'text-field': '{title}',
            // 'icon-image': 'icon-map-pin-hover',
            // 'icon-allow-overlap': true
          },
          'filter': ['==', 'id', ''] // filter all out initially
        });

        // Create a popup, but don't add it to the map yet.
        popup = new mapboxgl.Popup({
          closeButton: false,
          offset: 20
        });

        // When clicking on map, check if clicking on a pin, and open URL if so
        map.on('click', function(e) {
          var features = map.queryRenderedFeatures(e.point, { layers: ['points', 'points-hover'] });
          if (features.length > 0) {
            var $mapPoint = $('.map-point[data-id=' + features[0].properties.id + ']');
            // Scroll down to map point card
            _scrollBody($mapPoint);
            // Show map point details
            if (!$mapPoint.hasClass('active')) {
              $mapPoint.find('a.toggler').trigger('click', e);
            }
            // window.open(features[0].properties.url, '_blank');
          }
        });

        // Map hover state handling
        map.on('mousemove', function(e) {
          var features = map.queryRenderedFeatures(e.point, { layers: ['points', 'points-hover'] });
          var clusters = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });

          if (clusters.length > 0) {
            if (hoveredCluster) {
              map.setFeatureState({source: 'points', id: hoveredCluster}, { hover: false });
            }
            hoveredCluster = clusters[0].id;
            map.setFeatureState({source: 'points', id: hoveredCluster}, { hover: true });
          } else {
            if (hoveredCluster) {
              map.setFeatureState({source: 'points', id: hoveredCluster}, { hover: false});
            }
            hoveredCluster =  null;
          }

          if (features.length) {
            var feature = features[0];

            // Cursor pointer = clickable
            map.getCanvas().style.cursor = 'pointer';

            // Hover state for pin: show pins in points-hover that match id
            map.setFilter('points-hover', ['==', 'id', feature.properties.id]);

            // Add "-hover" class to corresponding card
            // $('.cards li[data-id='+feature.properties.id+']').addClass('-hover');

            // Display a popup with the name of the county
            popup.setLngLat(feature.geometry.coordinates)
            .setText(feature.properties.title)
            .addTo(map);

          } else {
            // Clear out hover states for pins and features
            if (clusters.length===0) {
              // Only unset pointer if not hovering over a cluster
              map.getCanvas().style.cursor = '';
            }
            $('.cards li').removeClass('-hover');
            popup.remove();
            map.setFilter('points-hover', ['==', 'id', '']);
          }
        });

        // Highlight related pins on map when hovering over card
        $('body').on('mouseenter', '.map-point', function() {
          var id = $(this).attr('data-id');
          map.setFilter('points-hover', ['==', 'id', id]);
        }).on('mouseleave', '.map-point', function() {
          map.setFilter('points-hover', ['==', 'id', '']);
        });

        // Sticky map
        // var map_waypoint = new Waypoint.Sticky({
        //   element: $('.map-container')[0],
        //   wrapper: '<div class="map-sticky-wrapper" />',
        //   // context: $('#map-sticky-parent')[0]
        //   // handler: function(direction) {
        //   //   _resize();
        //   // },
        //   offset: headerOffset
        // });

      });

    } else {

    // Old school Mapbox js (ie9,10)

    //   if (typeof L.mapbox === 'undefined') { return; }

    //   L.mapbox.accessToken = mapbox_key;

    //   // Convert given zoom and centers to something readable by raster mapbox js
    //   var zoom = Math.ceil(startZoom)+1;
    //   var center = [startCenter[1],startCenter[0]];

    //   // Init map
    //   map = new L.mapbox.Map('map', null, { zoomControl: false }).setView(center, zoom);

    //   var rasterTileLayer = L.mapbox.styleLayer('mapbox://styles/tsquared1017/cj8c5fqt57w1q2slaz7ca5t7a').addTo(map);

    //   // Add loaded class when the raster tile layer is up and runnin
    //   rasterTileLayer.on('load', function(e) {
    //     $('#map').addClass('loaded');
    //   });

    //   // disable drag and zoom handlers
    //   map.scrollWheelZoom.disable();

    //   // Add mapbox nav controls (styling overrided in _maps.scss)
    //   new L.Control.Zoom({ position: 'topright' }).addTo(map);

    //   // Just init single map?
    //   if ($mapPoint.length === 0) { return; }

    //   mapPoints = [];
    //   $mapPoint.each(function(){
    //     var $this = $(this);
    //     $this.addClass('mapped');
    //     mapPoints.push({
    //       'type': 'Feature',
    //       'geometry': {
    //         'type': 'Point',
    //         'coordinates': [parseFloat($this.attr('data-lng')), parseFloat($this.attr('data-lat'))]
    //       },
    //       'properties': {
    //         'title': $this.attr('data-title'),
    //         'url': $this.attr('data-url'),
    //         'enabled': !$this.hasClass('disabled'),
    //         'icon' : {
    //           'iconUrl': '/assets/svgs/map-pin.svg',
    //           'iconSize': [30, 30],
    //           'iconAnchor': [15, 15],
    //         },
    //       },
    //     });
    //   });

    //   pointsLayer = L.mapbox.featureLayer(null, {id: 'points', 'type': 'symbol'}).addTo(map);

    //   // Give layers proper icons
    //   pointsLayer.on('layeradd', function(e) {
    //     var marker = e.layer,
    //       feature = marker.feature;
    //       marker.setIcon(L.icon(feature.properties.icon));
    //       marker.unbindPopup();
    //   });

    //   // Add geoJson
    //   pointsLayer.setGeoJSON(mapPoints);

    //   // Add click event
    //   pointsLayer.on('click', function(e) {
    //     if(e.layer.feature.properties.enabled) {
    //       location.href = e.layer.feature.properties.url;
    //     }
    //   });

    //   map.setView(pointsLayer.getBounds().getCenter());

    }

  }

  // function _addMapPoints() {
  //   var $mapPoints = $('.map-point:not(.mapped)');
  //   if ($mapPoints.length) {

  //     // Cull map points from DOM
  //     $mapPoints.each(function(){
  //       var $this = $(this);
  //       $this.addClass('mapped');
  //       if ($this.attr('data-lat') !== '') {
  //         mapPointsData.features.push({
  //           'type': 'Feature',
  //           'geometry': {
  //             'type': 'Point',
  //             'coordinates': [parseFloat($this.attr('data-lng')), parseFloat($this.attr('data-lat'))]
  //           },
  //           'properties': {
  //             'title': $this.attr('data-title'),
  //             'url': $this.attr('data-url')
  //           }
  //         });
  //       }
  //     });

  //     map.getSource('points').setData(mapPointsData);
  //     // Center
  //     var bounds = new mapboxgl.LngLatBounds();
  //     mapPointsData.features.forEach(function(feature) {
  //       bounds.extend(feature.geometry.coordinates);
  //     });
  //     map.fitBounds(bounds, {padding: 150});
  //     // Resize map
  //     map.resize();

  //   }
  // }

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

  // Adjust jumpto nav based on headerOffset
  function _setJumpToPosition() {
    if ($jumpTo.length) {
      if ($jumpTo.is('.stuck')) {
        $jumpTo.css('top', headerOffset);
      } else {
        $jumpTo.css('top', '');
      }
    }
  }

  // Set headerOffset var used for jumptonav placement and scrollbody calculations
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

  // Load more buttons for organizations lists
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
              org_filter: $load_more.attr('data-org-filter'),
              org_type: $load_more.attr('data-org-type')
          },
          success: function(data) {
            var $data = $(data);

            if (loadingTimer) {
              clearTimeout(loadingTimer);
            }
            // Append new posts to more_container
            $more_container.append($data).removeClass('loading');

            // Increase page in data attribute
            $load_more.attr('data-page-at', page+1);

            // Tell masonry we appended some items
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
