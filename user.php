<?php

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
		return getSession('username');
	}

	public static function getEmail() {
		return getSession('mail');
	}

	public static function getStatus() {
		return getSession('status');
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

	public static function getProfile() {
		if (!isLoggedin()) {
			print '<div id="register">You are either not logged in or do not have permission to view this page.</div>';
			return;
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
			</tbody>
			</table>';
	}
}
?>