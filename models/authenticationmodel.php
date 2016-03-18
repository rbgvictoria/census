<?php
class AuthenticationModel extends CI_Model {
	function AuthenticationModel() {
		parent::__construct();
		$this->load->database();
	}
	
	function checkLogin() {
		$this->db->where('user_name', $this->input->post('username'));
		$this->db->where('passwd', md5($this->input->post('passwd')));
		$query = $this->db->get('rbgcensus.user');
		if($query->num_rows() > 0) {
			$row = $query->row();
			$session = array('id'=>$row->user_id,
				'name'=>$row->user_name,
				'firstname'=>$row->first_name,
				'surname'=>$row->last_name,
				'email'=>$row->email,
				'role'=>$row->role);
			$this->session->set_userdata($session);
			return true;
		} else return false;
	}
    
    public function checkUsername($username) {
        $this->db->select('user_id');
        $this->db->from('rbgcensus.user');
        $this->db->where('user_name', $username);
        $query = $this->db->get();
        if ($query->num_rows())
            return TRUE;
        else {
            return FALSE;
        }
    }
    
    public function createAccount($data) {
        $insertArray = array(
            'user_name' => $data['username'],
            'passwd' => md5($data['passwd']),
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'email' => $data['email'],
            'role' => 'User',
        );
        $this->db->insert('users', $insertArray);
        return $this->db->affected_rows();
    }
    
}
?>