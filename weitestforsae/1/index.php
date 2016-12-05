<?php

require('./transmit.php');
require('./storage.php');

define("TOKEN", "weixin");

traceHttp();

$wechatProcessor = new WechatProcessor();
if (isset($_GET['echostr'])) {
	$wechatProcessor->valid();
} else {
	$wechatProcessor->responseMsg();
}

class WechatProcessor {
	var $transmitter;

	public function __construct() {
		$this->transmitter = new Transmit();
	}

	private function checkSignature() {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce = $_GET['nonce'];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}

	public function valid() {
		$echoStr = $_GET['echostr'];
		if ($this->checkSignature()) {
			echo $echoStr;
			exit;
		}
	}

	public function responseMsg() {
        if (!$this->checkSignature()) {
            echo "";
            logger("Check signature failed!\n");
            exit;
        }
        
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$msgType = trim($postObj->MsgType);
            logger("FromUserName:".$postObj->FromUserName."\nToUserName:".$postObj->ToUserName."\nMsgType:".$msgType);
			switch ($msgType) {
				case "event":
					$this->receiveEvent($postObj);
					break;
				case "text":
					$this->receiveText($postObj);
					break;
			}
		} else {
			echo "HTTP_RAW_POST_DATA is empty";
			exit;
		}
	}

	private function receiveEvent($object) {
		switch ($object->event) {
			case "subscribe":
				$content = "欢迎关注胡先东";
				break;
			default:
				break;
		}

		return $this->transmitter->transmitText($object, $content);
	}

	private function receiveText($object) {
		$keyword = trim($object->Content);
        logger("Receive Text, Content:".$keyword);
		if (!empty($keyword)) {
			if ($keyword == "?") {
				$content = date("Y-m-d H:i:s", time());
				$resultStr = $this->transmitter->transmitText($object, $content);
				echo $resultStr;
			} else {
				$url = "http://api.map.baidu.com/telematics/v3/weather?location=".urlencode($keyword)."&output=json&ak=3xZYGa8xA1ZfFoX5zbZUOHiA";
				$output = file_get_contents($url);
				logger("Weather Query Result:".$output);
				$content = json_decode($output, true);
				$resultStr = $this->transmitter->transmitNews($object, $content);
				echo $resultStr;
			}
		} else {
			echo "Input something...";
		}
	}
}

?>
