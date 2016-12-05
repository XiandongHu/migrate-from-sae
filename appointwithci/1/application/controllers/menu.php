<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu extends CI_Controller {
	
	public function create() {
		$wechat =& load_class('Wechat');
		$appid = $this->input->get('appid');
		$secret = $this->input->get('secret');
		if (!$appid) {
			$appid = "wx9f77b67d38629be3";
		}
		if (!$secret) {
			$secret = "2604369782a68565ecf1363fd4b1576c";
		}
		$menu = '{
			"button":[
				{
					"name":"我的故事",
					"sub_button":[
						{
							"type":"click",
							"name":"简介",
							"key":"V1_RESUME"
						},
						{
							"type":"click",
							"name":"八卦",
							"key":"V1_NEWS"
						},
						{
							"type":"click",
							"name":"联系我",
							"key":"V1_CONTACT"
						}
					]
				},
				{
					"name":"预约系统",
					"sub_button":[
						{
							"type":"view",
							"name":"预订",
							"url":"http://appointwithci.applinzi.com/appoint/book"
						},
						{
							"type":"view",
							"name":"查看",
							"url":"http://appointwithci.applinzi.com/appoint/check"
						}
					]
				}
			]
		}';
	log_message('debug', "appid=".$appid.", secret=".$secret);
		$this->response_data($wechat->create_menu($appid, $secret, $menu));
	}

	private function response_data($data) {
		if ($data) {
			$this->output->set_header('Content-Type: application/json; charset=utf-8');
			echo json_encode($data);
		} else {
			echo 'Response data is empty';
		}
	}

}
