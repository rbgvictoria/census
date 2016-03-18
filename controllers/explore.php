<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Explore extends CI_Controller {
    function  __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
    }
    
    function index() {
        $this->load->view('explore_view');
    }
    

}

/* End of file explore.php */
/* Location: ./rbgcensus/controllers/explore.php */