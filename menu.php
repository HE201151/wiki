<?php
include_once 'jason.php';
include_once 'utils.php';
include_once 'error.php';
include_once 'user.php';

class Menu {
	public static function showBanner() {
		echo '<div id="banner"><a href="index.php">' . Jason::getOnce("banner") . "</a></div>";
	}

	public static function showLogin() {
		if (!Error::none()) {
			echo '<div id="login_failed">' . Error::get() . '</div>';
		}
		if (!Utils::isLoggedIn()) {
			echo '<div id="login">
				<form method="post" action="post.php?action=login">
					<input placeholder="user name" type="text" name="username">
					<input placeholder="password" type="password" name="password">
					<input type="submit" name="login" value="login">
				</form>';
		} else {
			echo '<div id="login">You are logged in as <a href="?page=profile">' . Utils::getSession("username") . '</a></div>';
		}

		Error::alliswell();
	}

	public static function getNavigation() {
		echo "<ul>";
		echo '<li><a href="index.php">Home</a></li>';
		if (Utils::isLoggedIn()) {
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