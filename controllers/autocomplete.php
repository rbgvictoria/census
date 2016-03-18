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
    
    function autocomplete_taxonname() {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getTaxa($q);
        echo json_encode($items);
    }
    
    function autocomplete_family() {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getFamilies($q);
        echo json_encode($items);
    }
    
    function autocomplete_common_name() {
        if (empty($_GET['term'])) exit;
        $q = strtolower($_GET['term']);
        $items = $this->autocompletemodel->getCommonNames($q);
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