<?php

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Transaction extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('api/Transaction_model', 'trx');
		$this->load->helper('string');
	}

	public function index_get()
	{
		$this->response(array(
			'status' => FALSE,
			'message' => 'Nothing'
		), REST_Controller::HTTP_NOT_FOUND);
	}

	public function list_get(){

		$user_id = $this->get('user_id');

		if(empty($user_id)){
			$this->response(['status'=>FALSE, 'result'=>'user id tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if($user_id == 1){
			$result = $this->trx->get_transaction()->result();
		} else {
			$result = $this->trx->get_transaction(['user_id'=> $user_id])->result();
		}

		if(count($result) > 0){
			$this->set_response(['status'=>TRUE, 'result'=>$result], REST_Controller::HTTP_OK); 
		} else {
			$this->set_response(['status'=>TRUE, 'result'=>'Tidak ditemukan'], REST_Controller::HTTP_OK); 
		}
	}

	public function add_post(){

		$user_id = $this->post('user_id');
		$amount = $this->post('amount');
		$name = $this->post('fullname');
		$number = $this->post('number');
		$receipt = $this->post('receipt');

		if(empty($user_id)){
			$this->response(['status'=>FALSE, 'result'=>'user id tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if(empty($amount)){
			$this->response(['status'=>FALSE, 'result'=>'Nominal tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}


		$lastno = $this->trx->get_transaction(null,1)->row_array();

		if (date('Y', strtotime($lastno['transaction_created_at'])) < date('Y') OR (count($lastno)) == 0) {
			$nomor = sprintf('%04d', '0001');
			$no_trx = $nomor .'/AR-'. date('Ym');
		} else {
			$no = substr($lastno['transaction_no'], 0, 4);
			$nomor = sprintf('%04d', $no + 0001);
			$no_trx = $nomor .'/AR-'. date('Ym');
		}

		$params['transaction_no'] = $no_trx;
		$params['user_id'] = $user_id;
		$params['transaction_amount'] = $amount;
		$params['transaction_acc_name'] = $name;
		$params['transaction_acc_number'] = $number;

		if(!empty($receipt)){
			$image_file   	= time().rand(1111,9999).".png";
			$decoded_image = base64_decode($receipt);
			$upload_image 	= file_put_contents('./uploads/employee/'.$image_file, $decoded_image);

			if($upload_image === false){
				throw new Exception("Error uploading image");
			}
		}	

		$params['transaction_receipt'] = isset($image_file) ? $image_file : 'no_image.png';

		$this->trx->insert_transaction($params);

		$message['status'] = TRUE;
		$message['result'] = 'Transaksi berhasil, mohon tunggu konfirmasi berikutnya';
		$this->set_response($message, REST_Controller::HTTP_CREATED); 
	}

}

/* End of file Transaction.php */
/* Location: ./application/controllers/api/Transaction.php */