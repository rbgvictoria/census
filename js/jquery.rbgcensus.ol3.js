var map;
var cql_filter;

$(function() {
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

    var occ = new ol.layer.Tile({
        title: "Occurrence",
        source: new ol.source.TileWMS({
          url: 'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
          params: {
              LAYERS: 'rbgcensus:spatial_view',
              TRANSPARENT: true,
              CQL_FILTER: cql_filter
          },
          serverType: 'geoserver'
        }),
        opacity: 1
    });

    view = new ol.View({
        projection: projection,
        center: [322200, 5811180],
        extent: extent,
        minZoom: 1,
        maxZoom: 4,
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
    
    $('.map').css('height', $('.map').width() + 'px');
    
    map = new ol.Map({
      target: 'map',
      //controls: ol.control.defaults().extend([mousePositionControl]),
      layers: [rbgMap, occ],
      view: view
    });
    
    $('#tabs a:first').tab('show');
    
    var wgsMap = new ol.layer.Tile({
        title: "WGS level 3 map",
        source: new ol.source.TileWMS({
          url: 'http://data.rbg.vic.gov.au/geoserver/world/wms',
          params: {
              LAYERS: 'world:level3',
              TRANSPARENT: false
          },
          serverType: 'geoserver'
        }),
        opacity: 1
    });
    
    var mapQuestMap =   new ol.layer.Tile({
        style: 'Road',
        source: new ol.source.MapQuest({layer: 'osm'})
    });

    
    var wgsDist = new ol.layer.Tile({
        title: "WGS distribution",
        source: new ol.source.TileWMS({
          url: 'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
          params: {
              LAYERS: 'rbgcensus:wgs_taxa',
              TRANSPARENT: true,
              CQL_FILTER: cql_filter
          },
          serverType: 'geoserver'
        }),
        opacity: 1
    });
    
    wView = new ol.View({
        center: [16906827.66422842, 2504688.5428486546],
        //extent: extent,
        minZoom: 1,
        maxZoom: 7,
        zoom: 1
    });
    
    wMap = new ol.Map({
      target: 'map-world',
      //controls: ol.control.defaults().extend([mousePositionControl]),
      layers: [mapQuestMap, wgsDist],
      view: wView
    });
    
    $('#tabs2 a:first').tab('show');

});