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
    attributeTableModal();
    featureInfoModal();
    
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
        opacity: 1,
        visible: false
    });

    view = new ol.View({
        projection: projection,
        center: [322200, 5811180],
        extent: extent,
        minZoom: 1,
        maxZoom: 5,
        zoom: 2
    });
    
    var template = 'MGA 55 {x} {y}';
    
    var mousePositionControl = new ol.control.MousePosition({
        coordinateFormat: function(coord) {
            return ol.coordinate.format(coord, template, 0);
        },
        projection: 'EPSG:28355',
        undefinedHTML: '&nbsp;'
    });

    map = new ol.Map({
      target: 'map',
      controls: ol.control.defaults().extend([mousePositionControl]),
      layers: [rbgBase, rbgMap],
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
    
    toggleBaseMap(rbgBase, rbgMap);

    
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

var toggleBaseMap = function(rbgBase, rbgMap) {
    $('#base-map-toggle').on('click', '.btn', function(e) {
        console.log('Hello');
        if (!$(e.target).hasClass('active')) {
            if ($(e.target).children('input').eq(0).attr('id') === 'base-map-map') {
                rbgMap.setVisible(true);
                rbgBase.setVisible(false);
            }
            else {
                rbgBase.setVisible(true);
                rbgMap.setVisible(false);
            }
        }
    });
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
    
    /*var iconStyle = new ol.style.Style({
        image: new ol.style.Icon({
          color: [r, g, b, 1],
          src: 'img/icons/dot.png'
        })
    });*/
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

var attributeTableModal = function() {
    var template = heredoc(function() {/*
        <div class="modal fade" id="attributeTableModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-table fa-lg"></i> Attribute table</h4>
              </div>
              <div class="modal-body">
                <h2 class="layer-title"></h2>
                <table class="table table-condensed table-bordered">
                    <thead></thead>
                    <tbody></tbody>
                </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        */});
    $('<div/>', {
        html: template
    }).appendTo('body');
    
    $('#attributeTableModal').on('show.bs.modal', function (event) {
        var target = $(event.relatedTarget);
        var taxonID = target.parents('div').eq(0).data('taxon-id');
        var taxonName = target.parents('div').eq(0).children('label').text();
        var index = target.parents('div').eq(0).find(':checkbox').eq(0).data('species-layer-index');
        
        modal = $(this);
        modal.find('.layer-title').text(taxonName);
        
        var thead = heredoc(function() {/*
            <tr>
                <th>Plant number</th>
                <th>Taxon name</th>
                <th>Bed name</th>
            </tr>
        */});
        modal.find('thead').html(thead);
        modal.find('tbody').html('');
        
        var features = speciesLayers[index].getSource().getFeatures();
        $.each(features, function(index, feature) {
            var props = feature.getProperties();
            var tr = $('<tr/>').appendTo(modal.find('tbody'));
            $('<td/>').append('<a href="' + base_url + '/census/plant/' + props.plant_id + '" target="_blank">' + props.plant_number + '</a>').appendTo(tr);
            $('<td/>').append('<a href="' + base_url + '/census/taxon/' + props.taxon_id + '" target="_blank">' + props.taxon_name + '</a>').appendTo(tr);
            $('<td/>').append('<a href="' + base_url + '/census/bed/' + props.bed_id + '" target="_blank">' + props.bed_name + '</a>').appendTo(tr);
        });
        
    }).on('shown.bs.modal', function(event) {
        var windowHeight = $(window).height();
        var dialogHeight = $('#attributeTableModal .modal-dialog').height();
        if (dialogHeight > windowHeight * 0.80) {
            $('#attributeTableModal .modal-dialog, #attributeTableModal .modal-content').animate({
                height: '80%'
            }, 1000);
        }
    }).on('hidden.bs.modal', function() {
        $('#attributeTableModal .modal-dialog, #attributeTableModal .modal-content').css('height', 'auto');
    });
    
};

var featureInfoModal = function() {
    var template = heredoc(function() {/*
        <div class="modal fade" id="featureInfoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-info-circle fa-lg"></i> Feature info</h4>
              </div>
              <div class="modal-body">
                <h2 class="feature-title"></h2>
                <div class="feature-info"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        */});
    $('<div/>', {
        html: template
    }).appendTo('body');
    
    $('#featureInfoModal').on('show.bs.modal', function (event) {
        var target = $(event.relatedTarget);
        var plantID = target.data('plant-id');
        console.log(plantID);
        var layerGroup = target.data('layerGroup');
        var layerIndex = target.data('layerIndex');
        
        var features = [];
        if (layerGroup === 'collection') {
            features = collectionLayers[layerIndex].getSource().getFeatures();
        }
        else if (layerGroup === 'species') {
            features = speciesLayers[layerIndex].getSource().getFeatures();
        }
        
        featureProperties = [];
        $.each(features, function(index, feature) {
            var properties = feature.getProperties();
            featureProperties.push(properties);
        });
        console.log(featureProperties);
        
        var props = JSPath.apply('.{.plant_id=="' + plantID + '"}', featureProperties)[0];
        
        modal = $(this);
        modal.find('.feature-title').text(props.plant_number);
        modal.find('.feature-info').html('');
        
        $('<div/>', {
            html: '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/taxon/' + props.taxon_id + '" target="_blank"><i>' + props.taxon_name + '</i></a></div>'
        }).appendTo(modal.find('.feature-info'));
        
        $('<div/>', {
            html: '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/bed/' + props.bed_id + '" target="_blank"><i>' + props.bed_name + '</i></a></div>'
        }).appendTo(modal.find('.feature-info'));
        
        if (props.attributes !== undefined) {
            var labels = {
                "commemorative": "Commemorative",
                "national_trust_status": "Status",
                "national_trust_significance": "Significance",
                "date_planted": "Date planted",
            }
            
            Object.keys(props.attributes).forEach(function(key) {
                $('<div/>', {
                    html: '<b>' + labels[key] + ':</b> ' + props.attributes[key]
                }).appendTo(modal.find('.feature-info'));
            });            
        }
        
    }).on('shown.bs.modal', function(event) {
        var windowHeight = $(window).height();
        var dialogHeight = $('#featureInfoModal .modal-dialog').height();
        if (dialogHeight > windowHeight * 0.80) {
            $('#featureInfoModal .modal-dialog, #featureInfoModal .modal-content').animate({
                height: '80%'
            }, 1000);
        }
    }).on('hidden.bs.modal', function() {
        $('#featureInfoModal .modal-dialog, #featureInfoModal .modal-content').css('height', 'auto');
    });
};


var mapInteraction = function() {
    
    map.on('click', function(evt) {
        if (!$('.popover:hover').length) {
            $('.popup').popover('hide');
        }
        
        var element = document.createElement('div');
        $(element).addClass('popup');

        var popup = new ol.Overlay({
          element: element,
          positioning: 'bottom-center',
          stopEvent: false,
        });
        map.addOverlay(popup);
        
        var features = [];
        map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
            feature.setProperties({
                "layerProperties": layer.getProperties()
            });
            features.push(feature);
        });
        
        if (features.length) {
            var content = infoWindowContent(features);
            
            if (!$('.popover:hover').length) {
                var template = heredoc(function() {/*
                    <div class="popover" role="tooltip">
                        <div class="arrow"></div>
                        <h3 class="popover-title"></h3>
                        <div class="popover-content"></div>
                        <div class="popover-footer"></div>
                    </div>                 
                */});
                
                popup.setPosition(evt.coordinate);
                $(element).popover({
                  placement: 'top',
                  title: '<div class="text-right"><span class="close">&times;</span></div>',
                  html: true,
                  content: content,
                  template: template
                });
                $(element).popover('show');
                if (features.length > 1) {
                    console.log('Hello');
                    $('.popup').on('shown.bs.popover', function() {
                        $('<div/>', {
                            class: "feature-nav row"
                        }).appendTo('.popover-footer');
                        $('<div/>', {
                            class: "col-sm-6",
                            html: '1 of ' + features.length
                        }).appendTo('.feature-nav');
                        $('<div/>', {
                            class: "col-sm-6 text-right"
                        }).append('<button class="prev btn btn-default btn-sm" disabled="disabled"><i class="fa fa-chevron-left"></i> prev</span></button>')
                                .append('<button class="next btn btn-default btn-sm">next <i class="fa fa-chevron-right"></i></button>').appendTo('.feature-nav');
                    });
                }
                
                autoPan(evt.coordinate);
            }

            $('.popover').on('click', 'button', function() {
                var height = $('.features').eq(0).height();
                var index = $('.popover .feature').index($('.popover .feature').not(':hidden'));
                var newIndex;
                if ($(this).hasClass('prev')) {
                    newIndex = index - 1;
                }
                else {
                    newIndex = index + 1;
                }
                $(this).parents('.popover').eq(0).find('.feature').hide();
                $(this).parents('.popover').eq(0).find('.feature').eq(newIndex).show();
                $(this).parents('.feature-nav').eq(0).children('div').eq(0).html((newIndex + 1) + ' of ' + features.length);
                $('.features').css('height', height + 'px');
                $('.prev').removeAttr('disabled');
                $('.next').removeAttr('disabled');
                if (newIndex === 0) {
                    $('.prev').attr('disabled', 'disabled');
                }
                if (newIndex === features.length - 1) {
                    $('.next').attr('disabled', 'disabled');
                }
            });
            
            $('.popover .close').click(function() {
                $('.popover').popover('hide');
            });
        }
    });
    
};

var infoWindowContent = function(features) {
    var html = [];
    html.push('<div class="features">');
    $.each(features, function(index, feature) {
        html.push(infoWindowContentFeature(feature));
    });
    html.push('</div>');
    return html.join('');
};

var infoWindowContentFeature = function(feature) {
    var properties = feature.getProperties();
    var html = '<div class="feature">';
    html += '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/plant/' + properties.plant_id + '" target="_blank"><b>' + properties.plant_number + '</b></a></div>';
    html += '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/taxon/' + properties.taxon_id + '" target="_blank"><i>' + properties.taxon_name + '</i></a></div>';
    html += '<div><a href="http://data.rbg.vic.gov.au/rbgcensus/census/bed/' + properties.bed_id + '" target="_blank"><i>' + properties.bed_name + '</i></a></div>';
    
    if (properties.attributes !== undefined && properties.attributes) {
        html += '<div><span class="feature-info" data-toggle="modal" data-target="#featureInfoModal" ' + 
                'data-plant-id="' + properties.plant_id + '" ' + 
                'data-layer-group="' + properties.layerProperties.layerGroup + '" ' + 
                'data-layer-index="' + properties.layerProperties.layerIndex + '"><i class="fa fa-info-circle"></i></span></div>';
    }
    html += '</div>';
    return html;
};

/**
 * @function autoPan
 * 
 * @description Re-centers the map to ensure that the entire info window overlay reains within the viewport; the built-in 
 *   autopanning of Openlayers 3 does not work for the Bootstrap pop-overs we use. Source adapted from 
 *   http://stackoverflow.com/questions/34209971/popover-overlay-in-openlayers-3-does-not-extend-beyond-view#answer-34220334. 
 *   I added the animation.
 * @param {type} coordinate
 * @returns {undefined}
 */
var autoPan = function(coordinate) {
    var extent = map.getView().calculateExtent(map.getSize());
    var center = map.getView().getCenter();
    var pixelPosition = map.getPixelFromCoordinate([ coordinate[0], coordinate[1] ]);
    var mapWidth = $("#map").width();
    var mapHeight = $("#map").height();
    var popoverHeight = $(".popover").height();
    var popoverWidth = $(".popover").width();
    var thresholdTop = popoverHeight+50;
    var thresholdBottom = mapHeight;
    var thresholdLeft = popoverWidth/2-80;
    var thresholdRight = mapWidth-popoverWidth/2-130;
    if(pixelPosition[0] < thresholdLeft || pixelPosition[0] > thresholdRight || pixelPosition[1]<thresholdTop || pixelPosition[1]>thresholdBottom) {
        if(pixelPosition[0] < thresholdLeft) {
            var newX = pixelPosition[0]+(thresholdLeft-pixelPosition[0]);
        } else if(pixelPosition[0] > thresholdRight) {
            var newX = pixelPosition[0]-(pixelPosition[0]-thresholdRight);
        } else {
            var newX = pixelPosition[0];
        }
        if(pixelPosition[1]<thresholdTop) {
            var newY = pixelPosition[1]+(thresholdTop-pixelPosition[1]);
        } else if(pixelPosition[1]>thresholdBottom) {
            var newY = pixelPosition[1]-(pixelPosition[1]-thresholdBottom);
        } else {
            var newY = pixelPosition[1];
        }
        newCoordinate = map.getCoordinateFromPixel([newX, newY]);   
        newCenter = [(center[0]-(newCoordinate[0]-coordinate[0])), (center[1]-(newCoordinate[1]-coordinate[1])) ]
        var pan = ol.animation.pan({
            source: center,
        });
        map.beforeRender(pan);
        map.getView().setCenter(newCenter);
    }
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

/**
 * @description HEREDOC for Javascript; source:
 *   http://stackoverflow.com/questions/4376431/javascript-heredoc#answer-14496573
 * @param {string} f
 * @returns (string)
 */
var heredoc = function(f) {
    return f.toString().match(/\/\*\s*([\s\S]*?)\s*\*\//m)[1];
};