<?php

abstract class UserStatus {
	const Administrator = 0;
	const Moderator = 1;
	const User = 2;
	const Reactivation = 3;
	const ForgotPassword = 4;
	const FrozenUser = 5;
	const BannerUser = 6;
	const Registered = 7;
	const Deregistered = 8;
}


class User {
	public static function elevate($id, $status) {
		$db = new db;
		$db->request('UPDATE users SET status = :status where id = :id');
		$db->bind(':status', $status);
		$db->bind(':id', $id);
		$db->exec();
	}
}
?>