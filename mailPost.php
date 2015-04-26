<?php

include_once 'mail.php';
include_once 'error.php';

if (!empty($_POST)) {
	try {
		$mail = new Mail();
		$mail->sendMailFromPost();
		header("Location: index.php?page=contactDone");
	} catch (Exception $e) {
		Error::exception($e);
	}
}

?>