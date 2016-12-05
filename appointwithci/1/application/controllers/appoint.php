<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appoint extends CI_Controller {
	
	public function book() {
		$this->load->helper('url');
		$this->load->view('appoint_book');
	}

	public function check() {
		$this->load->helper('url');
		$this->load->view('welcome_message');
	}

}
