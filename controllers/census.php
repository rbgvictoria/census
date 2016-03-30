<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Census extends CI_Controller {
    var $data;
    
    function  __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('versioning');
        $this->output->enable_profiler(true);
        
        // Allow for custom style sheets and javascript
        $this->data['css'] = array();
        $this->data['js'] = array();
        $this->data['iehack'] = FALSE;
        
        $this->load->model('censusmodel');
    }

    public function index() {
        $this->data['beds'] = $this->censusmodel->getBeds();
        $this->data['precincts'] = $this->censusmodel->getPrecincts();
        $this->data['gridcodes'] = $this->censusmodel->getGridCodes();
        $this->load->view('home_view', $this->data);
    }
    
    public function map() {
        $this->load->view('map_view');
    }
    
    public function search() {
        if (!$_SERVER['QUERY_STRING'])
            redirect(base_url());
        
        $query = $this->queryString();
        
        if ($query->where) {
            $this->data['query_string'] = implode('&', $query->qstring);
            $this->data['start'] = $query->start;
            $this->data['rows'] = $query->rows;
            $this->data['numbers'] = $this->censusmodel->countPlants($query->where, $query->inclDeaccessioned);
            $this->data['cql_filter'] = implode(' AND ', $query->cql);
            
            if ($this->data['numbers']['num_plants'] > 0 ) {
                if ($query->start > 0) {
                    $plants = $this->censusmodel->findPlants($query->where, $query->sort, $query->rows+1, $query->start-1, $query->inclDeaccessioned);
                    $shift = array_shift($plants);
                    $this->data['plants'] = $plants;
                    $this->data['previous_taxon'] = $shift['taxon_name'];
                } 
                else {
                    $this->data['plants'] = $this->censusmodel->findPlants($query->where, $query->sort, $query->rows, $query->start, $query->inclDeaccessioned);
                    $this->data['previous_taxon'] = FALSE;
                }
                $guids = array();
                foreach ($this->data['plants'] as $plant) {
                    $guids[] = $plant['taxon_guid'];
                }
                $this->data['taxon_guids'] = $guids;
            }
            elseif (in_array('taxon', array_keys($query->where))) {
                $this->data['accepted_names'] = $this->censusmodel->getAcceptedNames($query->where);
            }
            $this->load->view('search_view', $this->data);
        }
        else {
            redirect(base_url());
        }
    }
    
    public function download_plant_list() {
        if (!$_SERVER['QUERY_STRING'])
            redirect(base_url());
        
        $query = $this->queryString();
        
        if ($query->where) {
            $data = $this->censusmodel->findPlantsDownload($query->where, $query->inclDeaccessioned);
            $csv = $this->arrayToCsv($data);
            
            $filename = 'rbgv_plant_list_' . date('Ymd_His') . '.csv';
            $this->output->enable_profiler(false);
            header('Content-type: text/csv');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            echo $csv;
        }
    }
    
    public function download_taxon_list() {
        if (!$_SERVER['QUERY_STRING'])
            redirect(base_url());
        
        $query = $this->queryString();
        
        if ($query->where) {
            $data = $this->censusmodel->findTaxaDownload($query->where, $query->inclDeaccessioned);
            $csv = $this->arrayToCsv($data);
            
            $filename = 'rbgv_taxon_list_' . date('Ymd_His') . '.csv';
            $this->output->enable_profiler(false);
            header('Content-type: text/csv');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            echo $csv;
        }
    }
    
    private function arrayToCsv($data) {
        $csv = array();
        $csv[] = $this->arrayToCsvRow(array_keys((array) $data[0]), ',');
        foreach ($data as $row) {
            $csv[] = $this->arrayToCsvRow(array_values((array) $row), ',');
        }
        return implode("\n", $csv);
    }
    
    private function arrayToCsvRow( array &$fields, $delimiter = ',', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ( $fields as $field ) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else {
                $output[] = $field;
            }
        }

        return implode( $delimiter, $output );
    }
    
    public function taxon($guid) {
        $this->data['page'] = 'taxon';
        $this->data['taxon'] = $this->censusmodel->getTaxon($guid);
        if (!$this->data['taxon']) {
            redirect('census');
        }
        $this->data['numbers'] = $this->censusmodel->countPlants(array('taxon_guid' => $guid));
        $plants = $this->censusmodel->findPlants(array('taxon_guid' => $guid));
        $accessions = array();
        $beds = array();
        $plantnos = array();
        foreach($plants as $plant) {
            $accessions[] = $plant['accession_number'];
            $beds[] = $plant['bed_name'];
            $plantnos[] = $plant['plant_number'];
        }
        array_multisort($beds, SORT_ASC, $accessions, SORT_ASC, $plantnos, SORT_ASC, $plants);
        $this->data['beds'] = $beds; 
        $this->data['beds_unique'] = array_unique($beds); 
        $this->data['plants'] = $plants;
        $this->data['cql_filter'] = "taxon_guid='$guid'";
        
        $this->data['regions'] = $this->censusmodel->getWGSRegions($guid);
        
        $this->load->view('detail_view', $this->data);
    }
    
    public function bed($guid) {
        if (!$guid)
            redirect(base_url());
        $start = 0;
        $rows = 100;
        if ($_SERVER['QUERY_STRING']) {
            $strbits = explode('&', $_SERVER['QUERY_STRING']);
            $terms = array();
            foreach ($strbits as $bit) {
                $parts = explode('=', $bit);
                $terms[$parts[0]] = ($parts[1]) ? $parts[1] : FALSE;
            }
            if (isset($terms['start']) && $terms['start'])
                $start = $terms['start'];
            if (isset($terms['rows']) && $terms['rows'])
                $rows = $terms['rows'];
        }
        
        $this->data['page'] = 'bed';
        $this->data['guid'] = $guid;
        $this->data['start'] = $start;
        $this->data['rows'] = $rows;
        $this->data['bed_info'] = $this->censusmodel->getBedInfo($guid);
        if (!$this->data['bed_info']) {
            redirect('census');
        }
        if (isset($this->data['bed_info']['subprecinct']) && !isset($this->data['bed_info']['bed'])) {
            $this->data['beds'] = $this->censusmodel->getBedsSubprecinct($guid);
        }
        $this->data['numbers'] = $this->censusmodel->countPlants(array('bed_guid' => $guid));
        if ($start > 0) {
            $plants = $this->censusmodel->findPlants(array('bed_guid' => $guid), $rows+1, $start-1);
            $shift = array_shift($plants);
            $this->data['plants'] = $plants;
            $this->data['previous_taxon'] = $shift['taxon_name'];
        }
        else {
            $this->data['plants'] = $this->censusmodel->findPlants(array('bed_guid' => $guid), $rows, $start);
            $this->data['previous_taxon'] = FALSE;
        }
        $this->data['cql_filter'] = "bed_guid='$guid'";
        
        $this->load->view('detail_view', $this->data);
    }
    
    public function grid($guid) {
        if (!$guid)
            redirect(base_url());
        $start = 0;
        $rows = 100;
        if ($_SERVER['QUERY_STRING']) {
            $strbits = explode('&', $_SERVER['QUERY_STRING']);
            $terms = array();
            foreach ($strbits as $bit) {
                $parts = explode('=', $bit);
                $terms[$parts[0]] = ($parts[1]) ? $parts[1] : FALSE;
            }
            if (isset($terms['start']) && $terms['start'])
                $start = $terms['start'];
            if (isset($terms['rows']) && $terms['rows'])
                $rows = $terms['rows'];
        }
        
        $this->data['page'] = 'grid';
        $this->data['guid'] = $guid;
        $this->data['start'] = $start;
        $this->data['rows'] = $rows;
        $this->data['grid_info'] = $this->censusmodel->getGridInfo($guid);
        $this->data['numbers'] = $this->censusmodel->countPlants(array('grid_guid' => $guid));
        if ($start > 0) {
            $plants = $this->censusmodel->findPlants(array('grid_guid' => $guid), $rows+1, $start-1);
            $shift = array_shift($plants);
            $this->data['plants'] = $plants;
            $this->data['previous_taxon'] = $shift['taxon_name'];
        }
        else {
            $this->data['plants'] = $this->censusmodel->findPlants(array('grid_guid' => $guid), $rows, $start);
            $this->data['previous_taxon'] = FALSE;
        }
        $this->data['cql_filter'] = "grid_guid='$guid'";
        $this->load->view('detail_view', $this->data);
    }
    
    public function accession($guid) {
        $this->data['page'] = 'accession';
        $this->data['guid'] = $guid;
        $this->data['accession_info'] = $this->censusmodel->getAccessionInfo($guid);
        if (!$this->data['accession_info']) {
            redirect('census');
        }
        $this->data['plant_info'] = $this->censusmodel->getPlantInfoByAccession($guid);
        $this->data['cql_filter'] = "accession_guid='$guid'";
        $this->load->view('detail_view', $this->data);
    }

    public function plant($guid) {
        $this->data['page'] = 'plant';
        $this->data['guid'] = $guid;
        $this->data['plant_info'] = $this->censusmodel->getPlantInfo($guid);
        if (!$this->data['plant_info']) {
            redirect('census');
        }
        $this->data['cql_filter'] = "plant_guid='$guid'";
        $this->load->view('detail_view', $this->data);
    }
    
    public function ajax($what) {
        if ($what == 'bed') {
            $data = $this->censusmodel->getBeds($this->input->get('location'));
            $json = json_encode($data);
            header('Content-type: application/json');
            echo $json;
        }
        exit;
    }

    private function queryString() {
        $qarray = explode('&', $_SERVER['QUERY_STRING']);
        $terms = array();
        foreach ($qarray as $item) {
            list($key, $value) = explode('=', $item);
            $terms[$key] = $value;
        }
        
        $start = 0;
        $rows = 100;
        $where = array();
        $qstring = array();
        $cql = array();
        if (isset($terms['taxon']) && $terms['taxon']) {
            $t = urldecode($terms['taxon']);
            $where['taxon'] = $t;
            $qstring[] = 'taxon=' . $terms['taxon'];
            $cql[] = "taxon_name LIKE '$t%'";
        }
        elseif (isset($terms['q']) && $terms['q']) {
            $t = urldecode($terms['q']);
            $where['taxon'] = $t;
            $qstring[] = 'taxon=' . $terms['q'];
            $cql[] = "taxon_name LIKE '$t%'";
        }
        if (isset($terms['common_name']) && $terms['common_name']) {
            $t = urldecode($terms['common_name']);
            $where['common_name'] = $t;
            $qstring[] = 'common_name=' . $terms['common_name'];
            $cql[] = "common_name LIKE '$t'";
        }
        if (isset($terms['family']) && $terms['family']) {
            $t = urldecode($terms['family']);
            $where['family'] = $t;
            $qstring[] = 'family=' . $terms['family'];
            $cql[] = "family='$t'";
        }
        if (isset($terms['location']) && $terms['location']) {
            $t = urldecode($terms['location']);
            $where['location'] = $t;
            $qstring[] = 'location=' . $terms['location'];
            $cql[] = "location='$t'";
        }
        if (isset($terms['precinct']) && $terms['precinct']) {
            $t = urldecode($terms['precinct']);
            $where['precinct'] = $t;
            $qstring[] = 'precinct=' . $terms['precinct'];
            $cql[] = "precinct='$t'";
        }
        if (isset($terms['bed']) && $terms['bed']) {
            $t = urldecode($terms['bed']);
            $where['bed'] = $t;
            $qstring[] = 'bed=' . $terms['bed'];
            $cql[] = "bed_name='$t'";
        }
        if (isset($terms['grid']) && $terms['grid']) {
            $where['grid'] = urldecode($terms['grid']);
            $qstring[] = 'grid=' . $terms['grid'];
            $cql[] = "grid_name='$terms[grid]'";
        }
        if (isset($terms['inclDeaccessioned']) && $terms['inclDeaccessioned']) {
            $inclDeaccessioned = 1;
            $qstring[] = 'inclDeaccessioned=1'; 
        }
        else {
            $inclDeaccessioned = 0;
            $cql[] = 'deaccessioned=0';
        }
        if (isset($terms['wgs_code']) && $terms['wgs_code']) {
            $t = urldecode($terms['wgs_code']);
            $where['wgs_code'] = $t;
            $qstring[] = 'wgs_code=' . $terms['wgs_code'];
            switch (strlen($t)) {
                case 1:
                    $cql[] = "wgs1_code='$t'";
                    break;
                case 2:
                    $cql[] = "wgs2_code='$t'";
                    break;
                case 3:
                    $cql[] = "wgs3_code='$t'";
                    break;
                case 6:
                    $cql[] = "wgs4_code='$t'";
                    break;
                default:
                    break;
            }
        }
        if (isset($terms['wgs_fullname']) && $terms['wgs_fullname']) {
            $t = urldecode($terms['wgs_fullname']);
            $wgs = $this->censusmodel->getWgsCode($t);
            if (!$wgs) {
                $this->data['message'] = "'$terms[wgs_fullname]' not found";
                $this->index();
            }
            $where['wgs_code'] = $wgs['code'];
            $qstring[] = 'wgs_code=' . $wgs['code'];
            $cql[] = "wgs{$wgs['level']}_code='$wgs[code]'";
        }
        
        if (isset($terms['start']) && $terms['start']) {
            $start = urldecode($terms['start']);
        }
        if (isset($terms['rows']) && $terms['rows']) {
            $rows = urldecode($terms['rows']);
        }
        if (isset($terms['provenance_type']) && $terms['provenance_type']) {
            $where['provenance_type'] = urldecode($terms['provenance_type']);
            $qstring[] = 'provenance_type=' . $terms['provenance_type'];
            $cql[] = "provenance_type_code='$terms[provenance_type]'";
        }
        if (isset($terms['identification_status']) && $terms['identification_status']) {
            $where['identification_status'] = urldecode($terms['identification_status']);
            $qstring[] = 'identification_status=' . $terms['identification_status'];
            if ($terms['identification_status'] == '2') {
                $cql[] = "identification_status IN ('2','2E','2L','2N','2X')";
            }
            else {
                $cql[] = "identification_status='$terms[identification_status]'";
            }
        }
        $order = 'taxon_name';
        if (isset($terms['order_results']) && $terms['order_results']) {
            $order = $terms['order_results'];
        }
        return (object) array(
            'where' => $where,
            'sort' => $order,
            'qstring' => $qstring,
            'cql' => $cql,
            'inclDeaccessioned' => $inclDeaccessioned,
            'start' => $start,
            'rows' => $rows
        );
    } 
    
}

/* End of file census.php */
/* Location: ./rbgcensus/controllers/census.php */