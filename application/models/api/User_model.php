<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	function get_user($arr=null, $limit=null, $offset=null){
		return $this->db->get_where('user', $arr, $limit, $offset);
	}

	function insert_user($data){
		return $this->db->insert('user', $data);
	}

	function update_user($data, $condition){
		return $this->db->insert('user', $data, $condition);
	}


}

/* End of file User_model.php */
/* Location: ./application/models/api/User_model.php */