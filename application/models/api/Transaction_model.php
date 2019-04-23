<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model {

	
	function get_transaction($arr=null, $limit=null, $offset=null){
		$this->db->order_by('transaction_created_at', 'desc');
		return $this->db->get_where('transaction', $arr, $limit, $offset);
	}

	function insert_transaction($data){
		return $this->db->insert('transaction', $data);
	}

	function update_transaction($data, $condition){
		return $this->db->update('transaction', $data, $condition);
	}
	

}

/* End of file Transaction_model.php */
/* Location: ./application/models/api/Transaction_model.php */