<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GeoJSON extends CI_Controller {
    function  __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('geojsonmodel');
        $this->output->enable_profiler(false);

    }
    
    public function search() {
        if (!$_SERVER['QUERY_STRING'])
            redirect(base_url());
        
        $qarray = explode('&', $_SERVER['QUERY_STRING']);
        $terms = array();
        foreach ($qarray as $item) {
            list($key, $value) = explode('=', $item);
            $terms[$key] = $value;
        }
        
        $where = array();
        if (isset($terms['taxon']) && $terms['taxon']) {
            $t = urldecode($terms['taxon']);
            $where['taxon'] = $t;
        }
        elseif (isset($terms['q']) && $terms['q']) {
            $t = urldecode($terms['q']);
            $where['taxon'] = $t;
        }
        if (isset($terms['common_name']) && $terms['common_name']) {
            $t = urldecode($terms['common_name']);
            $where['common_name'] = $t;
        }
        if (isset($terms['family']) && $terms['family']) {
            $t = urldecode($terms['family']);
            $where['family'] = $t;
        }
        if (isset($terms['location']) && $terms['location']) {
            $t = urldecode($terms['location']);
            $where['location'] = $t;
        }
        if (isset($terms['precinct']) && $terms['precinct']) {
            $t = urldecode($terms['precinct']);
            $where['precinct'] = $t;
        }
        if (isset($terms['bed']) && $terms['bed']) {
            $t = urldecode($terms['bed']);
            $where['bed'] = $t;
        }
        if (isset($terms['grid']) && $terms['grid']) {
            $where['grid'] = urldecode($terms['grid']);
        }
        if (isset($terms['wgs_code']) && $terms['wgs_code']) {
            $where['wgs_code'] = $terms['wgs_code'];
        }
        if (isset($terms['wgs_fullname']) && $terms['wgs_fullname']) {
            $t = urldecode($terms['wgs_fullname']);
            $this->load->model('censusmodel');
            $wgs = $this->censusmodel->getWgsCode($t);
            if (!$wgs) {
                redirect(base_url());
            }
            $where['wgs_code'] = $wgs['code'];
        }
        
        if ($where) {
            $this->createGeoJSON($this->geojsonmodel->getGridCells($where));
        }
        
    }
    function bed($uuid) {
        $terms = array();
        $terms['bed_guid'] = $uuid;
        $data = $this->geojsonmodel->getGridCells($terms);
        $this->createGeoJSON($data);
    }
    
    function accession($uuid) {
        $terms = array();
        $terms['accession_guid'] = $uuid;
        $data = $this->geojsonmodel->getGridCells($terms);
        $this->createGeoJSON($data);
    }
    
    private function createGeoJSON($data, $srid=false) {
        $srid = ($srid) ? $srid : '3857';
        $features = array();
        
        foreach ($data as $row) {
            $feature = array();
            $feature['type'] = 'Feature';
            $feature['geometry'] = json_decode($row->geometry);
            
            $properties = (isset($row->properties)) ? $row->properties : array('code' => $row->code);
            
            $feature['properties'] = (object) $properties;
            $features[] = (object) $feature;
        }
        
        $collection = array(
            'type' => 'FeatureCollection',
            'crs' => $this->createCrs($srid),
            'features' => $features
        );
        
        $json = json_encode($collection);
        
        header('Content-type: application/json');
        echo $json;
    }
    
    private function createCrs($srid) {
        $crs = array();
        $crs['type'] = 'name';
        $crs['properties'] = (object) array(
            'name' => 'EPSG:' . $srid
        );
        return (object) $crs;
    }
    
    public function collection($collection=FALSE) {
        $data = $this->geojsonmodel->getCollectionCentroidsAmg($collection);
        $featuredata = array();
        foreach ($data as $row) {
            $feature = array();
            $feature['geometry'] = $row['geometry'];
            $properties = array();
            $properties['taxon_name'] = $row['taxon_name'];
            $properties['taxon_guid'] = $row['taxon_guid'];
            $properties['plant_number'] = $row['accession_number'] . '.' . $row['plant_number'];
            $properties['plant_guid'] = $row['plant_guid'];
            $properties['grid_code'] = $row['grid_code'];
            if ($row['attr_label']) {
                $attributes = array();
                foreach ($row['attr_label'] as $index => $value) {
                    $attributes[$value] = $row['attr_value'][$index]; 
                }
                $properties['attributes'] = (object) $attributes;
            }
            $feature['properties'] = (object) $properties;
            $featuredata[] = (object) $feature;
        }
        $this->createGeoJSON($featuredata, '28355');
    }
    
    
    
    


}

/* End of file geojson.php */
/* Location: ./rbgcensus/controllers/geojson.php */