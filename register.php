<?php

// XXX use functions from user.php
include_once 'db.php';
include_once 'log.php';
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
		print self::preSubmitValidation() . 
		'<form id="register" action="post.php?action=register" method="post" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">Registration - All fields required</td>
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
										<td colspan="2"><input type="text" name="username" id="username" maxlength="50" style="width: 100%" value="" required/></td>
									</tr>
									<tr>
										<td><span class="smalltext">Password:</span></td>
										<td><span class="smalltext">Confirm Password:</span></td>
									</tr>
									<tr>
										<td><input type="password" name="password" id="password_id" maxlength="50" style="width: 100%" required/></td>
										<td><input type="password" name="password2" id="password2" maxlength="50" style="width: 100%" required/></td>
									</tr>
									<tr>
										<td><span class="smalltext"><label for="email">Email:</label></span></td>
										<td><span class="smalltext"><label for="email2">Confirm Email:</label></span></td>
									</tr>
									<tr>
										<td><input type="text" name="email" id="email" maxlength="50" style="width: 100%" value="" required/></td>
										<td><input type="text" name="email2" id="email2" maxlength="50" style="width: 100%" value="" required/></td>
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

	public static function preSubmitValidation() {
		$config = new Jason;
		$username_minlength = $config->get('login_min_size');
		$username_maxlength = $config->get('login_max_size');
		$pwd_minlength = $config->get('pwd_min_size');
		$pwd_maxlength = $config->get('pwd_max_size');
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					username: {
						required: true,
						minlength: ' . $username_minlength . ',
						maxlength: ' . $username_maxlength . '
					},
					password: {
						required: true,
						minlength: ' . $pwd_minlength . ',
						maxlength: ' . $pwd_maxlength . ',
						check_password: true
					},
					password2: {
						required: true,
						equalTo: "#password_id"
					},
					email: {
						required: true,
						email: true
					},
					email2: {
						required: true,
						email: true,
						equalTo: "#email"
					}
				},
				messages: {
					username: {
						required: "Please enter a username",
						minLength: "Your username must consist of at least 2 characters",
						maxLength: "Your username must not exceed 25 characters"
					},
					password: {
						required: "Please provide a password",
						minLength: "Your password must be at least 6 characters",
						maxLength: "Your password must not exceed 64 characters",
						check_password: "Password must contain a number and an uppercase letter"
					},
					password2: {
						required: "Please confirm your password",
						equalTo: "The passwords do not match"
					},
					email: {
						required: "Please provide a email address",
						email: "Please enter a valid email address"
					},
					email2: {
						required: "Please confirm your email",
						equalTo: "The emails do not match"
					}
				}
			});

			$.validator.addMethod("check_password", function(value) {
			   return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) 
			       && /[A-Z]/.test(value) // uppercase letter
			       && /\d/.test(value) // number
			});
		});
		</script>';
	}

	public static function validateSecretQuestion() {
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					question: "required",
					answer: "required"
				},
				messages: {
					question: "Please fill in the secret question.",
					answer: "Please fill in the secret question answer."
				}
			});
		});
		</script>';
	}

	public static function validateSecretQuestionCheck() {
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					answer: "required"
				},
				messages: {
					answer: "Please fill in the secret question answer."
				}
			});
		});
		</script>';
	}

	public static function validateResetPassword() {
		$config = new Jason;
		$pwd_minlength = $config->get('pwd_min_size');
		$pwd_maxlength = $config->get('pwd_max_size');
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					password: {
						required: "Please provide a password",
						minlength: ' . $pwd_minlength . ',
						maxlength: ' . $pwd_maxlength . ',
						check_password: "Password must contain a number and an uppercase letter"
					},
					password2: {
						required: true,
						equalTo: "#password_id"
					},
				},
				messages: {
					password: {
						required: "Please provide a password",
						minLength: "Your password must be at least ' . $pwd_minlength . ' characters",
						maxLength: "Your password must not exceed ' . $pwd_maxlength . ' characters",
						check_password: "Password must contain a number and an uppercase letter"
					},
					password2: {
						required: "Please confirm your password",
						equalTo: "The passwords do not match"
					},
				}
			});
			$.validator.addMethod("check_password", function(value) {
			   return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) 
			       && /[A-Z]/.test(value) // uppercase letter
			       && /\d/.test(value) // number
			});
		});
		</script>';
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
		$this->username = htmlspecialchars(Utils::post('username'));
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
		if (Utils::post('password') !== Utils::post('password2')) {
			throw new Exception('Passwords do not match');
		}

		$pwd = Utils::post('password');
		
		$config = new Jason;

		if (strlen($pwd) < $config->get('pwd_min_size')) {
			throw new Exception("Password too short!");
		}

		if (strlen($pwd) > $config->get('pwd_max_size')) {
			throw new Exception("Password too long!");
		}

		if (!preg_match("#[0-9]+#", $pwd) ) {
			throw new Exception("Password must include at least one number!");
		}

		if (!preg_match("#[a-z]+#", $pwd) ) {
			throw new Exception("Password must include at least one letter!");
		}

		if (!preg_match("#[A-Z]+#", $pwd) ) {
			throw new Exception("Password must include at least one CAPS!");
		}

		$this->password = Hash::get($pwd);
	}

	private static function updatePassword($uid) {
		if (Utils::post('password') !== Utils::post('password2')) {
			throw new Exception('Passwords do not match');
		}

		$pwd = Utils::post('password');
		
		$config = new Jason;

		if (strlen($pwd) < $config->get('pwd_min_size')) {
			throw new Exception("Password too short!");
		}

		if (strlen($pwd) > $config->get('pwd_max_size')) {
			throw new Exception("Password too long!");
		}

		if (!preg_match("#[0-9]+#", $pwd) ) {
			throw new Exception("Password must include at least one number!");
		}

		if (!preg_match("#[a-z]+#", $pwd) ) {
			throw new Exception("Password must include at least one letter!");
		}

		if (!preg_match("#[A-Z]+#", $pwd) ) {
			throw new Exception("Password must include at least one CAPS!");
		}

		$db = new db;
		$db->request('UPDATE users SET password = :newpassword WHERE id = :uid;');
		$db->bind(':newpassword', Hash::get($pwd));
		$db->bind(':uid', $uid);
		$db->doquery();
		$db = null;
	}

	private function checkEmail() {
		if (Mail::validateEmail(Utils::post('email'))) {
			$this->email = Utils::post('email');
		} else {
			throw new Exception('Invalid email address');
		}
		if (strcmp($this->email, Utils::post('email2')) !== 0) {
			throw new Exception('Emails do not match');
		}

		// Check if email is already used
		$this->db->request('SELECT 1 FROM users WHERE email = :email');
		$this->db->bind(':email', $this->email);
		$emailExists = $this->db->getAssoc();
		if (!empty($emailExists)) {
			throw new Exception('Email is already used');
		}
	}

	private function getUserId() {
		$this->db->request('SELECT id FROM users WHERE username = :username');
		$this->db->bind(':username', $this->username);
		$result = $this->db->getAssoc();
		$this->id = $result['id'];
	}

	private static function insertActivationCode($uid) {
		$code = uniqid();
		$db = new db;
		$db->request('SELECT activationCode FROM activations WHERE users_id = :uid;');
		$db->bind(':uid', $uid);
		$result = $db->getAssoc();

		if (empty($result)) {
			$db->request('INSERT INTO activations (users_id, activationCode) VALUES (:id, :code);');
			$db->bind(':id', $uid);
			$db->bind(':code', $code);
			try {
				$db->doquery();
			} catch (Exception $e) {
				Error::exception($e);
			}
			$db = null;

			return $code;
		} else {
			return $result['activationCode'];
		}
	}

	public static function deleteActivationCode($uid) {
		$db = new db;
		$db->request('DELETE FROM activations WHERE users_id = :uid;');
		$db->bind(':uid', $uid);
		$db->doquery();
		$db = null;
	}

	public function getActivationCode() {
		$this->db->request('SELECT activationCode FROM activations WHERE users_id = :id');
		$this->db->bind(':id', $this->id);
		$result = $this->db->getAssoc();
		return $result['activationCode'];
	}

	public function sendActivationMail() {
		$msg = 'Hello, please click on the following link to activate your account:<br />'
				. '<a href="http://' . $_SERVER['SERVER_NAME'] . 
				dirname($_SERVER["REQUEST_URI"].'?').'/' .
				 'index.php?page=activation&activationCode=' . $this->getActivationCode() .  '"">link</a>';

		Mail::sendMail($this->email, Jason::getOnce("admin_mail"), 'Account activation', $msg, true);
	}

	public static function sendResetPasswordMail($email, $code) {
		$msg = 'Hello, please click on the following link to reset your password:<br />'
				. '<a href="http://' . $_SERVER['SERVER_NAME'] . 
				dirname($_SERVER["REQUEST_URI"].'?').'/' .
				 'index.php?page=resetpw&activationCode=' . $code .  '"">link</a>';

		Mail::sendMail($email, Jason::getOnce("admin_mail"), 'Password Reset', $msg, true);
	}

	private function insertUser() {
		/* insert user in users table */
		$this->db->request('INSERT INTO users (username, password, created, email, status) VALUES (:username, :password, now(), :email, :status);');
		$this->db->bind(':username', $this->username);
		$this->db->bind(':password', $this->password);
		$this->db->bind(':email', $this->email);
		$this->db->bind(':status', UserStatus::Registered);
		$this->db->doquery();

		$this->getUserId();

		self::insertActivationCode($this->id);

		$this->sendActivationMail();
	}

	public static function getSecretQuestionForm($uid, $code) {
		print '
		<form id="register" action="index.php?page=activation&activationCode=' . $code . '" method="POST">
			<input type="hidden" name="uid" value="' . $uid . '">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
			            <tr>
			                <td id="regtitle">Secret Question</td>
			            </tr>
			            <tr id="formcontent">
			                <td>
			                    <table cellpadding="6" cellspacing="0" width=100%>
			                        <tbody>
			                        	<tr>
			                        		Please fill in a secret question and answer, this will help you later if you forget your password.
			                        	</tr>
			                        	<tr>
											<td><label for="question">Secret Question:</label></td>
											<td><input id="question" class="input" name="question" type="text" value="" required /></td>
										</tr>
											<td><label for="answer">Secret Answer:</label></td>
											<td><input id="answer" class="input" name="answer" type="text" value="" required /></td>
										</tr>
									</tbody>
			                    </table>
			                </td>
			            </tr>
			    </tbody>
			</table>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="submit and activate me" />
			</div>
		</form>	';
		self::validateSecretQuestion();
	}

	public static function getSecretQuestionCheckForm($uid) {
		try {
			$question = User::getSecretQuestion($uid);
		} catch (Exception $e) {
			Error::exception($e);
		}

		print '
		<form id="register" action="index.php?page=resetpw" method="POST">
			<input type="hidden" name="uid" value="' . $uid . '">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
			            <tr>
			                <td id="regtitle">Please enter your secret question answer.</td>
			            </tr>
			            <tr id="formcontent">
			                <td>
			                    <table cellpadding="6" cellspacing="0" width=100%>
			                        <tbody>
			                        	<tr>
											<td><label for="question">Secret Question:</label></td>
											<td>' . $question . '</td>
										</tr>
											<td><label for="answer">Secret Answer:</label></td>
											<td><input id="answer" class="input" name="answer" type="text" value="" required /></td>
										</tr>
									</tbody>
			                    </table>
			                </td>
			            </tr>
			    </tbody>
			</table>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="submit" />
			</div>
		</form>	';
		self::validateSecretQuestionCheck();
	}

	public static function getResetPasswordForm($uid) {
		print '
		<form id="register" action="index.php?page=resetpw" method="POST">
			<input type="hidden" name="uid" value="' . $uid . '">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
			            <tr>
			                <td id="regtitle">Password Reset</td>
			            </tr>
			            <tr id="formcontent">
			                <td>
			                    <table cellpadding="6" cellspacing="0" width=100%>
			                        <tbody>
			                        	<tr>
											<td><span class="smalltext">Password:</span></td>
											<td><span class="smalltext">Confirm Password:</span></td>
										</tr>
										<tr>
											<td><input type="password" name="password" id="password_id" maxlength="50" style="width: 100%" required/></td>
											<td><input type="password" name="password2" id="password2" maxlength="50" style="width: 100%" required/></td>
										</tr>
									</tbody>
			                    </table>
			                </td>
			            </tr>
			    </tbody>
			</table>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="submit" />
			</div>
		</form>	';
		self::validateResetPassword();
	}

	public static function resetPassword() {
		if (isset($_POST['uid']) && isset($_POST['answer'])) {
			try {
				User::matchSecretQuestion(Utils::post('uid'), Hash::get(Utils::post('answer')));
			} catch (Exception $e) {
				Error::exception($e);
				Utils::goBack();
				return;
			}
			self::getResetPasswordForm(Utils::post('uid'));

			return;
		}
		if (isset($_POST['uid']) && isset($_POST['password'])) {
			try {
				$db = new db;
				$db->request('SELECT id, status FROM users WHERE id = :uid;');
				$db->bind(':uid', Utils::post('uid'));
				$result = $db->getAssoc();
				if (empty($result)) {
					print '<div id="register">This user does not exist.</div>';
					return;
				}
				// update password
				self::updatePassword(Utils::post('uid'));
				// remove 'forgotpw' status
				User::toggleForgotPassword(Utils::post('uid'), Utils::stringToArray($result['status']));
				// also remove 'reactivation' status 
				if (User::isReactivation(Utils::stringToArray($result['status']))) {
					User::toggleReactivation(Utils::post('uid'), Utils::stringToArray($result['status']));
				}

				// delete activation code
				$db->request('DELETE FROM activations WHERE users_id = :uid;');
				$db->bind(':uid', Utils::post('uid'));
				$db->doquery();
				$db = null;
			} catch (Exception $e) {
				Error::exception($e);
				return;
			}
			print '<div id="register">Password updated.</div>';
			// auto log the user
			Log::autolog(Utils::post('uid'));
			header("Location: index.php");
			return;
		}
		if (!isset($_POST['email'])) {
			if (isset($_GET['activationCode'])) {
				$db = new db;
				$db->request('SELECT users_id, activationCode FROM activations WHERE activationCode = :code');
				$db->bind(':code', Utils::get('activationCode'));
				$result = $db->getAssoc();
				$db = null;
				if (empty($result)) {
					print '<div id="register">Invalid code.</div>';
					return;
				}
				self::getSecretQuestionCheckForm($result['users_id']);
			} else {
				print '
				<form id="register" action="index.php?page=resetpw" method="POST">
					<input type="hidden" name="uid" value="">
					<table border="0" cellspacing="0" cellpadding="6" class="tborder">
						<tbody>
					            <tr>
					                <td id="regtitle">Password Reset</td>
					            </tr>
					            <tr id="formcontent">
					                <td>
					                    <table cellpadding="6" cellspacing="0" width=100%>
					                        <tbody>
					                        	<tr>
													<td><span class="smalltext">Email:</span></td>
												</tr>
												<tr>
													<td><input type="text" name="email" id="email" maxlength="50" style="width: 100%" value="" required/></td>
												</tr>
											</tbody>
					                    </table>
					                </td>
					            </tr>
					    </tbody>
					</table>
					<div align="center" id="submit">
						<input id="submit_button" type="submit" value="submit" />
					</div>
				</form>	';
			}
		} else {
			$db = new db;
			$db->request('SELECT id, status, email FROM users WHERE email = :email;');
			$db->bind(':email', Utils::post('email'));
			$result = $db->getAssoc();
			if (empty($result)) {
				print '<div id="register">No user with this email exists.</div>';
				return;
			}

			if (User::canChangePassword(Utils::stringToArray($result['status']))) {
				$code = self::insertActivationCode($result['id']);
				self::sendResetPasswordMail($result['email'], $code);
				$status = User::toggleForgotPassword($result['id'], Utils::stringToArray($result['status']));
				print '<div id="register">A link to reset your password has been sent to your email.';
			} else {
				print '<div id="register">This user can not reset the password, please contact the administrator.</div>';
			}
			$db = null;
		}
	}

	public static function activate() {
		if (isset($_POST['question']) && isset($_POST['answer']) && isset($_POST['uid'])) {
			User::registerQuestions(Utils::post('uid'), Utils::post('question'), Utils::post('answer'));
		}

		$db = new db;
		$db->request('SELECT users_id, activationCode FROM activations WHERE activationCode = :code');
		$db->bind(':code', Utils::get('activationCode'));
		$result = $db->getAssoc();
		if (!empty($result)) {
			$status = User::getStatusFromActivationCode($result['activationCode']);
			$uid = $result['users_id'];
			
			if (!User::isRegistered($status)) {
				User::toggleReactivation($result['users_id'], $status);
			} else {
				if (isset($_POST['question'])) {
					User::changeStatus($uid, [ UserStatus::Member ]);
				}
			}

			if ($uid === SessionUser::getUserId()) {
				Utils::setSession('status', $status);
			}

			// if first activation
			if (User::isRegistered($status) && !isset($_POST['question'])) {
				self::getSecretQuestionForm($uid, $result['activationCode']);
				return;
			} else {
				// delete activation code
				$db->request('DELETE FROM activations WHERE users_id = :user');
				$db->bind(':user', $uid);
				$db->doquery();

				// add activation date to user
				$db->request('UPDATE users SET activated=now() WHERE id = :user');
				$db->bind(':user', $uid);
				$db->doquery();
			}
			self::getSuccessfulActivationMessage();
		} else {
			self::getWrongActivationMessage();
		}
		$db = null;
	}
}

?>