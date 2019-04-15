<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank_model extends CI_Model {

	function get_bank($arr = null, $limit = null, $offset = null){
		return $this->db->get_where('bank', $arr, $limit, $offset);
	}

	function update_bank($bank_id, $value){
		return $this->db->set('bank_saldo', "bank_saldo+$value" , FALSE)->where('bank_id', $cardno)
        ->update('bank');
	}

	

}

/* End of file Bank_model.php */
/* Location: ./application/models/api/Bank_model.php */