/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var base_url = 'http://data.rbg.vic.gov.au/dev/rbgcensus';

var map;
var view;
var commSource;
var natSource;
var commGeoJSON;
var natGeoJSON;

$(function() {
    $('.sidebar-left .slide-submenu').on('click',function() {
        var thisEl = $(this);
        thisEl.closest('.sidebar-body').fadeOut('slide',function(){
          $('.mini-submenu-left').fadeIn();
          applyMargins();
        });
    });

    $('.mini-submenu-left').on('click',function() {
        var thisEl = $(this);
        $('.sidebar-left .sidebar-body').toggle('slide');
        thisEl.hide();
        applyMargins();
    });

    $('.sidebar-right .slide-submenu').closest('.sidebar-body').fadeOut('slide',function(){
        $('.mini-submenu-right').fadeIn();
        applyMargins();
    });
    
    $('.sidebar-right .slide-submenu').on('click',function() {
        var thisEl = $(this);
        thisEl.closest('.sidebar-body').fadeOut('slide',function(){
          $('.mini-submenu-right').fadeIn();
          applyMargins();
        });
    });

    $('.mini-submenu-right').on('click',function() {
        var thisEl = $(this);
        $('.sidebar-right .sidebar-body').toggle('slide');
        thisEl.hide();
        applyMargins();
    });
    
    $(window).on("resize", applyMargins);
    
    var projection = new ol.proj.Projection({
        code: 'EPSG:28355',
        extent: [321679.9999999997,5810629.999999995,322719.9999999997,5811729.999999995]
    });
    
    var extent = [321679.9999999997,5810629.999999995,322719.9999999997,5811729.999999995];
    var rbgBase = new ol.layer.Tile({
        title: "RBG aerial photo",
        source: new ol.source.TileWMS({
            url: 'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
            params: {
                LAYERS: 'rbgcensus:RBG_Melbourne',
                VERSION: "1.1.0"
            },
            serverType: 'geoserver'
        })
    });
    
    var rbgGrid = new ol.layer.Tile({
        title: "RBG grid cells",
        source: new ol.source.TileWMS({
          url: 'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
          params: {
              LAYERS: 'rbgcensus:rbg_grid_mga', 
              TRANSPARENT: true
          },
          serverType: 'geoserver'
        }),
        opacity: 1,
        maxResolution: 0.53719375
    });

    $('#rbg-grid').on('click', 'input[type=checkbox]', function() {
        if ($(this).prop('checked')) {
            map.addLayer(rbgGrid);
        }
        else {
            map.removeLayer(rbgGrid);
        }
    })
    
    var rbgMap = new ol.layer.Tile({
        title: "RBG map",
        source: new ol.source.TileWMS({
          url: 'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
          params: {
              LAYERS: 'rbgcensus:feature,rbgcensus:outline',
              TRANSPARENT: false
          },
          serverType: 'geoserver'
        }),
        opacity: 1
    });

    $('#rbg-map').on('click', 'input[type=checkbox]', function() {
        if ($(this).prop('checked')) {
            map.addLayer(rbgMap);
        }
        else {
            map.removeLayer(rbgMap);
        }
    })
    
    
    // Commemorative trees
    commSource = new ol.source.ServerVector({
        format: new ol.format.GeoJSON(),
        loader: function() {
            var url = base_url + '/geojson/collection/1';
            $.ajax({
                url: url,
                success: function(data) {
                    commGeoJSON = data;
                    commSource.addFeatures(commSource.readFeatures(data));
                }
            });
        }
    });
    
    var iconStyleCT = new ol.style.Style({
      image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
        anchor: [0.5, 40],
        anchorXUnits: 'fraction',
        anchorYUnits: 'pixels',
        opacity: 1,
        src: base_url + '/img/tree-icons/yellow_40/tree65_yellow_40.png'
      }))
    });
    
    commemorativeTrees = new ol.layer.Vector({
      source: commSource,
      style: iconStyleCT
    });

    $('#commemorative :checkbox').removeAttr('checked');
    $('#commemorative').on('click', 'input[type=checkbox]', function() {
        if ($(this).prop('checked')) {
            map.addLayer(commemorativeTrees);
        }
        else {
            map.removeLayer(commemorativeTrees);
        }
    })
    
    // National Trust listed
    natSource = new ol.source.ServerVector({
        format: new ol.format.GeoJSON(),
        loader: function() {
            var url = base_url + '/geojson/collection/2';
            $.ajax({
                url: url,
                success: function(data) {
                    natGeoJSON = data;
                    natSource.addFeatures(natSource.readFeatures(data));
                }
            });
        }
    });
    
    var iconStyle = new ol.style.Style({
      image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
        anchor: [0.5, 40],
        anchorXUnits: 'fraction',
        anchorYUnits: 'pixels',
        opacity: 1,
        src: base_url + '/img/tree-icons/red_40/tree68_red_40.png'
      }))
    });
    
    nationalTrust = new ol.layer.Vector({
      source: natSource,
      style: iconStyle
    });

    $('#national-trust :checkbox').removeAttr('checked');
    $('#national-trust').on('click', 'input[type=checkbox]', function() {
        if ($(this).prop('checked')) {
            map.addLayer(nationalTrust);
        }
        else {
            map.removeLayer(nationalTrust);
        }
    })
    
    view = new ol.View({
        projection: projection,
        center: [322200, 5811180],
        extent: extent,
        minZoom: 1,
        maxZoom: 7,
        zoom: 2
    });
    
    var template = 'MGA 55 {x} {y}';
    
    var mousePositionControl = new ol.control.MousePosition({
        coordinateFormat: function(coord) {
            return ol.coordinate.format(coord, template, 0);
        },
        projection: 'EPSG:28355',
        // comment the following two lines to have the mouse position
        // be placed within the map.
        undefinedHTML: '&nbsp;'
    });

    map = new ol.Map({
      target: 'map',
      controls: ol.control.defaults().extend([mousePositionControl]),
      layers: [rbgBase],
      view: view
    });
    
    $('#rbg-grid').hide();
    map.getView().on('change:resolution', function() {
        if (view.getZoom() > 2) {
            $('#rbg-grid').show();
        }
        else {
            $('#rbg-grid').hide();
        }
    });
    
    // From: http://stackoverflow.com/questions/26022029/how-to-change-the-cursor-on-hover-in-openlayers-3
    var target = map.getTarget();
    var jTarget = typeof target === "string" ? $("#" + target) : $(target);
    // change mouse cursor when over marker
    $(map.getViewport()).on('mousemove', function (e) {
        var pixel = map.getEventPixel(e.originalEvent);
        var hit = map.forEachFeatureAtPixel(pixel, function (feature, layer) {
            return true;
        });
        if (hit) {
            jTarget.css("cursor", "pointer");
        } else {
            jTarget.css("cursor", "");
        }
    });
    
    map.on('click', function(evt) {
        var element = $('<div>', {
            'id': 'popup'
        });

        var popup = new ol.Overlay({
          element: element,
          positioning: 'bottom-center',
          stopEvent: false
        });
        map.addOverlay(popup);
        
        var feature = map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
            //console.log(feature.getProperties().grid_code);
            return feature;
        });
        if (feature) {
            var grid_code = feature.getProperties().grid_code;
            var trees = [];
            if ($('#commemorative :checkbox').prop('checked')) {
                var items = JSPath('..properties{.grid_code == "' + grid_code + '"}', commGeoJSON);
                if (items.length) {
                  trees.push('<h4>Commemorative trees</h4>');
                    $.each(items, function(index, item){
                        var tree = '<div class="popup-content-item">';
                        tree += '<div><a href="' + base_url + '/census/plant/' + item.plant_guid + '" target="_blank"><b>' + item.plant_number + '</b></a></div>';
                        tree += '<div><a href="' + base_url + '/census/taxon/' + item.taxon_guid + '" target="_blank"><i>' + item.taxon_name + '</i></a></div>';
                        if (item.attributes.commemorative !== undefined) {
                            tree += '<div><b>Commemorative:</b> ' + item.attributes.commemorative + '</div>';
                        }
                        if (item.attributes.in_memory_of !== undefined) {
                            tree += '<div><b>In memory of:</b> ' + item.attributes.in_memory_of + '</div>';
                        }
                        if (item.attributes.date_planted !== undefined) {
                            tree += '<div><b>Date planted:</b> ' + item.attributes.date_planted + '</div>';
                        }
                        tree += '</div>';
                        trees.push(tree);
                    });
                }
            }
            if ($('#national-trust :checkbox').prop('checked')) {
                var items = JSPath('..properties{.grid_code == "' + grid_code + '"}', natGeoJSON);
                if (items.length) {
                    trees.push('<h4>National Trust</h4>');
                    $.each(items, function(index, item){
                        var tree = '<div class="popup-content-item">';
                        tree += '<div><a href="' + base_url + '/census/plant/' + item.plant_guid + '" target="_blank"><b>' + item.plant_number + '</b></a></div>';
                        tree += '<div><a href="' + base_url + '/census/taxon/' + item.taxon_guid + '" target="_blank"><i>' + item.taxon_name + '</i></a></div>';
                        if (item.attributes.national_trust_status !== undefined) {
                            tree += '<div><b>Status:</b> ' + item.attributes.national_trust_status + '</div>';
                        }
                        if (item.attributes.national_trust_significance !== undefined) {
                            tree += '<div><b>Significance:</b> ' + item.attributes.national_trust_significance + '</div>';
                        }
                        tree += '</div>';
                        trees.push(tree);
                    });
                }
            }
            var content = trees.join('');
            
            
          var geometry = feature.getGeometry();
          var coord = geometry.getCoordinates();
          popup.setPosition(coord);
          /*$(element).popover({
            'placement': 'top',
            'html': true,
            'content': content
          });
          $(element).popover('show');*/
          $(element).html(content).prepend('<span class="popup-close"><i class="fa fa-times fa-border"></i></span>');
        }
    });
    
    /*map.on('pointermove', function(e) {
        //if (e.dragging) {
          
          return;
        //}
        //var pixel = map.getEventPixel(e.originalEvent);
        //var hit = map.hasFeatureAtPixel(pixel);
        //map.getTarget().style.cursor = hit ? 'pointer' : '';
    });*/
    
    $('#map').on('mouseleave', '#popup', function(e) {
        $(this).detach();
    });
    
    $('#map').on('click', '.popup-close', function(e) {
        $(this).parent().detach();
    });
    
    applyMargins();
});

