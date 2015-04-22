<?php
    // XXX include more precise error checking.
	// needed for utility functions
    include 'db.php';
	include 'session.php';

	// make sure the user did fill in username and pass
	$username = handleUsers();

    $db = new db();
    $db->request('SELECT username, mail from users WHERE username = :name and password = :pass');
    $db->bind(':name', $username);
    $db->bind(':pass', post('password'));
    $result = $db->getAssoc();
    
    if (!empty($result)) {
        $_SESSION["username"] = $result['username'];
        $_SESSION["mail"] = $result['mail'];
        $_SESSION['login_failed'] = "";
        $_SESSION['is_logged_in'] = TRUE;
    } else {
        $_SESSION['login_failed'] = "wrong username or password";
    }

    $db = null;

	header("Location: index.php");
?>