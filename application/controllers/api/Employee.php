<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Employee extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('api/User_model', 'user');
		$this->load->model('api/Employee_model', 'employee');
	}

	public function index_get()
	{
		$this->response(array(
			'status' => FALSE,
			'message' => 'Nothing'
		), REST_Controller::HTTP_NOT_FOUND);
	}

	public function register_post(){
		$nik = $this->post('nik');
		$email = $this->post('email');
		$password = $nik;
		$fullname = $this->post('fullname');
		$position_id = $this->post('position_id');
		$photo = $this->post('photo');

		$eNik = $this->employee->get_employee(array('employee_nik'=>$nik))->row_array();
		$eMail = $this->employee->get_employee(array('employee_email'=>$email))->row_array();

		if(count($eNik)>0){
			$this->response('NIK already used', REST_Controller::HTTP_BAD_REQUEST);
		}

		if(count($eMail)>0){
			throw new Exception('Email already used');
		}

		$params['employee_nik'] = $nik;
		$params['employee_email'] = $email;
		$params['employee_password'] = password_hash($password, PASSWORD_DEFAULT);
		$params['employee_fullname'] = $fullname;
		$params['position_id'] = $position_id;

		if(!empty($photo)){
			$image_file   	= time().rand(1111,9999).".png";
			$decoded_image = base64_decode($photo);
			$upload_image 	= file_put_contents('./uploads/employee/'.$image_file, $decoded_image);

			if($upload_image === false){
				throw new Exception("Error uploading image");
			}
		}	

		$params['employee_photo'] = isset($image_file) ? $image_file : 'no_image.png';

		$this->employee->insert_employee($params);
		$message['status'] = TRUE;
		$message['message'] = 'Add Employee Success';
		$this->set_response($message, REST_Controller::HTTP_CREATED); 
	}

}

/* End of file Employee.php */
/* Location: ./application/controllers/api/Employee.php */