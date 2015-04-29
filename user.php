<?php

include_once 'db.php';
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

	const canLogin = [ self::Administrator, self::Moderator, self::Member, 
						self::Reactivation, self::Frozen ];

	const canContact = [ self::Administrator, self::Moderator, self::Member,
						self::Reactivation, self::ForgotPassword, self::Frozen ];

	const canChangePassword = [ self::Administrator, self::Moderator, self::Member,
								self::Froze, self::Reactivation ];

	const canEditProfile = [ self::Administrator, self::Moderator, self::Member, self::Reactivation ];
}

class User {
	public static function changeStatus($id, $status) {
		$db = new db;
		$db->request('UPDATE users SET status = :status WHERE id = :id');
		$statusStr = Utils::arrayToString($status);
		$db->bind(':status', $statusStr);
		$db->bind(':id', $id);
		$db->doquery();
		$db = null;

		Utils::setSession("status", $status);
	}

	public static function toggleReactivation($id, $curStatus) {
		$isReactivation = in_array(UserStatus::Reactivation, $curStatus);
		if ($isReactivation) {
			$status = [ reset($curStatus) ];
		} else {
			$status = [ $curStatus[0], UserStatus::Reactivation ];
		}

		self::changeStatus($id, $status);
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

	public static function getUsername() {
		return Utils::getSession('username');
	}

	public static function getEmail() {
		return Utils::getSession('email');
	}

	public static function getStatus() {
		return Utils::getSession('status');
	}

	public static function getStatusFromActivationCode($code) {
		$db = new db;
		$db->request('SELECT status FROM users WHERE id = (SELECT users_id from activations WHERE activationCode = :code);');
		$db->bind(':code', $code);
		$result = $db->getAssoc();
		$db = null;
		return Utils::stringToArray($result['status']);
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

	public static function updateUsername($username) {
		$db = new db;
		$db->request('UPDATE users SET username = :newusername WHERE username = :oldusername;');
		$db->bind(':oldusername', self::getUsername());
		$db->bind(':newusername', $username);
		$db->doquery();
		$db = null;
	}

	public static function updatePassword($pwd) {
		$db = new db;
		$db->request('UPDATE users set password = :newpassword WHERE password = :oldpassword;');
		$db->bind(':oldpassword', Hash::get(Utils::post('password')));
		$db->bind(':newpassword', Hash::get($pwd));
		$db->doquery();
		$db = null;
	}

	public static function updateEmail($email) {
		$db = new db;

		// update email
		$db->request('UPDATE users SET mail = :newmail WHERE mail = :oldmail');
		$db->bind(':oldmail', self::getEmail());
		$db->bind(':newmail', $email);
		$db->doquery();

		// insert new activation code
		$db->request('INSERT into activations (users_id, activationCode) VALUES (:id, :code);');
		$db->bind(':id', User::getUserId());
		$db->bind(':code', uniqid());
		$db->doquery();

		// get new activation code
		$db->request('SELECT activationCode FROM activations WHERE users_id = :id');
		$db->bind(':id', User::getUserId());
		$result = $db->getAssoc();
		$activationCode = $result['activationCode'];
		$db = null;

		User::toggleReactivation(self::getUserId(), self::getStatus());

		// send reactivation mail
		$msg = 'Hello, please click on the following link to reactivate your account:<br />'
				. '<a href="http://' . $_SERVER['SERVER_NAME'] . 
				dirname($_SERVER["REQUEST_URI"].'?').'/' .
				 'index.php?page=activation&activationCode=' . $activationCode .  '"">link</a>';

		Mail::sendMail($email, Jason::getOnce("admin_mail"), 'Account reactivation', $msg, true);
	}

	public static function updateAvatar() {
		$db = new db;
		$db->request('UPDATE users SET avatar = :avatar WHERE id = :id');
		$db->bind(':avatar', self::getAvatar());
		$db->bind(':id', self::getUserId());
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

	public static function validateLoginChangeForm() {
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

	public static function getLoginChangeForm() {
		print '<form id="register" action="post.php?action=changeLogin" method="post" accept-charset="UTF-8">
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
		self::validateLoginChangeForm();
	}

	public static function getPasswordChangeForm() {
		print '<form id="register" action="post.php?action=changePassword" method="post" accept-charset="UTF-8">
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

	public static function getEmailChangeForm() {
		print '<form id="register" action="post.php?action=changeEmail" method="post" accept-charset="UTF-8">
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

	public static function getAvatarChangeForm() {
		print '<form id="register" action="post.php?action=changeAvatar" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
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
		$db->request('SELECT 1 FROM users WHERE mail = :mail');
		$db->bind(':mail', Utils::post('email'));
		$emailExists = $db->getAssoc();
		if (!empty($emailExists)) {
			throw new Exception('Email is already used');
		}
		$db = null;
	}

	public static function checkAvatar() {
		$config = new Jason;
		$tmp = $_FILES['avatar']['tmp_name'];
		$imagePath = $config->get('avatar_path') . '/' . self::getUserId() . '_' . $_FILES['avatar']['name'];

		if ($_FILES['avatar']['size'] > $config->get('avatar_max_size')) {
			throw new Exception("File size too big, it must not exceed " . $config->get('avatar_max_size') . "bytes.");
		}

		if (file_exists($imagePath)) {
			throw new Exception("Filename already exists");
		} else {
			Utils::imageImport($tmp, $config->get('avatar_max_width'), $config->get('avatar_max_height'), $imagePath);
		}

		Utils::setSession('avatar', $imagePath);
	}

	public static function getProfile() {
		if (!Utils::isLoggedin()) {
			print '<div id="register">You are either not logged in or do not have permission to view this page.</div>';
			return;
		}

		if (Utils::isGet('action')) {
			switch (Utils::get('action')) {
				case "changeLogin" :
					self::getLoginChangeForm();
					return;
				
				case "changePassword" :
					self::getPasswordChangeForm();
					return;

				case "changeEmail" :
					self::getEmailChangeForm();
					return;

				case "changeAvatar" : 
					self::getAvatarChangeForm();
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

									if (!empty(User::getAvatar())) {
									print '<tr>
										<td>Avatar:</td>
									</tr>
									<tr>
										<td><img src=" ' . User::getAvatar() . '" /></td>
									</tr>';

									}
									print '
									<tr>
										<td>Username:</td>
									</tr>
									<tr>
										<td>' . self::getUsername() . '</td>
									</tr>
									<tr> 
										<td>Email:</td>
									</tr>
									<tr>
										<td>' . self::getEmail() . '</td>
									</tr>
									<tr>
										<td>Status:</td>
									</tr>
									<tr>
										<td>' . self::getStatusDesc() . '</td>
									</tr>
								</tbody>
							</table>
						</fieldset>
					</td>
				</tr> ';
				if (self::canEditProfile(self::getStatus())) {
					print '
				<tr>
					<tr>
						<td id="regtitle">Edit User Profile</td>
					</tr>
					<tr id="formcontent">
						<td>
							<tr>
								<td><a href="index.php?page=profile&action=changeLogin">Change login</a></td>
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