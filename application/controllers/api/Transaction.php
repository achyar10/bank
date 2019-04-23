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
		$this->load->model('api/Bank_model', 'bank');
		$this->load->model('api/User_model', 'user');
		$this->load->helper('string');
	}

	public function index_get()
	{
		$this->response(array(
			'status' => FALSE,
			'message' => 'Nothing'
		), REST_Controller::HTTP_NOT_FOUND);
	}

	public function bank_get(){

		$bank = $this->bank->get_bank()->result_array();
		$this->set_response(['status'=>TRUE, 'result'=>$bank], REST_Controller::HTTP_OK);
	}

	public function list_get(){

		$user_id = $this->get('user_id');

		if(empty($user_id)){
			$this->response(['status'=>FALSE, 'result'=>'user id tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		$user = $this->user->get_user(['user_id'=>$user_id])->row();

		if($user->role_id == 1){
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
		$bank_id = $this->post('bank_id');

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

		$sub = substr($amount, -3);
		$sub2 = substr($amount, -2);
		$sub3 = substr($amount, -1);

		$total =  random_string('numeric', 3);
		$total2 =  random_string('numeric', 2);
		$total3 =  random_string('numeric', 1);

		if($sub == 0){
			$params['transaction_amount'] = $amount + $total;
		} elseif($sub2 == 0) {
			$params['transaction_amount'] = $amount + $total2;
		} elseif($sub3 == 0){
			$params['transaction_amount'] = $amount + $total3;
		} else {
			$params['transaction_amount'] = $sub;
		}

		$params['transaction_no'] = $no_trx;
		$params['user_id'] = $user_id;
		$params['bank_id'] = $bank_id;

		$this->trx->insert_transaction($params);

		$message['status'] = TRUE;
		$message['result'] = 'Transaksi berhasil, silahkan konfirmasi setoran';
		$this->set_response($params, REST_Controller::HTTP_CREATED); 
	}

	public function confirm_post(){

		$transaction_id = $this->post('transaction_id');
		$name = $this->post('fullname');
		$number = $this->post('number');
		$receipt = $this->post('receipt');

		if(empty($transaction_id)){
			$this->response(['status'=>FALSE, 'result'=>'id tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if(empty($name)){
			$this->response(['status'=>FALSE, 'result'=>'Nama pengirim tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if(empty($number)){
			$this->response(['status'=>FALSE, 'result'=>'Nomor rekening pengirim tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		$params['transaction_acc_name'] = $name;
		$params['transaction_acc_number'] = $number;
		$params['transaction_status'] = 1;

		if(!empty($receipt)){
			$image_file   	= time().rand(1111,9999).".png";
			$decoded_image = base64_decode($receipt);
			$upload_image 	= file_put_contents('./uploads/trx/'.$image_file, $decoded_image);

			if($upload_image === false){
				throw new Exception("Error uploading image");
			}
		}	

		$params['transaction_receipt'] = isset($image_file) ? $image_file : 'no_image.png';

		$this->trx->update_transaction($params, ['transaction_id'=>$transaction_id]);

		$message['status'] = TRUE;
		$message['result'] = 'konfirmasi berhasil, mohon tunggu konfirmasi berikutnya';
		$this->set_response($message, REST_Controller::HTTP_CREATED); 
	}

	function check_auto_get(){
		require APPPATH . 'libraries/src/CekBNI.php';
		$config = [
			'credential' => [
				'username' => 'achyaran1012',
				'password' => 'admin234'
			],
        'nomor_rekening' => '0400661273', //No. Rekening
        'range' => [
        	'tgl_akhir' => date('d-M-Y',strtotime(date('Y-m-d'))),
        	'tgl_awal' => date('d-M-Y',strtotime(date('Y-m-d')))
        ],
    ];

    $bni = new CekBNI($config);
    $tes = $bni->toArray();

    $res = array();
    foreach ($tes as $row) {
    	array_push($res, [
    		'mutasi' => $row[2],
    		'amount' => $this->idr($row[3]),
    		'saldo' => $this->idr($row[4])
    	]);
    }

    for ($i = 0; $i < count($res); $i++) {
    	if($res[$i]['mutasi'] != 'Db') {
    		$this->approve($res[$i]['amount']);
    	}
    }
}

function approve($amount){

	if($amount == null){
		echo 'Data tidak ditemukan';
	}

	$trx = $this->trx->get_transaction(['transaction_amount'=> $amount, 'transaction_status'=>0, 'DATE(transaction_created_at)'=>date('Y-m-d')])->row();

	if(count($trx) > 0){
		$this->user->update_saldo($trx->user_id, $trx->transaction_amount);
		$this->bank->update_bank($trx->bank_id, $trx->transaction_amount);

		$params['transaction_status'] = 2;
		$this->trx->update_transaction($params, ['transaction_amount'=>$amount, 'DATE(transaction_created_at)'=>date('Y-m-d')]);
		echo 'Approve Berhasil Rp. '.number_format($trx->transaction_amount).'<br>';
	} else {
		echo 'Tidak ada dana masuk <br>';
	}
}

function idr($nominal){
	$res = substr($nominal, 3,-3);
	$result = preg_replace("/[^0-9]/", "", $res);
	return $result;
}

}

/* End of file Transaction.php */
/* Location: ./application/controllers/api/Transaction.php */