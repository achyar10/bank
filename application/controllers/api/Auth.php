<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Auth extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('api/User_model', 'user');
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

		$params['user_email'] = $email;
		$params['user_password'] = password_hash($password, PASSWORD_DEFAULT);
		$params['user_fullname'] = $fullname;
		$this->user->insert_user($params);
		$message['status'] = TRUE;
		$message['message'] = 'Add Success';
		$this->set_response($message, REST_Controller::HTTP_CREATED); 
	}

	function login_post(){
		$email = $this->post('email');
		$password = $this->post('password');

		$user = $this->user->get_user(array('user_email'=>$email))->row();

		if(!empty($user) && password_verify($password, $user->user_password)){
			$this->set_response($user, REST_Controller::HTTP_OK); 
		} else {
			$this->response(array(
				'status' => FALSE,
				'message' => 'email or password wrong'
			), REST_Controller::HTTP_BAD_REQUEST);
		}
	}

}

/* End of file Auth.php */
/* Location: ./application/controllers/api/Auth.php */