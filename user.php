<?php

include_once 'db.php';
include_once 'mail.php';
include_once 'hash.php';

abstract class UserStatus {
	const Administrator = "admin";
	const Moderator = "moderator";
	const Member = "member";
	const Reactivation = "reactivation";
	const ForgotPassword = "forgotpwd";
	const Frozen = "frozen";
	const Banned = "banned";
	const Registered = "registered";
	const Deregistered = "deregistered";

	const statusHierarchy = [ self::Administrator, self::Moderator, self::Member, self::Frozen, self::Banned, self::Deregistered ];

	const canLogin = [ self::Administrator, self::Moderator, self::Member, 
						self::Reactivation, self::Frozen ];

	const canContact = [ self::Administrator, self::Moderator, self::Member,
						self::Reactivation, self::ForgotPassword, self::Frozen ];

	const canChangePassword = [ self::Administrator, self::Moderator, self::Member,
								self::Frozen, self::Reactivation ];

	const canEditProfile = [ self::Administrator, self::Moderator, self::Member, self::Reactivation ];

	const canWiki = [ self::Administrator, self::Moderator, self::Member, self::ForgotPassword ];

	const profileRequest = 'SELECT id, avatar, username, status, activated, lastconnect FROM users ';
}

/* functions related to the current user in session. */
class SessionUser {
	public static function getUsername() {
		return Utils::getSession('username');
	}

	public static function getEmail() {
		return Utils::getSession('email');
	}

	public static function getStatus() {
		return Utils::getSession('status');
	}

	public static function isAdmin() {
		if (Utils::isLoggedin()) {
			return (Utils::in_array_any(self::getStatus(), [ UserStatus::Administrator ]));
		} else {
			return false;
		}
	}

	public static function canWiki() {
		return (Utils::in_array_any(self::getStatus(), UserStatus::canWiki));
	}

	public static function getUserId() {
		return Utils::getSession('user_id');
	}

	public static function getAvatar() {
		return Utils::getSession('avatar');
	}

	public static function getStatusDesc() {
		return Utils::arrayToString(self::getStatus());
	}
}

class User {
	private $db;
	private $uid;
	private $username;
	private $email;
	private $status;
	private $acivated;
	private $lastconnect;
	private $avatar;

	/*
	 * Get user information from uid
	 *
	 */
	// XXX uid = 0 does not work
	public function __construct($uid) {
		$this->db = new db;
		$this->db->request('SELECT avatar, username, status, activated, lastconnect, email FROM users WHERE id = :id');
		$this->db->bind(':id', $uid);
		$info = $this->db->getAssoc();

		if (!empty($info)) {
			$this->uid = $uid;
			$this->username = $info['username'];
			$this->email = $info['email'];
			$this->status = Utils::stringToArray($info['status']);
			$this->activated = $info['activated'];
			$this->lastconnect = $info['lastconnect'];
			$this->avatar = $info['avatar'];
		} else {
			throw new Exception("User not found.");
		}
	}