function applyMargins() {
    var leftToggler = $(".mini-submenu-left");
    var rightToggler = $(".mini-submenu-right");
    if (leftToggler.is(":visible")) {
        $("#map .ol-zoom")
          .css("margin-left", 0)
          .removeClass("zoom-top-opened-sidebar")
          .addClass("zoom-top-collapsed");
    } else {
        $("#map .ol-zoom")
          .css("margin-left", $(".sidebar-left").width())
          .removeClass("zoom-top-opened-sidebar")
          .removeClass("zoom-top-collapsed");
    }
    if (rightToggler.is(":visible")) {
        $("#map .ol-rotate")
          .css("margin-right", 0)
          .removeClass("zoom-top-opened-sidebar")
          .addClass("zoom-top-collapsed");
    } else {
        $("#map .ol-rotate")
          .css("margin-right", $(".sidebar-right").width())
          .removeClass("zoom-top-opened-sidebar")
          .removeClass("zoom-top-collapsed");
    }
}

function isConstrained() {
    return $("div.mid").width() == $(window).width();
}

function applyInitialUIState() {
    if (isConstrained()) {
        $(".sidebar-left .sidebar-body").fadeOut('slide');
        $(".sidebar-right .sidebar-body").fadeOut('slide');
        $('.mini-submenu-left').fadeIn();
        $('.mini-submenu-right').fadeIn();
    }
}




