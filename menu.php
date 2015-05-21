<?php
include_once 'jason.php';
include_once 'utils.php';
include_once 'error.php';
include_once 'user.php';

class Menu {
	public static function showBanner() {
		$config = new Jason;
		if (!empty($config->get("banner_img"))) {
			print '<div id="banner"><span id="bannerimg"><img src="' . $config->get("banner_img") . '" width=70 height=70 /></span>
					<span id="bannertext"><a href="index.php">' . $config->get("banner") . '</a></span></div>';
		} else {
			print '<div id="banneralone"><span id="bannertext"><a href="index.php">' . $config->get("banner") . '</a></span></div>';
		}
	}

	public static function showLogin() {
		if (!Error::none()) {
			echo '<div id="login_failed">' . Error::get() . '</div>';
		}
		if (!Utils::isLoggedIn()) {
			echo '<div id="login">
				<form id="loginForm" method="post" action="post.php?action=login">
					<input id="username" placeholder="user name" type="text" name="username" required>
					<input id="password" placeholder="password" type="password" name="password" required>
					<input type="submit" name="login" value="login"><br />
					<span class="resetpw"><a href="index.php?page=resetpw">Lost your password ?</a></span>
				</form>
				<script>
				$(function() {
					$("#loginForm").validate({
						rules: {
							username: "required",
							password: "required"
						},
						messages: {
							username: "Please enter a username",
							password: "Please provide a password"
						}
					});
				});
				</script>';
		} else {
			echo '<div id="login">You are logged in as <a href="?page=profile">' . Utils::getSession("username") . '</a></div>';
		}

		Error::alliswell();
	}

	public static function getNavigation() {
		echo "<ul>";
		echo '<li><a href="index.php">Home</a></li>';
		if (Utils::isLoggedIn()) {
			if (SessionUser::isAdmin(SessionUser::getStatus())) {
				echo '<li><a href="?page=admin">Admin</a></li>';
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