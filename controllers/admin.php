<?php
/**
 * @property CI_Loader $load
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 */

class Admin extends CI_Controller {
// a class to allow users to log in and set a session var to say they
// are logged in.
    var $data;
    
    function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('captcha');
        $this->output->enable_profiler(false);
        $this->load->model('authenticationmodel');
    }

    function index($message="") {
        $this->login($message);
    }
    
    function login($message="") {
        if (isset($_SERVER['HTTP_REFERER']) && substr($_SERVER['HTTP_REFERER'], 0, strlen(base_url())) == base_url())
            $this->data['referer'] = $_SERVER['HTTP_REFERER'];
        else
            $this->data['referer'] = FALSE;
        $this->load->view('login', $this->data);
    }

    function authenticate(){
    // do the authenticate stuff here:
    if($this->input->post('username') && $this->input->post('passwd')){
                    if($this->authenticationmodel->checkLogin())
            if ($this->input->post('referer')){
                redirect($this->input->post('referer'));
            }
            else 
                redirect(site_url());
        else $message = 'Authentication failed';
        $this->load->view('message', array("message" => $message));
    }
    else 
        $this->load->view('message', array('message' => "Username or password not filled in"));
    }

    function logout(){
        // unset the session variables, then destroy the session
        $unset = array('id'=>'', 'name'=>'', 'firstname'=>'', 'surname'=>'', 'email'=>'', 'role'=>'');
        $this->session->unset_userdata($unset);
        //$this->session->sess_destroy();
        if (isset($_SERVER['HTTP_REFERER']) && substr($_SERVER['HTTP_REFERER'], 0, strlen(base_url())) == base_url()) 
            redirect($_SERVER['HTTP_REFERER']);
        else 
            redirect(site_url());
    }
}
?>
