<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class User extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
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

	public function register_post(){
		$email = $this->post('email');
		$password = $this->post('password');
		$fullname = $this->post('fullname');

		if(empty($email)){
			$this->response(['status'=>FALSE, 'result'=>'Email tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if(empty($password)){
			$this->response(['status'=>FALSE, 'result'=>'Password tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if(empty($fullname)){
			$this->response(['status'=>FALSE, 'result'=>'Nama Lengkap tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		$check_user = $this->user->get_user(array('user_email'=>$email))->row_array();

		if(count($check_user)>0){
			$this->response(['status'=>FALSE, 'result'=>'Email sudah terdaftar'], REST_Controller::HTTP_BAD_REQUEST);
		}

		$token = random_string('alnum', 32);
		$params['user_email'] = $email;
		$params['user_password'] = password_hash($password, PASSWORD_DEFAULT);
		$params['user_fullname'] = $fullname;
		$params['role_id'] = 2;
		$params['user_token'] = $token;

		$this->user->insert_user($params);
		$message['status'] = TRUE;
		$message['result'] = 'Register Berhasil, Silahkan cek email untuk aktivasi';
		$this->set_response($message, REST_Controller::HTTP_CREATED); 
	}

	public function login_post(){
		$email = $this->post('email');
		$password = $this->post('password');

		if(empty($email)){
			$this->response(['status'=>FALSE, 'result'=>'Email tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		if(empty($password)){
			$this->response(['status'=>FALSE, 'result'=>'Password tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		$check_user = $this->user->get_user(array('user_email'=>$email))->row();

		if(count($check_user) > 0){
			$result = array();
			array_push($result, [
				'user_id' => $check_user->user_id,
				'user_email' => $check_user->user_email,
				'user_password' => $check_user->user_password,
				'user_fullname' => $check_user->user_fullname,
				'user_saldo' => $check_user->user_saldo,
				'role_id' => $check_user->role_id
			]);
			if(!empty($check_user) && password_verify($password, $check_user->user_password)){
				$this->set_response(['status'=>TRUE, 'result'=>$result], REST_Controller::HTTP_OK); 
			} else {
				$this->response(['status'=>FALSE, 'result'=>'Kata sandi salah'], REST_Controller::HTTP_BAD_REQUEST);
			}

		} else {
			$this->response(['status'=>FALSE, 'result'=>'Email tidak terdaftar'], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function check_saldo_get(){
		$user_id = $this->get('user_id');

		if(empty($user_id)){
			$this->response(['status'=>FALSE, 'result'=>'User tidak boleh kosong'], REST_Controller::HTTP_BAD_REQUEST);
		}

		$user = $this->user->get_user(['user_id'=>$user_id])->row();
		$res['saldo'] = $user->user_saldo;
		if(count($user) > 0) {
			$this->set_response(['status'=>TRUE, 'result'=>$res], REST_Controller::HTTP_OK); 
		} else {
			$this->set_response(['status'=>TRUE, 'result'=>'Tidak ditemukan'], REST_Controller::HTTP_BAD_REQUEST); 
		}
	}

}

/* End of file User.php */
/* Location: ./application/controllers/api/User.php */