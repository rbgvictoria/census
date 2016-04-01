<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AutoComplete extends CI_Controller {

    var $data;

    function  __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        $this->output->enable_profiler(FALSE);
        $this->load->model('autocompletemodel');
    }
    
    function autocomplete_taxonname($access_key=FALSE) {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getTaxa($q, $access_key);
        echo json_encode($items);
    }
    
    function autocomplete_taxonname_explore() {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getTaxaForExplore($q);
        echo json_encode($items);
    }
    
    function autocomplete_family($access_key=FALSE) {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getFamilies($q, $access_key);
        echo json_encode($items);
    }
    
    function autocomplete_common_name($access_key=FALSE) {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getCommonNames($q, $access_key);
        echo json_encode($items);
    }
    
    function autocomplete_wgs() {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getWgsFullName($q);
        echo json_encode($items);
    }
    
}

/* End of file autocomplete.php */
/* Location: ./rbgcensus/controllers/autocomplete.php */