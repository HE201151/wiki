<?php
	// needed for utility functions
	include 'session.php';

	// make sure the user did fill in username and pass
	$username = handleUsers();

	// connect to the database and check whether username exists. TODO put in own class with static 
	// methods for queries?
	try {
   		$dbh = new PDO('mysql:host=localhost;dbname=pmm_projet', 'youri', 'password');
   		$sql = 'SELECT username, mail from users WHERE username = :param';
    	$sth = $dbh->prepare($sql);
    	$sth->bindParam(':param', $username, PDO::PARAM_STR);
    	$sth->execute();
    	$result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
    		$_SESSION["username"] = $result['username'];
            $_SESSION["mail"] = $result['mail'];
            $_SESSION['is_logged_in'] = TRUE;
        }
    	$dbh = null;
	} catch (PDOException $e) {
    	print "Error!: " . $e->getMessage() . "<br/>";
    	die();
	}

	header("Location: index.php");
?>