<?php
include_once 'jason.php';
include_once 'session.php';
include_once 'error.php';
include_once 'user.php';

class Menu {
	public static function showBanner() {
		echo '<div id="banner"><a href="/">' . Jason::getOnce("banner") . "</a></div>";
	}

	public static function showLogin() {
		if (!Error::none()) {
			echo '<div id="login_failed">' . Error::get() . '</div>';
		}
		if (!isLoggedIn()) {
			echo '<div id="login">
				<form method="post" action="logPost.php">
					<input placeholder="user name" type="text" name="username">
					<input placeholder="password" type="password" name="password">
					<input type="submit" name="login" value="login">
				</form>';
		} else {
			echo '<div id="login">You are logged in as <a href="?page=profile">' . getSession("username") . '</a></div>';
		}
	}

	public static function getNavigation() {
		echo "<ul>";
		if (isLoggedIn()) {
			if (User::getStatus() == UserStatus::Administrator) {
				echo '<li><a href="?page=administration">Admin</a></li>';
			}
			echo '<li><a href="?page=profile">Profile</a></li>';
			echo '<li><a href="?page=logout">Log Out</a></li>';
		} else {
			echo '<li><a href="?page=register">Register</a></li>';
		}
		echo '<li><a href="?page=contact">Contact</a></li>';
		echo '</ul>';
	}
}
?>