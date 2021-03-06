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
      headerOffset = 0,
      jumpToOffset = 0,
      $map,
      $mapContainer,
      map,
      popup,
      hoveredCluster,
      pointsLayer,
      mapPointsData,
      mapboxGlSupported,
      currentDomain = document.location.protocol + '//' + document.location.hostname;

  function _init() {
    // Cache some common DOM queries
    $body = $(document.body);
    $window = $(window);
    $document = $(document);
    $siteHeader = $('header.site-header');
    $jumpTo = $('.jumpto');
    $wpAdminBar = $('#wpadminbar');
    $map = $('#map');
    $mapContainer = $('.map-container');
    mapboxGlSupported = mapboxgl.supported();

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
    // (disabled to use GTM for this)
    // $('a[href^="http"]:not([href*="' + currentDomain + '"])').on('click', function(e) {
    //   // Just return if Analytics isn't initiated
    //   if (typeof ga === 'undefined') {
    //     return;
    //   }
    //   // .. otherwise cancel click & track outbound link
    //   e.preventDefault();
    //   var $this = $(this);
    //   gtag('event', 'click', {
    //     'event_category': 'outbound',
    //     'event_label': $this.attr('href'),
    //     'transport_type': 'beacon',
    //     'event_callback': function(){
    //       // Open in new tab/window if specified
    //       if ($this.attr('target')) {
    //         window.open($this.attr('href'), $this.attr('target'));
    //       } else {
    //         window.location = $this.attr('href');
    //       }
    //     }
    //   });
    // });

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
    $document.on('click', '.organizations-listing .toggler', function(e) {
      e.preventDefault();
      var $parent = $(this).parents('li.item:first');
      $parent.toggleClass('active');

      // Update map size and offsets
      _setMapSize();

      // Fly to point on map
      if ($parent.hasClass('active') && !$parent.hasClass('no-coords')) {
        _mapFlyTo([$parent.attr('data-lng'), $parent.attr('data-lat')], $parent.attr('data-title'));
      }

      // Update waypoint offsets
      Waypoint.refreshAll();
    });
  }

  // Init mapbox
  function _initMaps() {
    if (breakpoints.md && $map.length) {
      // Mapbox GL will only work in ie11+
      // For ie9 and ie10, we will need to use the old school raster-tile mapbox

      // Get the correct CSS
      var mapboxCss = mapboxGlSupported ? 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.53.1/mapbox-gl.css' : 'https://api.mapbox.com/mapbox.js/v3.2.0/mapbox.css';
      $('head').append('<link href="'+mapboxCss+'" rel="stylesheet" />');

      // Get the correct JS, init maps on load
      var mapboxJs = mapboxGlSupported ? 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.53.1/mapbox-gl.js' : 'https://api.mapbox.com/mapbox.js/v3.2.0/mapbox.js';
      $.getScript(mapboxJs, function() {
        _initMap([-87.6568088,41.8909229], 11);
      });
    }
  }

  function _initMap(startCenter, startZoom) {

    var $mapPoints = $('.map-point');
    mapPointsData = {
      'type': 'FeatureCollection',
      'features': []
    };

    // FOR MAPBOX GL (ie11+, and everything else)
    if (mapboxGlSupported) {
      if (typeof mapboxgl === 'undefined') { return; }
      mapboxgl.accessToken = mapbox_key;
      map = new mapboxgl.Map({
        container: 'map',
        scrollZoom: false,
        zoom: startZoom,
        center: startCenter,
        style: mapbox_style,
        fadeDuration: 0
      });

      // Add nav
      var nav = new mapboxgl.NavigationControl();
      map.addControl(nav, 'top-left');

      // Inject svg icons for map controls
      $('#map .mapboxgl-ctrl-zoom-in').html('<svg class="icon icon-plus" aria-hidden="true" role="presentation"><use xlink:href="#icon-plus"/></svg>');
      $('#map .mapboxgl-ctrl-zoom-out').html('<svg class="icon icon-minus" aria-hidden="true" role="presentation"><use xlink:href="#icon-minus"/></svg>');

      // Cull map points from DOM
      $mapPoints.each(function(){
        var $this = $(this);
        $this.addClass('mapped');
        if ($this.attr('data-lat')) {
          mapPointsData.features.push({
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
        } else {
          $this.addClass('no-coords');
        }
      });

      // No map points with lat/lng found?
      if (mapPointsData.features.length === 0) { return; }

      map.on('load', function () {
        map.addSource('points', {
          'type': 'geojson',
          'data': mapPointsData,
          'cluster': true,
          'clusterMaxZoom': 14, // Max zoom to cluster points on
          'clusterRadius': 35 // Radius of each cluster when clustering points (defaults to 50)
        });

        // Cluster layers
        map.addLayer({
          'id': 'clusters',
          'type': 'circle',
          'source': 'points',
          'filter': ['has', 'point_count'],
          'paint': {
            'circle-color': ['case',
              ['boolean', ['feature-state', 'hover'], false],
              '#212126',
              '#E04E22',
            ],
            'circle-radius': 20,
          }
        });
        map.addLayer({
          'id': 'cluster-count',
          'type': 'symbol',
          'source': 'points',
          'filter': ['has', 'point_count'],
          'layout': {
            'text-field': '{point_count_abbreviated}',
            'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
            'text-size': 16,
          },
          'paint': {
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
              'center': features[0].geometry.coordinates,
              'zoom': zoom
            });
          });
        });

        // Show clusters as clickable with pointer cursor
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
          'filter': ['!', ['has', 'point_count']],
          'layout': {
            'icon-image': 'icon-map-pin',
            'icon-allow-overlap': true
          }
        });

        // Add points as a hover layer with "map-pin-hover" icon
        map.addLayer({
          'id': 'points-hover',
          'type': 'symbol',
          'source': 'points',
          'layout': {
            'icon-image': 'icon-map-pin-hover',
            'icon-allow-overlap': true
          },
          'filter': ['==', 'id', ''] // filter all out initially
        });

        // Create a popup, but don't add it to the map yet.
        popup = new mapboxgl.Popup({
          'closeButton': false,
          'offset': 20
        });

        // When clicking on map, check if clicking on a pin, and open URL if so
        map.on('click', function(e) {
          var features = map.queryRenderedFeatures(e.point, { layers: ['points', 'points-hover'] });
          if (features.length > 0) {
            _mapPointClick(features[0].properties.id, e);
          }
        });

        // Map hover state handling
        map.on('mousemove', function(e) {
          var features = map.queryRenderedFeatures(e.point, { layers: ['points', 'points-hover'] });
          var clusters = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });

          // Any clusters being hovered over?
          if (clusters.length > 0) {
            // Clear out any previously hovered over clusters
            if (hoveredCluster) {
              map.setFeatureState({source: 'points', id: hoveredCluster}, { hover: false });
            }
            // Set current cluster as hovered element
            hoveredCluster = clusters[0].id;
            // Set property hovered:true to trigger styles set above
            map.setFeatureState({source: 'points', id: hoveredCluster}, { hover: true });
          } else {
            // Clear out any previously hovered over clusters
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
            $('.cards li[data-id='+feature.properties.id+']').addClass('-hover');

            // Display popup with card title
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
        $('body').on('mouseenter', '.map-point:not(.no-coords)', function() {
          var id = $(this).attr('data-id');
          map.setFilter('points-hover', ['==', 'id', id]);
        }).on('mouseleave', '.map-point', function() {
          map.setFilter('points-hover', ['==', 'id', '']);
        });

        // Center map
        if (mapPointsData.features.length > 1) {
          var bounds = new mapboxgl.LngLatBounds();
          mapPointsData.features.forEach(function(feature) {
            bounds.extend(feature.geometry.coordinates);
          });
          map.fitBounds(bounds, {padding: 100});
        } else {
          map.setCenter(mapPointsData.features[0].geometry.coordinates);
        }

      }); // end map onload

    } else {

      // Old school Mapbox.js (ie9,10)

      if (typeof L === 'undefined') { return; }

      L.mapbox.accessToken = mapbox_key;

      // Convert given zoom and centers to something readable by raster mapbox js
      var zoom = Math.ceil(startZoom)+1;
      var center = [startCenter[1], startCenter[0]];

      // Init map
      map = new L.mapbox.Map('map', null, { zoomControl: false }).setView(center, zoom);

      // Init popup object
      popup = L.popup({offset: new L.Point(0, -10)});

      // Set style
      var rasterTileLayer = L.mapbox.styleLayer(mapbox_style).addTo(map);

      // Disable drag and zoom handlers
      map.scrollWheelZoom.disable();

      // Add mapbox nav controls (styling overrided in _maps.scss)
      new L.Control.Zoom({ position: 'topleft' }).addTo(map);

      _setMapSize();

      if ($mapPoints.length === 0) { return; }

      // Add points to map
      pointsLayer = L.mapbox.featureLayer(null, {id: 'points', 'type': 'symbol'}).addTo(map);

      // Give layers proper icons
      pointsLayer.on('layeradd', function(e) {
        var marker = e.layer,
            feature = marker.feature;
        marker.setIcon(L.icon(feature.properties.icon));
        marker.unbindPopup();
      });

      // Add click event
      pointsLayer.on('click', function(e) {
        _mapPointClick(e.layer.feature.properties.id, e);
      });

      // Add all map points
      _addMapPoints();

    }

    // Sticky map
    var mapTopWaypoint = new Waypoint.Sticky({
      element: $mapContainer[0],
      wrapper: '<div class="map-sticky-wrapper" />',
      // context: $('#map-sticky-parent')[0]
      handler: function(direction) {
        _setHeaderOffset();
        _setMapSize();
      },
      offset: function() {
        return headerOffset + jumpToOffset;
      }
    });
    var mapBottomWaypoint = new Waypoint({
      element: $('.map-column')[0],
      handler: function(direction) {
        $mapContainer.toggleClass('stuck-bottom', direction==='down');
      },
      offset: 'bottom-in-view'
    });

  }

  function _mapPointClick(id, e) {
    var $mapPoint = $('.map-point[data-id=' + id + ']');

    // Scroll down to map point card
    _scrollBody($mapPoint);

    // Show highlight on card
    $('.map-point').removeClass('-hover');
    $mapPoint.addClass('-hover');

    // Show map point details
    if (!$mapPoint.hasClass('active')) {
      $mapPoint.find('a.toggler').trigger('click', e);
    } else {
      _mapPopup([$mapPoint.attr('data-lng'), $mapPoint.attr('data-lat')], $mapPoint.attr('data-title'));
    }
  }

  function _mapFlyTo(latLng, title) {
    if (map) {
      if (mapboxGlSupported) {
        // Mapbox GL
        map.flyTo({
          center: latLng,
          zoom: 11
        });
        setTimeout(function() {
          _mapPopup(latLng, title);
        }, 500);
      } else {
        // Mapbox.js (Leaflet)
        map.flyTo(L.latLng(latLng[1], latLng[0]), 14);
        _mapPopup(latLng, title);
      }
    }
  }

  function _mapPopup(latLng, title) {
    if (mapboxGlSupported) {
      popup.setLngLat(latLng)
        .setText(title)
        .addTo(map);
    } else {
      popup.setLatLng([latLng[1], latLng[0]])
        .setContent(title)
        .openOn(map);
    }
  }

  function _addMapPoints() {
    var $mapPoints = $('.map-point:not(.mapped)');
    if ($mapPoints.length) {

      if (mapboxGlSupported) {

        // Cull map points from DOM
        $mapPoints.each(function(){
          var $this = $(this);
          $this.addClass('mapped');
          if ($this.attr('data-lat')) {
            mapPointsData.features.push({
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
          } else {
            $this.addClass('no-coords');
          }
        });

        // Add points to source
        map.getSource('points').setData(mapPointsData);

        // Update map size and offsets
        _resize();

        // Center
        var bounds = new mapboxgl.LngLatBounds();
        mapPointsData.features.forEach(function(feature) {
          bounds.extend(feature.geometry.coordinates);
        });
        map.fitBounds(bounds, {padding: 100});

      } else {

        // Cull map points from DOM
        $mapPoints.each(function(){
          var $this = $(this).addClass('mapped');
          if ($this.attr('data-lat')) {
            mapPointsData.features.push({
              'type': 'Feature',
              'geometry': {
                'type': 'Point',
                'coordinates': [parseFloat($this.attr('data-lng')), parseFloat($this.attr('data-lat'))]
              },
              'properties': {
                'title': $this.attr('data-title'),
                'url': $this.attr('data-url'),
                'id': $this.attr('data-id'),
                'icon' : {
                  'iconUrl': '/app/themes/ilsfa/dist/images/icon-map-pin.svg',
                  'iconSize': [36, 48],
                  'iconAnchor': [18, 24],
                },
              },
            });
          } else {
            $this.addClass('no-coords');
          }
        });

        // Add geoJson source
        pointsLayer.setGeoJSON(mapPointsData.features);

        // Update map size and offsets
        _resize();

        // Center w/ new map points added
        map.setView(pointsLayer.getBounds().getCenter());
      }
    }
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
      var jumpToLinks = [];

      // Clear out dummy li for spacing
      $jumpTo.find('li').remove();

      // Get page-content headers and manually added [data-jumpto] links
      $('main .page-content h2, [data-jumpto]').each(function() {
        if (!$(this).attr('data-jumpto-hash')) {
          title = $(this).attr('data-jumpto') || $(this).text();
          $(this).attr('data-jumpto-hash', _slugify(title));
          jumpToLinks.push({title: title, el: this});
        }
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

      // Update offset calculations
      _setHeaderOffset();
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
    _setMapSize();
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

  // Adjust map size for viewport - headers
  function _setMapSize() {
    if (map) {
      $mapContainer.height($(window).height() - headerOffset - jumpToOffset);
      if (mapboxGlSupported) {
        map.resize();
      } else {
        map.invalidateSize();
      }
      if ($mapContainer.hasClass('stuck')) {
        $mapContainer.css('top', headerOffset + jumpToOffset);
      } else {
        $mapContainer.css('top', '');
      }
    }
  }

  // Set headerOffset var used for jumptonav placement and scrollbody calculations
  function _setHeaderOffset() {
    headerOffset = $siteHeader.outerHeight();
    if ($wpAdminBar.length && $wpAdminBar.css('position') === 'fixed') {
      headerOffset = headerOffset + $wpAdminBar.outerHeight();
    }
    // Also calculate jumpToOffset used in various placement calculations (e.g. sticky map)
    if ($jumpTo.length) {
      jumpToOffset = $jumpTo.outerHeight();
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
          action: 'load_more_posts',
          post_type: post_type,
          page: page+1,
          per_page: per_page,
          sort: $load_more.attr('data-org-sort'),
          region: $load_more.attr('data-org-region'),
          org_category: $load_more.attr('data-org-category'),
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
          if ($more_container.hasClass('compact-grid')) {
            $more_container.masonry('appended', $data, true);
          }

          if (map) {
            _addMapPoints();
            Waypoint.refreshAll();
          }

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
