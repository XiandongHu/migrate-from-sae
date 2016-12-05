<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Entry extends CI_Controller {
	
	public function index() {
		$wechat =& load_class('Wechat');
		$signature = $this->input->get('signature');
		$timestamp = $this->input->get('timestamp');
		$nonce = $this->input->get('nonce');
		$echo_str = $this->input->get('echostr');
		if ($echo_str) {
			$wechat->valid($signature, $timestamp, $nonce, $echo_str);
		} else {
			$post_data = file_get_contents("php://input");
			$wechat->response_msg($signature, $timestamp, $nonce, $post_data);
		}
	}

}
