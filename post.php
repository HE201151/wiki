<?php

include_once 'user.php';
include_once 'utils.php';
include_once 'error.php';
include_once 'mail.php';
include_once 'log.php';
include_once 'register.php';

if (!empty($_POST)) {
	switch (Utils::get('action')) {
		case 'changeUsername' :
			try {
				User::checkPassword(SessionUser::getUsername(), Utils::post('password'));
				User::validUsername(Utils::post('username'));
				if (Utils::isGet('uid')) {
					User::updateUsername(Utils::get('uid'), Utils::post('username'));
					$msg = 'Your username was changed.';
					Mail::sendMail(User::getEmailFromUid(Utils::get('uid')), Jason::getOnce("admin_mail"), 'Profile Change', $msg, false);
				} else {
					User::updateUsername(SessionUser::getUserId(), Utils::post('username'));
					Utils::setSession('username', Utils::post('username'));
				}
				Error::set('Username changed successfully.');
			} catch (Exception $e) {
				Error::exception($e);
			} finally {
				Utils::goBack();
			}
			break;

		case 'changePassword' :
			try {
				User::checkPassword(SessionUser::getUsername(), Utils::post('password'));
				User::checkNewPassword();
				if (Utils::isGet('uid')) {
					User::updatePassword(Utils::get('uid'), Utils::post('newpassword'));
					$msg = 'Your password was changed.';
					Mail::sendMail(User::getEmailFromUid(Utils::get('uid')), Jason::getOnce("admin_mail"), 'Profile Change', $msg, false);
				} else {
					User::updatePassword(SessionUser::getUserId(), Utils::post('newpassword'));
				} 
				Error::set('Password changed successfully.');
			} catch (Exception $e) {
				Error::exception($e);
			} finally {
				Utils::goBack();
			}
			break;

		case 'changeEmail' :
			try {
				User::checkPassword(SessionUser::getUsername(), Utils::post('password'));
				User::checkNewEmail();
				if (Utils::isGet('uid')) {
					User::updateEmail(Utils::get('uid'), Utils::post('email'));
					$msg = 'Your email was changed.';
					Mail::sendMail(User::getEmailFromUid(Utils::get('uid')), Jason::getOnce("admin_mail"), 'Profile Change', $msg, false);
					Error::set('Email changed successfully.');
				} else {
					User::updateEmail(SessionUser::getUserId(), Utils::post('email'));
					Utils::setSession('email', Utils::post('email'));
					Utils::setSession('status', [ reset(SessionUser::getStatus()), UserStatus::Reactivation ]);
					Error::set('Email changed successfully, please click on the reactivation link sent to the new email.');
				}
			} catch (Exception $e) {
				Error::exception($e);
			} finally {
				Utils::goBack();
			}
			break;

		case 'changeAvatar' :
			try {
				User::checkPassword(SessionUser::getUsername(), Utils::post('password'));
				if (Utils::isGet('uid')) {
					$imagePath = User::checkAvatar(Utils::get('uid'));
					User::updateAvatar(Utils::get('uid'), $imagePath);
					$msg = 'Your avatar was changed.';
					Mail::sendMail(User::getEmailFromUid(Utils::get('uid')), Jason::getOnce("admin_mail"), 'Profile Change', $msg, false);
				} else { 
					$imagePath =User::checkAvatar(SessionUser::getUserId());
					User::updateAvatar(SessionUser::getUserId(), $imagePath);
					Utils::setSession('avatar', $imagePath);
				}
				Error::set('Avatar changed sucessfully.');
			} catch (Exception $e) {
				Error::exception($e);
			} finally {
				Utils::goBack();
			}
			break;

		case 'message' : 
			try {
				if (Utils::isGet('mid')) {
					Mail::reply(Utils::get('mid'));
					header("Location: index.php?page=contact&mid=" . Utils::get('mid'));
				} else {
					$mail = new Mail;
					$mail->sendMailFromPost();
					header("Location: index.php?page=contactDone");
				}
			} catch (Exception $e) {
				Error::exception($e);
			}
			break;

		case 'login' :
			$log = new Log;
    		header("Location: index.php");
    		break;

    	case 'register' :
    		$newUser = new Register;
			if (Error::none()) {
				header("Location: index.php?page=registerDone");
			} else {
				Utils::goBack();
			}
			break;

		default : 
			print 'unknown action.';
	}
}

?>