<?php
    // XXX include more precise error checking.
	// needed for utility functions
    include_once 'db.php';
	include_once 'session.php';
    include_once 'error.php';

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
        Error::alliswell();
        $_SESSION['is_logged_in'] = TRUE;
    } else {
        Error::set("wrong username or password");
    }

    $db = null;

	header("Location: index.php");
?>