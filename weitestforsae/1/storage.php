<?php

require_once('./logger.php');

class Storage {
	var $mmc;

	public function __construct() {
		$this->mmc = memcache_init();
	}

	public function set($key, $value) {
		$ret = true;
		if ($this->mmc) {
			$this->mmc->set($key, $value);
		} else {
			logger("Memcache init failed!\n");
			$ret = false;
		}

		return $ret;
	}

	public function get($key) {
		$value;
		if ($this->mmc) {
			$value = $this->mmc->get($key);
		} else {
			logger("Memcache connect failed!\n");
		}

		return $value;
	}
}

?>
