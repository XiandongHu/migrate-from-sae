<?php

require_once('./logger.php');

class Transmit {
	public function transmitText($object, $content) {
		if (!isset($content) || empty($content)) {
            logger("content is not set or empty!");
			return "";
		}
	
		$textTpl =
			"<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";
		return sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
	}

	public function transmitNews($object, $content) {
		if (!isset($content)) {
            logger("content is not set!");
			return "";
		}

		if ($content['error'] != 0 || $content['status'] != "success") {
			logger("city is not found!");
			return $this->transmitText($object, "没有找到该城市");
		}
        
        $results = $content['results'];
        if (!is_array($results) || count($results) == 0) {
            logger("weather data not found!");
			return $this->transmitText($object, "没有查到该城市的天气情况");
        }
        
        $result = $results[0];

		$itemTpl =
			"<item>
			<Title><![CDATA[%s]]></Title>
			<Description><![CDATA[%s]]></Description>
			<PicUrl>![CDATA[%s]]</PicUrl>
			<Url>![CDATA[]]</Url>
			</item>";
		$itemStr = "";

		$title = $result['currentCity']."天气预报 空气指数:".$result['pm25'];
		$description = "";
        $notices = $result['index'];
		foreach ($notices as $notice) {
			$description .= $notice['des']."\n";
		}
		$picUrl = "";
		$itemStr .= sprintf($itemTpl, $title, $description, $picUrl);

		$weathers = $result['weather_data'];
		foreach ($weathers as $weather) {
            $title = $weather['date']." ".$weather['weather']." ".$weather['wind']." ".$weather['temperature'];
            $description = "";
            $picUrl = $weather['dayPictureUrl'];
            logger("Title:".$title."\nDescription:".$description."\nPicUrl:".$picUrl);
			$itemStr .= sprintf($itemTpl, $title, $description, $picUrl);
		}

        $newsTpl =
			"<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[news]]></MsgType>
			<Content><![CDATA[]]></Content>
			<ArticleCount>%s</ArticleCount>
			<Articles>$itemStr</Articles>
			<FuncFlag>0</FuncFlag>
			</xml>";
		return sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($weathers) + 1);
	}
}

?>
