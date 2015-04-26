<?php

include_once 'utils.php';

class Error {
	public static $err = 'error';

	public static function get() {
		return Utils::getSession(self::$err);
	}

	public static function set($msg) {
		Utils::setSession(self::$err, $msg);
	}

	public static function alliswell() {
		Utils::setSession(self::$err, null);
	}

	public static function exception(Exception $e) {
		Utils::setSession(self::$err, $e->getMessage());
	}

	public static function none() {
		return empty(Utils::getSession(self::$err));
	}
}
?>