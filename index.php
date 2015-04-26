<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>PHP Test</title>
		<link rel="stylesheet" type="text/css" href="global.css" />
	</head>
	<body>
		<?php
			include_once 'menu.php';
		?>
		<header>
			<?php Menu::showBanner(); Menu::showLogin(); ?>
		</header>
		<nav>
			<?php Menu::getNavigation(); ?>
		</nav>
		<article>
			<?php
			if (Utils::page() === "register") {
				include_once 'register.php';
				Register::getRegisterForm();

			} else if (Utils::page() === "registerDone") {
				include_once 'register.php';
				Register::getSuccessfulRegistrationMessage();

			} else if (Utils::page() === "activation") {
				include_once 'register.php';
				Register::activate();

			} else if (Utils::page() === "contact") {
				include_once 'mail.php';
				Mail::getContactForm();

			} else if (Utils::page() === "contactDone") {
				include_once 'mail.php';
				Mail::getSuccessfulContactMessage();

			} else if (Utils::page() === "logout") {
				include_once 'log.php';
				Log::logout();

			} else if (Utils::page() === "profile") {
				include_once 'user.php';
				User::getProfile();

			} else {
				if (Utils::isLoggedIn()) {
					echo Utils::getSession("status") . '<a href="mailto:'. Utils::getSession("mail").'">'. Utils::getSession("username").'</a>';
				} else {
					echo "You are not logged in.";
				}
			}
 			?>
		</article>
		<footer>
			<a href="mailto:youri.mout@gmail.com">Mail Youri Mouton, Copyright © 2015.</a>
		</footer>
	</body>
</html>
