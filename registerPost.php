<?php

include_once 'register.php';
include_once 'error.php';

/* Submit registration form */
if (!empty($_POST)) {
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
}

?>