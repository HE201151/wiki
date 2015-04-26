<?php	
	session_start();

	function isLoggedIn() {
		return (getSession('is_logged_in'));
	}

	function isPost($field) {
		return (isset($_POST[$field]) && !empty($_POST[$field]));
	}

	function isGet($field) {
		return (isset($_GET[$field]) && !empty($_GET[$field]));
	}

	function isSession($field) {
		return (isset($_SESSION[$field]) && !empty($_SESSION[$field]));
	}

	function post($field) {
		return $_POST[$field];
	}

	function get($field) {
		return $_GET[$field];
	}

	function getSession($field) {
		if (isSession($field))
			return $_SESSION[$field];
	}

	function setSession($field, $value) {
		$_SESSION[$field] = $value;
	}

	function handleUsers() {
		if (isPost('username')) {
			if (isPost('password'))
				return $_POST['username'];
			else
				return 'no password set';
		} else {
			return 'no info';
		}
	}

	function page() {
		if (isGet('page')) {
			return get('page');
		}
	}
?>
