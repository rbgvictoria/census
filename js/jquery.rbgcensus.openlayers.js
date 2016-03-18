var google_hybrid;
var google_streets;
var rbg_grid;

var map_world;
var google_streets2;
var google_physical;
var wgs4;
var osm;
var ol_wms;

$(function() {
    if ($('#map').length > 0) {
        init();
    }
    $('#tabs a:first').tab('show');

    if ($('#map-world').length > 0) {
        init2();
    }
    $('#tabs2 a:first').tab('show');
});

function init() {
    map = new OpenLayers.Map('map', {
            projection: 'EPSG:3857',
            displayProjection: 'EPSG:4326'
    });

    map.addControl(new OpenLayers.Control.LayerSwitcher());
    map.addControl(
        new OpenLayers.Control.MousePosition({
            div: document.getElementById("mouse-position"),
            prefix: 'coordinates: ',
            separator: '°E ',
            suffix: '°N',
            numDigits: 5,
            emptyString: ''
        })
    );
    map.addControl(
        new OpenLayers.Control.Navigation({
            dragPanOptions: {
                enableKinetic: true
            }
        })
    );
    
    osm = new OpenLayers.Layer.OSM( "Simple OSM Map");
        
    google_hybrid = new OpenLayers.Layer.Google(
        "Google Hybrid", {
            type: google.maps.MapTypeId.HYBRID, 
            numZoomLevels: 20
        }
    );

    google_streets = new OpenLayers.Layer.Google(
        "Google Streets", {
            numZoomLevels: 20
        }
    );

    rbg_grid = new OpenLayers.Layer.WMS(
        'Grids',
        'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
        {
            layers: 'rbgcensus:spatial_view',
            transparent:true,
            CQL_FILTER: cql_filter
        },
        {
            isBaseLayer: false,
            displayInLayerSwitcher: false,
            opacity: 1
        }
    );

    map.addLayers([osm, google_streets, google_hybrid, rbg_grid]);
    map.setCenter(new OpenLayers.LonLat(144.979670,-37.830039).transform('EPSG:4326', 'EPSG:3857'),16);
    
}

function init2() {
    map2 = new OpenLayers.Map( 'map-world', {
            projection: 'EPSG:3857',
            displayProjection: 'EPSG:4326'
    });
    layer = new OpenLayers.Layer.OSM();
    map2.addLayer(layer);
    
        wgs4 = new OpenLayers.Layer.WMS(
        'WGS regions',
        'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
        {
            layers: 'rbgcensus:wgs_taxa_900913',
            transparent:true,
            CQL_FILTER: cql_filter
        },
        {
            isBaseLayer: false,
            displayInLayerSwitcher: false,
            opacity: 0.5
        }
    );

    map2.addLayer(wgs4);
    map2.setCenter(
        new OpenLayers.LonLat(0, 0).transform(
            new OpenLayers.Projection("EPSG:4326"),
            map2.getProjectionObject()
        ), 1
    );    

}

function init_world() {
    map_world = new OpenLayers.Map('map-world', {
            projection: 'EPSG:3857',
            displayProjection: 'EPSG:4326'
    });

    //map_world.addControl(new OpenLayers.Control.LayerSwitcher());
    map_world.addControl(
        new OpenLayers.Control.MousePosition({
            div: document.getElementById("mouse-position2"),
            prefix: 'coordinates: ',
            separator: '°E ',
            suffix: '°N',
            numDigits: 5,
            emptyString: ''
        })
    );
    
    //var osm_world = new OpenLayers.Layer.OSM();

    google_streets2 = new OpenLayers.Layer.Google(
        "Google Streets", {
            wrapDateLine: true, 
            displayOutsideMaxExtent: true
        }
    );
        
    google_physical = new OpenLayers.Layer.Google(
        "Google Physical",
        {type: google.maps.MapTypeId.TERRAIN}
    );

    wgs4 = new OpenLayers.Layer.WMS(
        'WGS regions',
        'http://data.rbg.vic.gov.au/geoserver/rbgcensus/wms',
        {
            layers: 'rbgcensus:wgs_taxa',
            transparent:true,
            CQL_FILTER: cql_filter
        },
        {
            isBaseLayer: false,
            displayInLayerSwitcher: false,
            opacity: 1
        }
    );

    map_world.addControl(new OpenLayers.Control.LayerSwitcher());
    map_world.addLayers([google_streets2, google_physical]);
    map_world.setCenter(new OpenLayers.LonLat(30,0).transform('EPSG:4326', 'EPSG:3857'),0);
    
}

