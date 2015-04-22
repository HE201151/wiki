<?php

include_once 'session.php';

class Error {
	public static $err = 'error';

	public static function get() {
		return getSession(self::$err);
	}

	public static function set($msg) {
		setSession(self::$err, $msg);
	}

	public static function alliswell() {
		setSession(self::$err, null);
	}

	public static function exception(Exception $e) {
		setSession(self::$err, $e->getMessage());
	}

	public static function none() {
		return empty(getSession(self::$err));
	}
}
?>