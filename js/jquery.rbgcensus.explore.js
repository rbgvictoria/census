/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var base_url = location.href.substring(0, location.href.indexOf('rbgcensus') + 9);

var map;
var view;
var commSource;
var natSource;
var commGeoJSON;
var natGeoJSON;

var collectionLayers = [];
var speciesLayers = [];

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
    
    initMap();
    mapInteraction();
    getCollectionLayers();
    getSpeciesLayers();
    toggleLayerOnOff();
    removeLayer();
    
    applyMargins();
});

var initMap = function() {
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
};

var mapInteraction = function() {
    map.on('click', function(evt) {
        
        /*
         * The JQuery element gives an error in the new version of OpenLayers,
         * so we use the native Javascript way to create the element
         * @type @exp;document@call;createElement
         */
        var element = document.createElement('div');
        $(element).attr('id','popup');

        /*var element = $('<div>', {
            'id': 'popup'
        });*/

        var popup = new ol.Overlay({
          element: element,
          positioning: 'bottom-center',
          stopEvent: false
        });
        map.addOverlay(popup);
        
        var features = [];
        map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
            features.push(feature);
        });
        
        if (features.length) {
            var content = infoWindowContent(features);
            
            
          var geometry = features[0].getGeometry();
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
};

var infoWindowContent = function(features) {
    var html = [];
    html.push('<div class="features">');
    $.each(features, function(index, feature) {
        html.push(infoWindowContentFeature(feature));
    });
    html.push('</div>');
    
    if (features.length > 1) {
        html.push('<div class="feature-nav row">');
        html.push('<div class="col-sm-6">')
        html.push('1 of ' + features.length);
        html.push('</div>');
        
        html.push('<div class="col-sm-6 text-right">');
        html.push('<button class="prev btn btn-default btn-sm" disabled="disabled"><i class="fa fa-chevron-left"></i> prev</span>');
        html.push('<button class="next btn btn-default btn-sm">next <i class="fa fa-chevron-right"></i></button>');
        html.push('</div>');
        html.push('</div>');
    }
    
    return html.join('');
};

var infoWindowContentFeature = function(feature) {
    var properties = feature.getProperties();
    var html = '<div class="feature">';
    html += '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/plant/' + properties.plant_id + '" target="_blank"><b>' + properties.plant_number + '</b></a></div>';
    html += '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/taxon/' + properties.taxon_id + '" target="_blank"><i>' + properties.taxon_name + '</i></a></div>';
    html += '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/bed/' + properties.bed_id + '" target="_blank"><i>' + properties.bed_name + '</i></a></div>';
    //html += '<div>&nbsp;</div>';
    
    /*if (properties.attributes !== undefined && properties.attributes) {
        html += '<div><span class="feature-info" data-toggle="modal" data-target="#featureInfoModal" ' + 
                'data-plant-id="' + properties.plant_id + '" ' + 
                'data-layer-group="' + properties.layerProperties.layerGroup + '" ' + 
                'data-layer-index="' + properties.layerProperties.layerIndex + '"><i class="fa fa-info-circle"></i></span></div>';
    }*/
    html += '</div>';
    return html;
};

var getCollectionLayers = function() {
    $('#collection').on('change', function() {
        var collection = $(this).val();
        if (collection) {
            var colls = [];
            $('#collection-layers>div').each(function() {
                colls.push($(this).data('collection-id'));
            });
            var i = colls.indexOf(Number(collection));
            if (i === -1) {
                getCollectionLayer(collection);
            }
            else {
                $('#collection-layers>div').eq(i).prependTo('#collection-layers');
            }
        }
    });
};

var getCollectionLayer = function(collection) {
    var format = new ol.format.GeoJSON();
    var url = base_url + '/geojson/collection/' + collection;
    var source = new ol.source.Vector({
        loader: function() {
            $.ajax({
                url: url,
                success: function(data) {
                   source.addFeatures(format.readFeatures(data));
                }
            });
        }
    });
    
    var r = Math.round(Math.random()*255);
    var g = Math.round(Math.random()*255);
    var b = Math.round(Math.random()*255);
    
    var layerProps = {
        1: {
            "icon" : "tree-icons/yellow_40/tree65_yellow_40.png",
            "name" : "Commemorative trees"
        },
        2: {
            "icon" : "tree-icons/red_40/tree68_red_40.png",
            "name" : "National Trust listed trees"
        }
    };
    
    var iconStyle = new ol.style.Style({
      image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
        anchor: [0.5, 40],
        anchorXUnits: 'fraction',
        anchorYUnits: 'pixels',
        opacity: 1,
        src: 'img/' + layerProps[collection].icon
      }))
    });
    
    var layer = new ol.layer.Vector({
      source: source,
      style: iconStyle
    });
    
    var i = collectionLayers.length;  
    layer.setProperties({
        "layerGroup": "collection",
        "layerIndex": i
    });
    collectionLayers.push(layer);
    
    map.addLayer(collectionLayers[i]);
    
    $('<div>', {
        'class': 'list-group-item checkbox',
        'html' : '<label><input type="checkbox" data-collection-layer-index="' + i + '" checked="checked"/>' + 
                '<img src="img/' + layerProps[collection].icon + '" alt="" height="20" width="20"/> ' +
                layerProps[collection].name + ' <span class="delete-layer"><i class="fa fa-trash-o"></i></span></l abel>',
        'data-collection-id': collection
    }).prependTo('#collection-layers');
};

