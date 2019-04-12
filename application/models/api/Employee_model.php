<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_model extends CI_Model {

	function get_employee($arr=null, $limit=null, $offset=null){
		return $this->db->get_where('employees', $arr, $limit, $offset);
	}

	function insert_employee($data){
		return $this->db->insert('employees', $data);
	}

	function update_employee($data, $condition){
		return $this->db->update('employees', $data, $condition);
	}

	

}

/* End of file Employee_model.php */
/* Location: ./application/models/api/Employee_model.php */