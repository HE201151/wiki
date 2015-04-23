<?php
	include_once 'db.php';
	include_once 'error.php';
	include_once 'hash.php';
	include_once 'mail.php';

class Register {
	private $username;
	private $password;
	private $email;

	// needed for querying existing values
	private $db;

	public static function getRegisterForm() {
		print '
		<form id="register" action="register.php" method="post" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">Registration</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td>Username:</td>
									</tr>
									<tr>
										<td colspan="2"><input type="text" name="name" id="name" maxlength="50" style="width: 100%" value="" /></td>
									</tr>
									<tr>
										<td><span class="smalltext">Password:</span></td>
										<td><span class="smalltext">Confirm Password:</span></td>
									</tr>
									<tr>
										<td><input type="password" name="password" id="password" maxlength="50" style="width: 100%" /></td>
										<td><input type="password" name="password2" id="password2" maxlength="50" style="width: 100%" /></td>
									</tr>
									<tr>
										<td><span class="smalltext"><label for="email">Email:</label></span></td>
										<td><span class="smalltext"><label for="email2">Confirm Email:</label></span></td>
									</tr>
									<tr>
										<td><input type="text" name="email" id="email" maxlength="50" style="width: 100%" value="" /></td>
										<td><input type="text" name="email2" id="email2" maxlength="50" style="width: 100%" value="" /></td>
									</tr>
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr>
			</tbody>
			</table>
			<br />
			<div id="submit" align="center">
				<input type="submit" name="Submit" value="Submit Registration!" />
			</div>
		</form>';
	}

	public static function getSuccessfulRegistrationMessage() {
		print '<div id="register">Registration was successful, please check your email !</div>';
	}

	/* XXX need error class */
	public function __construct() {
		try {
			$this->db = new db();
		} catch (Exception $e) {
			Error::exception($e);
			return;
		}

		try {
			$this->sanitizeUsername();
		} catch (Exception $e) {
			Error::exception($e);
			return;
		}

		try {
			$this->checkPassword();
		} catch (Exception $e) {
			Error::exception($e);
			return;
		}

		try {
			$this->checkEmail();
		} catch (Exception $e) {
			Error::exception($e);
			return;
		}

		try {
			$this->insertUser();
		} catch (Exception $e) {
			Error::exception($e);
			return;
		}

		Error::alliswell();
	}

	private function sanitizeUsername() {
		$this->username = htmlspecialchars(post('name'));
		$config = new Jason();
		$minSize = $config->get('login_min_size');
		$maxSize = $config->get('login_max_size');

		if (strlen($this->username) < $minSize) {
			throw new Exception('Username too short');
		}

		if (strlen($this->username) > $maxSize) {
			throw new Exception('Username too long');
		}

		// Check if username is already used
		$this->db->request('SELECT 1 from users where username = :username');
		$this->db->bind(':username', $this->username);
		$userExists = $this->db->getAssoc();
		if (!empty($userExists)) {
			throw new Exception('Username is already used');
		}
	}

	private function checkPassword() {
		if (post('password') !== post('password2')) {
			throw new Exception('Passwords do not match');
		}
		$this->password = Hash::get(post('password'));
	}

	private function checkEmail() {
		if (Mail::validateEmail(post('email'))) {
			$this->email = post('email');
		} else {
			throw new Exception('Invalid email address');
		}
		if (strcmp($this->email, post('email2')) !== 0) {
			throw new Exception('Emails do not match');
		}

		// Check if email is already used
		$this->db->request('SELECT 1 from users where mail = :mail');
		$this->db->bind(':mail', $this->email);
		$emailExists = $this->db->getAssoc();
		if (!empty($emailExists)) {
			throw new Exception('Email is already used');
		}
	}

	private function insertUser() {
		$this->db->request('INSERT into users (username, password, created, mail) VALUES (:username, :password, now(), :mail);');
		$this->db->bind(':username', $this->username);
		$this->db->bind(':password', $this->password);
		$this->db->bind(':mail', $this->email);
		$this->db->exec();
		$this->db = null;
	}
}


/* Submit registration form */
if (!empty($_POST)) {
	$newUser = new Register();
	/* if no errors, go to done page */
	if (Error::none()) {
		header("Location: index.php?page=registerDone");
	/* otherwise go back */
	} else {
		print '<script type="text/javascript">'
   			. 'history.go(-1);'
   			. '</script>';
	}
}

?>