<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller {
    function  __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->model('censusmodel');
    }
    
    function bed() {
        $data = $this->censusmodel->getBedsJSON($this->input->get('location'), $this->input->get('access_key'));
        $json = json_encode($data);
        header('Content-type: application/json');
        echo $json;
    }

    function precinct() {
        $data = $this->censusmodel->getPrecinctsJSON($this->input->get('location'));
        $json = json_encode($data);
        header('Content-type: application/json');
        echo $json;
    }
    
    function find_by_accession_number($accessionNo, $plantNo=FALSE) {
        $data = $this->censusmodel->findByAccessionPlantNumber($accessionNo, $plantNo);
        $json = json_encode($data);
        header('Access-Control-Allow-Origin: *');  
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
        header('Content-type: application/json');
        echo $json;
    }
    
    function tree_removal_request() {
        $this->load->model('treeremovalmodel');
        $insert = $this->treeremovalmodel->insertTreeRemovalRecord($this->input->post());
        $json = json_encode($insert);
        $this->jsonHeaders();
        echo $json;
    }
    
    private function jsonHeaders() {
        header('Access-Control-Allow-Origin: *');  
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
        header('Content-type: application/json');
    }

}

/* End of file ajax.php */
/* Location: ./rbgcensus/controllers/ajax.php */