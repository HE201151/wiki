<?php	

include_once 'jason.php';

// access session variables
session_name(Jason::getOnce('session_name'));
session_start();

class Utils {
	public static function isLoggedIn() {
		return (self::getSession('is_logged_in'));
	}

	public static function isPost($field) {
		return (isset($_POST[$field]) && !empty($_POST[$field]));
	}

	public static function isGet($field) {
		return (isset($_GET[$field]) && !empty($_GET[$field]));
	}

	public static function isSession($field) {
		return (isset($_SESSION[$field]) && !empty($_SESSION[$field]));
	}

	public static function post($field) {
		return $_POST[$field];
	}

	public static function get($field) {
		return $_GET[$field];
	}

	public static function getSession($field) {
		if (self::isSession($field))
			return $_SESSION[$field];
	}

	public static function setSession($field, $value) {
		$_SESSION[$field] = $value;
	}

	public static function handleUsers() {
		if (self::isPost('username')) {
			if (self::isPost('password'))
				return $_POST['username'];
			else
				return 'no password set';
		} else {
			return 'no info';
		}
	}

	public static function page() {
		if (self::isGet('page')) {
			return self::get('page');
		}
	}
}
?>