	public function getUsername() {
		return $this->username;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getUserId() {
		return $this->uid;
	}

	public function getAvatar() {
		return $this->avatar;
	}

	public function getStatusDesc() {
		return Utils::arrayToString($this->getStatus());
	}

	public static function isRegistered($status) {
		return (Utils::in_array_any($status, [ UserStatus::Registered ]));
	}

	public static function isForgotPassword($status) {
		return (Utils::in_array_any($status, [ UserStatus::ForgotPassword ]));
	}

	public static function isReactivation($status) {
		return (Utils::in_array_any($status, [ UserStatus::Reactivation ]));
	}

	public static function changeStatus($uid, $status) {
		$db = new db;
		$db->request('UPDATE users SET status = :status WHERE id = :id');
		$statusStr = Utils::arrayToString($status);
		$db->bind(':status', $statusStr);
		$db->bind(':id', $uid);
		$db->doquery();
		$db = null;
	}

	public static function toggleReactivation($uid, $curStatus) {
		$isReactivation = in_array(UserStatus::Reactivation, $curStatus);
		if ($isReactivation) {
			$status = [ reset($curStatus) ];
		} else {
			$status = [ $curStatus[0], UserStatus::Reactivation ];
		}

		self::changeStatus($uid, $status);
	}

	public static function toggleForgotPassword($uid, $curStatus) {
		$isForgotPassword = in_array(UserStatus::ForgotPassword, $curStatus);
		$isReactivation = in_array(UserStatus::Reactivation, $curStatus);
		if ($isForgotPassword && !$isReactivation) {
			$status = [ reset($curStatus) ];
		} else if ($isForgotPassword && $isReactivation) {
			$status = [ reset($curStatus), UserStatus::Reactivation ];
		} else if (!$isForgotPassword && $isReactivation) {
			$status = [ $curStatus[0], UserStatus::Reactivation, UserStatus::ForgotPassword ];
		} else {
			$status = [ $curStatus[0], UserStatus::ForgotPassword ];
		}

		self::changeStatus($uid, $status);

		return $status;
	}

	public static function getSecretQuestion($uid) {
		$db = new db;
		$db->request('SELECT secret_question FROM users WHERE id = :uid;');
		$db->bind(':uid', $uid);
		$result = $db->getAssoc();
		if (empty($result)) {
			throw new Exception('Failed to get secret question.');
		}
		$db = null;
		return $result['secret_question'];
	}

	public static function matchSecretQuestion($uid, $answer) {
		$db = new db;
		$db->request('SELECT 1 FROM users WHERE id = :uid AND secret_question_answer = :answer;');
		$db->bind(':uid', $uid);
		$db->bind('answer', $answer);
		$result = $db->getAssoc();
		if (empty($result)) {
			throw new Exception('Wrong answer.');
		}
		$db = null;
	}

	public static function canLogin($status) {
		return (Utils::in_array_any($status, UserStatus::canLogin));
	}

	public static function canContact($status) {
		return (Utils::in_array_any($status, UserStatus::canContact));
	}

	public static function canChangePassword($status) {
		return (Utils::in_array_any($status, UserStatus::canChangePassword));
	}

	public static function canEditProfile($status) {
		return (Utils::in_array_any($status, UserStatus::canEditProfile));
	}

	public static function getStatusFromActivationCode($code) {
		$db = new db;
		$db->request('SELECT status FROM users WHERE id = (SELECT users_id FROM activations WHERE activationCode = :code);');
		$db->bind(':code', $code);
		$result = $db->getAssoc();
		$db = null;
		return Utils::stringToArray($result['status']);
	}

	public static function getEmailFromUid($uid) {
		$db = new db;
		$db->request('SELECT email FROM users WHERE id = :id');
		$db->bind(':id', $uid);
		$result = $db->getAssoc();
		$db = null;
		return $result['email'];
	}

	public static function getStatusFromUid($uid) {
		$db = new db;
		$db->request('SELECT status FROM users WHERE id = :id');
		$db->bind(':id', $uid);
		$result = $db->getAssoc();
		$db = null;
		return Utils::stringToArray($result['status']);
	}

	public static function getAvatarFromUid($uid) {
		$db = new db;
		$db->request('SELECT avatar FROM users WHERE id = :id');
		$db->bind(':id', $uid);
		$result = $db->getAssoc();
		$db = null;
		return $result['avatar'];
	}

	public static function getUsersFromKey($key, $value, $exact) {
		$db = new db;

		$request = UserStatus::profileRequest;

		$match = $exact ? " = :value;" : " like concat('%', :value, '%');";

		if ($key === 'username') {
			$request .= 'WHERE ' . $key . $match;
		} else if ($key === 'email') {
			$request .= 'WHERE ' . $key . $match;
		} else if ($key === 'status') {
			$request .= 'WHERE ' . $key . $match;
		} else if (!empty($key) && !empty($value)) {
			throw new Exception('Wrong search key');
		}

		$db->request($request);
		$db->bind(':value', $value);
		return $db->getAllAssoc();
	}

	public static function validUsername($username) {
		$config = new Jason;
		$minSize = $config->get('login_min_size');
		$maxSize = $config->get('login_max_size');

		if (strlen($username) < $minSize) {
			throw new Exception('Username too short');
		}

		if (strlen($username) > $maxSize) {
			throw new Exception('Username too long');
		}

		// Check if username is already used
		$db = new db;
		$db->request('SELECT 1 FROM users WHERE username = :username');
		$db->bind(':username', $username);
		$userExists = $db->getAssoc();
		if (!empty($userExists)) {
			throw new Exception('Username is already used');
		}
		$db = null;
	}

	public static function updateUsername($uid, $username) {
		$db = new db;
		$db->request('UPDATE users SET username = :newusername WHERE id = :id;');
		$db->bind(':id', $uid);
		$db->bind(':newusername', $username);
		$db->doquery();
		$db = null;
	}

	public static function updatePassword($uid, $pwd) {
		$db = new db;
		$db->request('UPDATE users set password = :newpassword WHERE id = :id;');
		$db->bind(':id', $uid);
		$db->bind(':newpassword', Hash::get($pwd));
		$db->doquery();
		$db = null;
	}

	// XXX reactivation if change by admin ?
	public static function updateEmail($uid, $email) {
		$db = new db;

		// update email
		$db->request('UPDATE users SET email = :newmail WHERE id = :id');
		$db->bind(':id', $uid);
		$db->bind(':newmail', $email);
		$db->doquery();

		// insert new activation code
		$db->request('INSERT into activations (users_id, activationCode) VALUES (:id, :code);');
		$db->bind(':id', $uid);
		$db->bind(':code', uniqid());
		$db->doquery();

		// get new activation code
		$db->request('SELECT activationCode FROM activations WHERE users_id = :id');
		$db->bind(':id', $uid);
		$result = $db->getAssoc();
		$activationCode = $result['activationCode'];
		$db = null;

		User::toggleReactivation($uid, self::getStatusFromUid($uid));

		// send reactivation mail
		$msg = 'Hello, please click on the following link to reactivate your account:<br />'
				. '<a href="http://' . $_SERVER['SERVER_NAME'] . 
				dirname($_SERVER["REQUEST_URI"].'?').'/' .
				 'index.php?page=activation&activationCode=' . $activationCode .  '"">link</a>';

		Mail::sendMail($email, Jason::getOnce("admin_mail"), 'Account reactivation', $msg, true);
	}

	public static function updateAvatar($uid, $avatar) {
		$db = new db;
		$db->request('UPDATE users SET avatar = :avatar WHERE id = :id');
		$db->bind(':avatar', $avatar);
		$db->bind(':id', $uid);
		$db->doquery();
		$db = null;
	}

	public static function checkPassword($username, $pass) {
		$db = new db;
        $db->request('SELECT 1 FROM users WHERE username = :name AND password = :pass');
        $db->bind(':name', $username);
        $db->bind(':pass', Hash::get($pass));
        $result = $db->getAssoc();

        if (empty($result)) {
        	throw new Exception("Wrong Password");
        }
        $db = null;
	}

	public static function validateUsernameChangeForm() {
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
					password: "required"
				},
				messages: {
					username: {
						required: "Please enter a username",
						minLength: "Your username must consist of at least 2 characters",
						maxLength: "Your username must not exceed 25 characters"
					},
					password: "Please provide a password"
				}
			});
		});
		</script>';
	}

	public static function validatePasswordChangeForm() {
		$config = new Jason;
		$pwd_minlength = $config->get('pwd_min_size');
		$pwd_maxlength = $config->get('pwd_max_size');
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					password: "required",
					newpassword: {
						required: true,
						minlength: ' . $pwd_minlength . ',
						maxlength: ' . $pwd_maxlength . ',
						check_password: true
					},
					newpassword2: {
						required: true,
						equalTo: "#newpassword"
					},
				},
				messages: {
					password: "Please provide a password",
					newpassword: {
						required: "Please provide a password",
						minLength: "Your password must be at least ' . $pwd_minlength . ' characters",
						maxLength: "Your password must not exceed ' . $pwd_maxlength . ' characters",
						check_password: "Password must contain a number and an uppercase letter"
					},
					newpassword2: {
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

	public static function validateEmailChange() {
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					password: "required",
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
					password: "Please enter your password",
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
		});
		</script>';
	}

	public static function validateAvatarChange() {
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					password: "required"
				},
				messages: {
					password: "Please enter a username"
				}
			});
		});
		</script>';
	}

	public static function validateSearchForm() {
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					search: "required",
					select: "required"
				},
				messages: {
					search: "Please enter a search key",
					select: "Please choose what to search from"
				}
			});
		});
		</script>';
	}

	public static function getUsernameChangeForm($uid = "") {
		$get = $uid;
		if (!empty($uid)) {
			$get = '&uid=' . $uid;
		}
		print '<form id="register" action="post.php?action=changeUsername' . $get .'" method="post" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">User Login Change</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td>New Username:</td>
									</tr>
									<tr>
										<td colspan="2"><input type="text" name="username" id="username" maxlength="50" style="width: 100%" value="" required/></td>
									</tr>
									<tr> 
										<td>Password:</td>
									</tr>
									<tr>
										<td><input type="password" name="password" id="password" maxlength="50" style="width: 100%" required/></td>
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
				<input type="submit" name="Submit" value="Submit login change!" />
			</div>
			</form>';
		self::validateUsernameChangeForm();
	}

	public static function getPasswordChangeForm($uid = "") {
		$get = $uid;
		if (!empty($uid)) {
			$get = '&uid=' . $uid;
		}
		print '<form id="register" action="post.php?action=changePassword' . $get .'" method="post" method="post" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">User Password Change</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr> 
										<td>Current Password:</td>
									</tr>
									<tr>
										<td colspan="2"><input type="password" name="password" id="password" maxlength="50" style="width: 100%" required/></td>
									</tr>
									<tr>
										<td><span class="smalltext">New Password:</span></td>
										<td><span class="smalltext">Confirm New Password:</span></td>
									</tr>
									<tr>
										<td><input type="password" name="newpassword" id="newpassword" maxlength="50" style="width: 100%" required/></td>
										<td><input type="password" name="newpassword2" id="newpassword2" maxlength="50" style="width: 100%" required/></td>
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
				<input type="submit" name="Submit" value="Submit password change!" />
			</div>
			</form>';
		self::validatePasswordChangeForm();
	}

	public static function getEmailChangeForm($uid = "") {
		$get = $uid;
		if (!empty($uid)) {
			$get = '&uid=' . $uid;
		}
		print '<form id="register" action="post.php?action=changeEmail' . $get .'" method="post" method="post" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">User Email Change</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td>Password:</td>
									</tr>
									<tr>
										<td colspan="2"><input type="password" name="password" id="password" maxlength="50" style="width: 100%" required/></td>
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
				<input type="submit" name="Submit" value="Submit email change!" />
			</div>
		</form>';
		self::validateEmailChange();
	}

	public static function getAvatarChangeForm($uid = "") {
		$get = $uid;
		if (!empty($uid)) {
			$get = '&uid=' . $uid;
		}
		print '<form id="register" action="post.php?action=changeAvatar' . $get .'" method="post" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">User Avatar Change</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td>Password:</td>
									</tr>
									<tr>
										<td colspan="2"><input type="password" name="password" id="password" maxlength="50" style="width: 100%" required/></td>
									</tr>
									<tr>
										<td><span class="smalltext"><label for="avatar">Avatar:</label></span></td>
									</tr>
									<tr>
										<td><input type="file" name="avatar" id="avatar" required/></td>
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
				<input type="submit" name="Submit" value="Submit avatar change!" />
			</div>
		</form>';
		self::validateAvatarChange();
	}

	public static function checkNewPassword() {
		if (Utils::post('newpassword') !== Utils::post('newpassword2')) {
			throw new Exception('Passwords do not match');
		}

		$pwd = Utils::post('newpassword');
		
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
	}

	public static function checkNewEmail() {
		if (!Mail::validateEmail(Utils::post('email'))) {
			throw new Exception('Invalid email address');
		}
		if (strcmp(Utils::post('email'), Utils::post('email2')) !== 0) {
			throw new Exception('Emails do not match');
		}

		// Check if email is already used
		$db = new db;
		$db->request('SELECT 1 FROM users WHERE email = :email');
		$db->bind(':email', Utils::post('email'));
		$emailExists = $db->getAssoc();
		if (!empty($emailExists)) {
			throw new Exception('Email is already used');
		}
		$db = null;
	}

	public static function checkAvatar($uid) {
		$config = new Jason;
		$tmp = $_FILES['avatar']['tmp_name'];
		$imagePath = $config->get('avatar_path') . '/' . $uid . '_' . $_FILES['avatar']['name'];

		if ($_FILES['avatar']['size'] > $config->get('avatar_max_size')) {
			throw new Exception("File size too big, it must not exceed " . $config->get('avatar_max_size') . " bytes.");
		}

		if (file_exists($imagePath)) {
			throw new Exception("Filename already exists");
		} else {
			Utils::imageImport($tmp, $config->get('avatar_max_width'), $config->get('avatar_max_height'), $imagePath);
		}

		return $imagePath;
	}

	public static function registerQuestions($uid, $question, $answer) {
		$db = new db;
		$db->request('UPDATE users SET secret_question = :se, secret_question_answer = :sa WHERE id = :uid;');
		$db->bind(':se', $question);
		$db->bind(':sa', Hash::get($answer));
		$db->bind(':uid', $uid);
		$db->doquery();
		$db = null;
	}

	public static function getMemberList($userArray) {
		print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">Member List</td>
				</tr>
				<tr id="formcontent">
					<td>
						<table cellpadding="6" cellspacing="0" width=100%>
							<tbody>
							<tr>
								<td>Avatar</td>
								<td>Username</td>
								<td>Status</td>
								<td>Joined</td>
								<td>Last Visit</td>';

								foreach ($userArray as $link) {
									print  '<tr>
												<td class="trow"><a href="index.php?page=profile&uid=' . $link['id'] . '"><img src="' . $link['avatar'] . '" width=70 height=70 /></a></td>
												<td class="trow"><a href="index.php?page=profile&uid=' . $link['id'] . '">' . $link['username'] . '</a></td>
												<td class="trow">' . $link['status'] . '</td>
												<td class="trow">' . $link['activated'] . '</td>
												<td class="trow">' . $link['lastconnect'] . '</td>
											</tr>';
								}
							print '</tbody>
						</table>
					</td>
				</tr>
			</tbody>
			</table>';
	}

	public static function getSearchForm() {
		print '<form id="register" action="index.php?page=admin&action=listMembers" method="post">
		<table border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">Member Search</td>
				</tr>
				<tr id="formcontent">
					<td>
						<table cellpadding="6" cellspacing="0" width=100%>
							<tbody>
								<tr>
									<td colspan="2">Search</td>
									<td>What to search</td>
								</tr>
								<tr>
									<td colspan="2"><input type="text" name="search" id="search" maxlength="50" style="width: 100%" value="" required/></td>
									<td>
										<select id="select" name="select">
											<option value="username">username</option>
											<option value="email">email</option>
											<option value="status">status</option>
										</select>
									</td>
									<td><input type="checkbox" name="exact" value="exact"> exact match</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<br />
		<div id="submit" align="center">
			<input type="submit" name="Submit" value="search" />
		</div>
		</form>';
		print self::validateSearchForm();
	}

	public static function getSearchFailed() {
		print '<div id="register">Sorry, but no results were found.</div>';
	}

	public static function getMessageList() {
		$request = 'SELECT id, subject, email, user_id, parent_id, date, status FROM messages ';
		if (Utils::isGet('sort')) {
			switch (Utils::get('sort')) {
				case 'all' :
					break;
				case 'date_desc' :
					$request .= 'ORDER BY date DESC';
					break;
				case 'date_asc' :
					$request .= 'ORDER BY date ASC';
					break;

				case 'anon' :
					$request .= 'WHERE user_id is null';
					break;

				case 'member' :
					$request .= 'WHERE user_id is not null';
					break;

				case 'noreply' :
					$request .= 'WHERE status = 0';
					break;
			}
		}
		
		$db = new db;
		$db->request($request);
		$msgArray = $db->getAllAssoc();

		print '
        <table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
        <tbody>
            <tr>
                <td id="regtitle">
                	<span style="float: left;">Messages</span>
                	<span style="float: right;">
                		<span>Sort by / Filter :</span>
                		<span>
                			<form id="sort" action="index.php?page=admin&action=messages" method="get">
                				<input type="hidden" name="page"  value="admin">
								<input type="hidden" name="action" value="messages">
                				<select id="sortsearch" name="sort" onchange="this.form.submit()">
                					<option value="all">All messages</option>
                					<option value="date_asc">Date, ascending</option>
                					<option value="date_desc">Date, descending</option>
                					<option value="anon">Anonymous Messages</option>
                					<option value="member">Messages from members</option>
                					<option value="noreply">Message with no replies</option>
                				</select>
                			</form>
                		</span>
                	</span> 
                </td>
            </tr>
            <tr id="formcontent">
                <td>
                    <table cellpadding="6" cellspacing="0" width=100%>
                        <tbody>
                        <tr>
                            <td id="tcat">Member</td>
                            <td id="tcat">Subject</td>
                            <td id="tcat">Email</td>
                            <td id="tcat">Date</td>
                            <td id="tcat">Parent</td>
                            <td id="tcat">Status</td>';

                            foreach ($msgArray as $key => $value) {
                            	$db->request('SELECT username FROM users WHERE id = (SELECT user_id FROM messages WHERE user_id = :uid LIMIT 1);');
                            	$db->bind(':uid', $value['user_id']);
                            	$result = $db->getAssoc();
                                print  '<tr>
                                            <td class="trow"><a href="index.php?page=profile&uid=' . $value['user_id'] . '">' . $result['username'] . '</a></td>
                                            <td class="trow"><a href="index.php?page=contact&mid=' . $value['id'] . '">' . $value['subject'] . '</a></td>
                                            <td class="trow">' . $value['email'] . '</td>
                                            <td class="trow">' . $value['date'] . '</td>
                                            <td class="trowsmall"><a href="index.php?page=contact&mid=' . $value['id'] . '">' . $value['parent_id'] . '</a></td>
                                            <td class="trowsmall">' . Mail::getStatus($value['status']) . '</td>
                                        </tr>';
                            }
                        print '</tbody>
                    </table>
                </td>
            </tr>
        </tbody>
        </table>';

        if (Utils::isGet('sort')) {
        	print '<script>document.getElementById("sortsearch").value = "' . Utils::get('sort') .'";</script>';
        }

        $db = null;
	}

	public static function getAdmin() {
		if (!SessionUser::isAdmin(SessionUser::getStatus())) {
			print '<div id="register">You are not allowed to see this page.</div>';
			return;
		}

		if (Utils::isGet('action')) {
			switch (Utils::get('action')) {
				case 'listMembers' :
					$key = (Utils::isGet('select')) ? Utils::post('select') : "";
					$value = (Utils::isGet('search')) ? Utils::post('search') : "";
					$exact = Utils::isGet('exact');
					try {
						$userArray = self::getUsersFromKey($key, $value, $exact);
					} catch (Exception $e) {
						Error::exception($e);
						Utils::goBack();
					}
					if (count($userArray) < 1) {
						self::getSearchFailed();
						break;
					}
					self::getMemberList($userArray);
					break;

				case 'searchMembers' :
					self::getSearchForm();
					break;

				case 'settings' :
					$j = new Jason;
					$j->showConfig();
					break;

				case 'saveSettings' :
					$j = new Jason;
					foreach ($_POST as $key => $value) {
						$j->set($key, $value);
					}
					$j->writeFile();
					Error::set('Settings Saved.');
					header("Location: index.php?page=admin&action=settings");
					break;

				case 'messages' : 
					self::getMessageList();
					break;

				default :
					print 'invalid action.';
			}
		} else {
			print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
					<tr>
						<td id="regtitle">Administration</td>
					</tr>
					<tr>
						<tr id="formcontent">
							<td>
								<tr>
									<td><a href="index.php?page=admin&action=listMembers">Member List</a></td>
								</tr>
								<tr>
									<td><a href="index.php?page=admin&action=searchMembers">Search Members</a></td>
								</tr>
								<tr>
									<td><a href="index.php?page=admin&action=messages">Manage Messages</a></td>
								</tr>
								<tr>
									<td><a href="index.php?page=admin&action=settings">Manage Settings</a></td>
								</tr>
							</td>
						</tr>
					</tr>
				</tbody>
			</table>';
		}
	}

	public function showProfile() {
		print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">User Profile</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>';

									if (!empty($this->getAvatar())) {
										print '
									<tr>
										<td>Avatar:</td>
									</tr>
									<tr>
										<td><img src=" ' . $this->getAvatar() . '" /></td>
									</tr>';
									}
									print '
									<tr>
										<td>Username:</td>
									</tr>
									<tr>
										<td>' . $this->getUsername() . '</td>
									</tr>
									<tr> 
										<td>Email:</td>
									</tr>
									<tr>
										<td>' . $this->getEmail() . '</td>
									</tr>
									<tr>
										<td>Status:</td>
									</tr>
									<tr>
										<td>' . $this->getStatusDesc() . '</td>
									</tr>
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr> ';
				if (SessionUser::isAdmin(SessionUser::getStatus())) {
					print '
				<tr>
					<tr>
						<td id="regtitle">Edit User Profile</td>
					</tr>
					<tr id="formcontent">
						<td>
							<tr>
								<td><a href="index.php?page=profile&action=changeUsername&uid=' . $this->getUserid() . '">Change login</a></td>
							</tr>
							<tr>
								<td><a href="index.php?page=profile&action=changePassword&uid=' . $this->getUserid() . '">Change password</a></td>
							</tr>
							<tr>
								<td><a href="index.php?page=profile&action=changeEmail&uid=' . $this->getUserid() . '">Change email</a></td>
							</tr>
							<tr>
								<td><a href="index.php?page=profile&action=changeAvatar&uid=' . $this->getUserid() . '">Change avatar</a></td>
							</tr>
						</td>
					</tr>
				</tr>';
				}
				print '
			</tbody>
		</table>';
	}

	public static function getProfile() {
		if (!Utils::isLoggedin()) {
			print '<div id="register">You are either not logged in or do not have permission to view this page.</div>';
			return;
		}

		if (Utils::isGet('uid') && !(Utils::isGet('action'))) {
			try {
				$user = new User(Utils::get('uid'));
				$user->showProfile();
			} catch (Exception $e) {
				Error::exception($e);
			}
			return;
		}

		$get = !Utils::isGet('uid') ? "" : Utils::get('uid');
		if (Utils::isGet('action')) {
			switch (Utils::get('action')) {
				case "changeUsername" :
					self::getUsernameChangeForm($get);
					return;
				
				case "changePassword" :
					self::getPasswordChangeForm($get);
					return;

				case "changeEmail" :
					self::getEmailChangeForm($get);
					return;

				case "changeAvatar" : 
					self::getAvatarChangeForm($get);
					return;
			}
		}
		

		print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">User Profile</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>';

									if (!empty(SessionUser::getAvatar())) {
									print '<tr>
										<td>Avatar:</td>
									</tr>
									<tr>
										<td><img src=" ' . SessionUser::getAvatar() . '" /></td>
									</tr>';

									}
									print '
									<tr>
										<td>Username:</td>
									</tr>
									<tr>
										<td>' . SessionUser::getUsername() . '</td>
									</tr>
									<tr> 
										<td>Email:</td>
									</tr>
									<tr>
										<td>' . SessionUser::getEmail() . '</td>
									</tr>
									<tr>
										<td>Status:</td>
									</tr>
									<tr>
										<td>' . SessionUser::getStatusDesc() . '</td>
									</tr>
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr> ';
				if (self::canEditProfile(SessionUser::getStatus())) {
					print '
				<tr>
					<tr>
						<td id="regtitle">Edit User Profile</td>
					</tr>
					<tr id="formcontent">
						<td>
							<tr>
								<td><a href="index.php?page=profile&action=changeUsername">Change login</a></td>
							</tr>
							<tr>
								<td><a href="index.php?page=profile&action=changePassword">Change password</a></td>
							</tr>
							<tr>
								<td><a href="index.php?page=profile&action=changeEmail">Change email</a></td>
							</tr>
							<tr>
								<td><a href="index.php?page=profile&action=changeAvatar">Change avatar</a></td>
							</tr>
						</td>
					</tr>
				</tr> ';
			print '</tbody>
			</table>';
		}
	}
}
?>