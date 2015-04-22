<?php
	include_once 'jason.php';
	include_once 'session.php';
	include_once 'error.php';

	function showBanner() {
		echo '<div id="banner"><a href="/">' . Jason::getOnce("banner") . "</a></div>";
	}

	function showLogin() {
		if (!Error::none()) {
			echo '<div id="login_failed">' . Error::get() . '</div>';
		}
		if (!isLoggedIn()) {
			echo '<div id="login">
				<form method="post" action="login.php">
					<input placeholder="user name" type="text" name="username">
					<input placeholder="password" type="password" name="password">
					<input type="submit" name="login" value="login">
				</form>';
		} else {
			echo '<div id="login">You are logged in as ' . getSession("username") . '</div>';
		}
	}

	function getNavigation() {
		echo "<ul>";
		if (isLoggedIn()) {
			echo '<li><a href="?page=profile">Profile</a></li>';
			echo '<li><a href="logout.php">Log Out</a></li>';
		} else {
			echo '<li><a href="?page=register">Register</a></li>';
		}
		echo '<li><a href="?page=contact">Contact</a></li>';
		echo '</ul>';
	}
?>