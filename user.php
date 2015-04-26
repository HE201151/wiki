<?php

abstract class UserStatus {
	const Administrator = 0;
	const Moderator = 1;
	const User = 2;
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
		$db->exec();
		$db = null;
	}

	public static function canConnect($status) {
		return array_key_exists($status, UserStatus::canConnect);
	}

	public static function canContact($status) {
		return array_key_exists($status, UserStatus::canContact);
	}

	public static function canChangePassword($status) {
		return array_key_exists($status, UserStatus::canChangePassword);
	}
}
?>