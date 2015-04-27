<?php

include_once 'user.php';
include_once 'utils.php';
include_once 'error.php';
include_once 'mail.php';
include_once 'log.php';
include_once 'register.php';

if (!empty($_POST)) {
	switch (Utils::get('action')) {
		case 'changeLogin' :
			try {
				User::checkPassword(User::getUsername(), Utils::post('password'));
				User::validUsername(Utils::post('username'));
				User::updateUsername(Utils::post('username'));
				Utils::setSession('username', Utils::post('username'));
				Error::set('Username changed successfully.');
			} catch (Exception $e) {
				Error::Exception($e);
			} finally {
				print '<script type="text/javascript">'
						. 'history.go(-1);'
						. '</script>';
			}
			break;


		case 'message' : 
			try {
				$mail = new Mail();
				$mail->sendMailFromPost();
				header("Location: index.php?page=contactDone");
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
			/* if no errors, go to done page */
			if (Error::none()) {
				header("Location: index.php?page=registerDone");
			/* otherwise go back */
			} else {
				print '<script type="text/javascript">'
		   			. 'history.go(-1);'
		   			. '</script>';
			}
			break;

			
		default : 
			print 'unknown action.';
	}
}

?>