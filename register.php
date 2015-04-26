<?php
	include_once 'db.php';
	include_once 'error.php';
	include_once 'hash.php';
	include_once 'mail.php';
	include_once 'user.php';

class Register {
	private $id;
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
		print '<div id="register">Registration was successful, please check your email.</div>';
	}

	public static function getSuccessfulActivationMessage() {
		print '<div id="register">User successfully activated, you can now log in.</div>';
	}

	public static function getWrongActivationMessage() {
		print '<div id="register">This code does not exist.</div>';
	}

	public function __construct() {
		try {
			$this->db = new db;
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
		$config = new Jason;
		$minSize = $config->get('login_min_size');
		$maxSize = $config->get('login_max_size');

		if (strlen($this->username) < $minSize) {
			throw new Exception('Username too short');
		}

		if (strlen($this->username) > $maxSize) {
			throw new Exception('Username too long');
		}

		// Check if username is already used
		$this->db->request('SELECT 1 FROM users WHERE username = :username');
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

		$pwd = post('password');
		
		$config = new Jason;

		if(strlen($pwd) < $config->get('pwd_min_size')) {
			throw new Exception("Password too short!");
		}

		if(strlen($pwd) > $config->get('pwd_max_size')) {
			throw new Exception("Password too long!");
		}

		if(!preg_match("#[0-9]+#", $pwd) ) {
			throw new Exception("Password must include at least one number!");
		}

		if(!preg_match("#[a-z]+#", $pwd) ) {
			throw new Exception("Password must include at least one letter!");
		}

		if(!preg_match("#[A-Z]+#", $pwd) ) {
			throw new Exception("Password must include at least one CAPS!");
		}

		$this->password = Hash::get($pwd);
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
		$this->db->request('SELECT 1 FROM users WHERE mail = :mail');
		$this->db->bind(':mail', $this->email);
		$emailExists = $this->db->getAssoc();
		if (!empty($emailExists)) {
			throw new Exception('Email is already used');
		}
	}

	private function getUserId() {
		$this->db->request('SELECT id from users WHERE username = :username');
		$this->db->bind(':username', $this->username);
		$result = $this->db->getAssoc();
		$this->id = $result['id'];
	}

	private function insertActivationCode() {
		$this->db->request('INSERT into activations (users_id, activationCode) VALUES (:id, :code);');
		$this->db->bind(':id', $this->id);
		$this->db->bind(':code', uniqid());
		$this->db->exec();
	}

	public function sendActivationMail() {
		$msg = 'Hello, please click on the following link to activate your account:<br />'
				. '<a href="index.php?page=activation&activation=' . $this->id .  '>link</a>';

		Mail::sendMail($this->email, Jason::getOnce("admin_mail"), 'Account activation', $msg);
	}

	private function insertUser() {
		/* insert user in users table */
		$this->db->request('INSERT into users (username, password, created, mail, status) VALUES (:username, :password, now(), :mail, :status);');
		$this->db->bind(':username', $this->username);
		$this->db->bind(':password', $this->password);
		$this->db->bind(':mail', $this->email);
		$this->db->bind(':status', UserStatus::Registered);
		$this->db->exec();

		$this->getUserId();

		$this->insertActivationCode();

		$this->sendActivationMail();
	}

	public function getId() {
		return $this->id;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getUsername() {
		return $this->username;
	}

	public static function activate() {
		$db = new db;
		$db->request('SELECT users_id, activationCode FROM activations WHERE activationCode = :code');
		$db->bind(':code', get('activation'));
		$result = $db->getAssoc();
		if (!empty($result)) {
			User::changeStatus($result['users_id'], UserStatus::User);
			self::getSuccessfulActivationMessage();
			$db->request('DELETE FROM activations where users_id = :user');
			$db->bind(':user', $result['users_id']);
			$db->exec();
		} else {
			self::getWrongActivationMessage();
		}
		$db = null;
	}
}


/* Submit registration form */
if (!empty($_POST)) {
	$newUser = new Register;
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