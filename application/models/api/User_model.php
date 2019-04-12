<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	function get_user($arr=null, $limit=null, $offset=null){
		return $this->db->get_where('users', $arr, $limit, $offset);
	}

	function insert_user($data){
		return $this->db->insert('users', $data);
	}


}

/* End of file User_model.php */
/* Location: ./application/models/api/User_model.php */