var getSpeciesLayers = function() {
    $('input#taxon-name').autocomplete({
        source: base_url + '/autocomplete/autocomplete_taxonname_explore',
        minLength: 2,
        focus: function( event, ui ) {
            $( "#taxon-name" ).val( ui.item.taxon_name );
            return false;
        },
        select: function( event, ui ) {
            $( "#taxon-id" ).val( ui.item.guid );
            $( "#taxon-name" ).val( ui.item.taxon_name );
            
            var guids = [];
            $('#species-layers>div').each(function() {
                guids.push($(this).data('taxon-id'));
            });
            var i = guids.indexOf(ui.item.guid);
            if (i === -1) {
                getSpeciesLayer(ui.item.guid);
            }
            else {
                $('#species-layers>div').eq(i).prependTo('#species-layers');
            }
            return false;
        }
    })
    .autocomplete("instance")._renderItem = function(ul, item) {
        ul.addClass('rbgcensus-explore-autocomplete-list');
        return $( "<li>", {html: item.taxon_name} ).appendTo( ul );
    };
};

var getSpeciesLayer = function(guid) {
    var format = new ol.format.GeoJSON();
    var url = base_url + '/geojson/species/' + guid;
    var source = new ol.source.Vector({
        loader: function() {
            $.ajax({
                url: url,
                success: function(data) {
                    source.addFeatures(format.readFeatures(data));
                }
            });
        }
    });
    
    var r = Math.round(Math.random()*255);
    var g = Math.round(Math.random()*255);
    var b = Math.round(Math.random()*255);
    
    var fill = new ol.style.Fill({
        color: [r,g,b,1.0]
    });
    
    var fillText = 'rgba(' + r + ',' + g + ',' + b + ', 1.0)';
    
    var stroke = new ol.style.Stroke({
        color: '#000',
        width: 1.25
    });
    
    var iconStyle = new ol.style.Style({
        image: new ol.style.Circle({
            fill: fill,
            stroke: stroke,
            radius: 8
        }),
        fill: fill,
        stroke: stroke
    });
    
    var layer = new ol.layer.Vector({
      source: source,
      style: iconStyle
    });
    var i = speciesLayers.length;  
    layer.setProperties({
        "layerGroup": "species",
        "layerIndex": i
    });
    speciesLayers.push(layer);
    
    map.addLayer(speciesLayers[i]);
    
    var circle = '<svg height="16" width="16">' +
            '<circle cx="8" cy="10" r="6" stroke="black" stroke-width="1" fill="' + fillText + '" />' +
        '</svg>';

    var triangle = '<svg height="16" width="16">' +
        '<polygon points="2,16 8,4 14,16" style="fill:' + fillText + ';stroke:black;stroke-width:1" />' +
        '</svg>';

    $('<div>', {
        'class': 'list-group-item checkbox',
        'html' : '<label><input type="checkbox" data-species-layer-index="' + i + '" checked="checked"/>' + 
                '<span class="icon">' + circle + '</span> ' + 
                $('#taxon-name').val() + '</label>' +  
                '<span class="attribute-table" data-toggle="modal" data-target="#attributeTableModal"><i class="fa fa-table"></i></span>' + 
                ' <span class="delete-layer"><i class="fa fa-trash-o"></i></span>',
        'data-taxon-id': guid
    }).prependTo('#species-layers');
};

var toggleLayerOnOff = function() {
    $('#species-layers').on('click', ':checkbox', function() {
        var index = Number($(this).attr('data-species-layer-index'));
        if ($(this).prop('checked')) {
            map.addLayer(speciesLayers[index]);
        }
        else {
            map.removeLayer(speciesLayers[index]);
        }
    });
    
    $('#collection-layers').on('click', ':checkbox', function() {
        var index = Number($(this).attr('data-collection-layer-index'));
        if ($(this).prop('checked')) {
            map.addLayer(collectionLayers[index]);
        }
        else {
            map.removeLayer(collectionLayers[index]);
        }
    });
};

var removeLayer = function() {
    $('#species-layers').on('click', '.delete-layer', function() {
        var parentDiv = $(this).parents('div').eq(0);
        var checked = parentDiv.find(':checkbox').eq(0).prop('checked');
        var index = Number(parentDiv.find(':checkbox').eq(0).attr('data-species-layer-index'));
        if (checked) {
            map.removeLayer(speciesLayers[index]);
        }
        speciesLayers[index] = {};
        parentDiv.remove();
    });
    
    $('#collection-layers').on('click', '.delete-layer', function() {
        var parentDiv = $(this).parents('div').eq(0);
        var checked = parentDiv.find(':checkbox').eq(0).prop('checked');
        var index = Number(parentDiv.find(':checkbox').eq(0).attr('data-collection-layer-index'));
        if (checked) {
            map.removeLayer(collectionLayers[index]);
        }
        collectionLayers[index] = {};
        parentDiv.remove();
    });
};

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