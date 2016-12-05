<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Wechat {
	
	protected $_token = 'weixin';
	
	public function valid($signature, $timestamp, $nonce, $echo_str) {
		if ($this->_check_signature($signature, $timestamp, $nonce)) {
			echo $echo_str;
			exit;
		}
	}

	public function response_msg($signature, $timestamp, $nonce, $post_data) {
        if (!$this->_check_signature($signature, $timestamp, $nonce)) {
            log_message('error', "Check signature failed!");
            exit;
        }
        
		if ($post_data) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
			$post_obj = simplexml_load_string($post_data, 'SimpleXMLElement', LIBXML_NOCDATA);
			$msg_type = trim($post_obj->MsgType);
            log_message('debug', "FromUserName:".$post_obj->FromUserName.", ToUserName:".$post_obj->ToUserName.", msg_type:".$msg_type);
			switch ($msg_type) {
				case "event":
					$this->_receive_event($post_obj);
					break;
				case "text":
					$this->_receive_text($post_obj);
					break;
			}
		} else {
            log_message('error', "Post data missing!");
			exit;
		}
	}

	private function _check_signature($signature, $timestamp, $nonce) {
		$tmp_array = array($this->_token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmp_array, SORT_STRING);
		$tmp_str = implode($tmp_array);
		$tmp_str = sha1($tmp_str);
		if ($tmp_str == $signature) {
			return true;
		} else {
			return false;
		}
	}

	private function _receive_event($object) {
		switch ($object->event) {
			case "subscribe":
				$content = "欢迎关注胡先东";
				break;
			default:
				break;
		}

		return $this->_transmit_text($object, $content);
	}

	private function _receive_text($object) {
		$keyword = trim($object->Content);
        log_message('debug', "Receive Text, Content:".$keyword);
		if (!empty($keyword)) {
			if ($keyword == "?") {
				$content = date("Y-m-d H:i:s", time());
				$result= $this->_transmit_text($object, $content);
				echo $result;
			} else if ($keyword == "demo") {
				$content = "http://appointwithci.applinzi.com/demos";
				$result= $this->_transmit_text($object, $content);
				echo $result;
			} else if ($keyword == "预约") {
				$content = "http://appointwithci.applinzi.com/appoint/book";
				$result= $this->_transmit_text($object, $content);
				echo $result;
			} else if ($keyword == "查看") {
				$content = "http://appointwithci.applinzi.com/appoint/check";
				$result= $this->_transmit_text($object, $content);
				echo $result;
			} else {
				$url = "http://api.map.baidu.com/telematics/v3/weather?location=".urlencode($keyword)."&output=json&ak=3xZYGa8xA1ZfFoX5zbZUOHiA";
				$output = file_get_contents($url);
				log_message('debug', "Weather Query Result:".$output);
				$content = json_decode($output, true);
				$result= $this->_transmit_news($object, $content);
				echo $result;
			}
		} else {
			log_message('error', "Input is empty!");
		}
	}

	private function _transmit_text($object, $content) {
		if (!isset($content) || empty($content)) {
            log_message('error', "Content is not set or empty!");
			return "";
		}
	
		$text_tpl =
			"<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";
		return sprintf($text_tpl, $object->FromUserName, $object->ToUserName, time(), $content);
	}

	private function _transmit_news($object, $content) {
		if (!isset($content)) {
            log_message('error', "Content is not set!");
			return "";
		}

		if ($content['error'] != 0 || $content['status'] != "success") {
			log_message('debug', "City is not found!");
			return $this->_transmit_text($object, "没有找到该城市");
		}
        
        $results = $content['results'];
        if (!is_array($results) || count($results) == 0) {
            log_message('debug', "Weather data not found!");
			return $this->_transmit_text($object, "没有查到该城市的天气情况");
        }
        
        $result = $results[0];

		$item_tpl =
			"<item>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<PicUrl>![CDATA[%s]]</PicUrl>
			<Url>![CDATA[]]</Url>
			</item>";
		$item_str = "";

		$title = $result['currentCity']."天气预报 空气指数:".$result['pm25'];
		$description = "";
        $notices = $result['index'];
		foreach ($notices as $notice) {
			$description .= $notice['des']."\n";
		}
		$pic_url = "";
		$item_str .= sprintf($item_tpl, $title, $description, $pic_url);

		$weathers = $result['weather_data'];
		foreach ($weathers as $weather) {
            $title = $weather['date']." ".$weather['weather']." ".$weather['wind']." ".$weather['temperature'];
            $description = "";
            $pic_url = $weather['dayPictureUrl'];
            log_message('debug', "Title:".$title.", Description:".$description.", PicUrl:".$pic_url);
			$item_str .= sprintf($item_tpl, $title, $description, $pic_url);
		}

        $news_tpl =
			"<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[news]]></MsgType>
			<Content><![CDATA[]]></Content>
			<ArticleCount>%s</ArticleCount>
			<Articles>$item_str</Articles>
			<FuncFlag>0</FuncFlag>
			</xml>";
		return sprintf($news_tpl, $object->FromUserName, $object->ToUserName, time(), count($weathers) + 1);
	}

	public function create_menu($appid, $secret, $menu) {
		$access_token = $this->get_access_token($appid, $secret);
		if ($access_token) {
			$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
			return $this->http_request($url, $menu);
		} else {
			log_message('error', "Get access token failed!");
		}
	}

	private function get_access_token($appid, $secret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
		$output = json_decode(file_get_contents($url), true);
		if ($output['access_token']) {
			return $output['access_token'];
		} else {
			return null;
		}
	}

	private function http_request($url, $data = null) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

}
