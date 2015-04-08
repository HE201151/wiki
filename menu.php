<?php

include 'jason.php';

	function showBanner() {
		$config = new Jason();
		echo "<div id=\"banner\"><a href=\"/\">" . $config->get("banner") . "</a></div>";
	}

	function showLogin() {
		echo '<div id="login">
			<a href="user.php">user</a>
			<a href="admin.php">admin</a>
			<a href="log.php">logout</a>
			<form method="post" action="login.php">
				<input placeholder="user name" type="text" name="username">
				<input placeholder="password" type="password" name="password">
				<input type="submit" name="login" value="login">
			</form>';
	}
?>