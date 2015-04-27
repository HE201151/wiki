<?php

include_once 'db.php';
include_once 'hash.php';

abstract class UserStatus {
	const Administrator = 0;
	const Moderator = 1;
	const Member = 2;
	const Reactivation = 3;
	const ForgotPassword = 4;
	const Frozen = 5;
	const Banned = 6;
	const Registered = 7;
	const Deregistered = 8;

	const canConnect = [ self::Administrator, self::Moderator, self::User, 
						self::Reactivation, self::Frozen ];

	const canContact = [ self::Administrator, self::Moderator, self::User,
						self::Reactivation, self::ForgotPassword, self::Frozen ];

	const canChangePassword = [ self::Administrator, self::Moderator, self::User,
								self::Froze, self::Reactivation ];
}

class User {
	public static function changeStatus($id, $status) {
		$db = new db;
		$db->request('UPDATE users SET status = :status where id = :id');
		$db->bind(':status', $status);
		$db->bind(':id', $id);
		$db->doquery();
		$db = null;
	}

	public static function canConnect() {
		return array_key_exists(self::getStatus(), UserStatus::canConnect);
	}

	public static function canContact() {
		return array_key_exists(self::getStatus(), UserStatus::canContact);
	}

	public static function canChangePassword() {
		return array_key_exists(self::getStatus(), UserStatus::canChangePassword);
	}

	public static function getUsername() {
		return Utils::getSession('username');
	}

	public static function getEmail() {
		return Utils::getSession('mail');
	}

	public static function getStatus() {
		return Utils::getSession('status');
	}

	public static function getUserId() {
		return Utils::getSession('user_id');
	}

	public static function getStatusDesc() {
		switch (self::getStatus()) {
			case UserStatus::Administrator :
				$v = "Administrator";
				break;
			case UserStatus::Moderator :
				$v = "Moderator";
				break;
			case UserStatus::Member :
				$v = "Member";
				break;
			case UserStatus::Reactivation :
				$v = "Member in reactivation";
				break;
			case UserStatus::ForgotPassword :
				$v = "Member forgot password";
				break;
			case UserStatus::Frozen : 
				$v = "Frozen member";
				break;
			case UserStatus::Banned : 
				$v = "Banned member";
				break;
			case UserStatus::Registered :
				$v = "Registered";
				break;
			case UserStatus::Deregistered :
				$v = "Deregistered";
				break;
			default :
				$v = "Invalid status";
		}

		return $v;
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
	}

	public static function updateUsername($username) {
		$db = new db;
		$db->request('UPDATE users SET username = :newusername WHERE username = :oldusername;');
		$db->bind(':oldusername', self::getUsername());
		$db->bind(':newusername', $username);
		$db->doquery();
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

	public static function getProfile() {
		if (!Utils::isLoggedin()) {
			print '<div id="register">You are either not logged in or do not have permission to view this page.</div>';
			return;
		}

		if (Utils::isGet('action')) {
			if (Utils::get('action') === "changeLogin") {
				self::getLoginChangeForm();
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
								<tbody>
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
				</tr>
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
				</tr>
			</tbody>
			</table>';
	}
}
?>