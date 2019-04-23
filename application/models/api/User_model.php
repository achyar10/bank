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

	function update_saldo($user_id, $value){
		return $this->db->set('user_saldo', "user_saldo+$value" , FALSE)->where('user_id', $user_id)
        ->update('user');
	}


}

/* End of file User_model.php */
/* Location: ./application/models/api/User_model.php */