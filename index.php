<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Projet PMM</title>
		<link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
		<link rel="stylesheet" type="text/css" href="css/global.css" />
		<script src="js/jquery-1.11.2.min.js"></script>
		<script src="js/jquery.validate.min.js"></script>
	</head>
	<body>
		<?php
			include_once 'menu.php';
		?>
		<div class="wrapper">
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

				} else if (Utils::page() === "admin") {
					include_once 'user.php';
					User::getAdmin();

				} else if (Utils::page() === "resetpw") {
					include_once 'register.php';
					Register::resetPassword();

				} else if (Utils::page() === "wiki") {
					include_once 'wiki.php';
					if (isset($_GET['wiki'])) {
						Wiki::getWiki(Utils::get('wiki'));
					} else {
						Wiki::getAllWikis();
					}

				} else {
					include_once 'wiki.php';
					if (Utils::isLoggedIn()) {
						print '<div id="register">Welcome, ' . SessionUser::getUsername() . '. ' . SessionUser::getEmail() . '. You are an ' . SessionUser::getStatusDesc() . '.</div>';
					} else {
						echo "You are not logged in.";
					}
					Wiki::getAllWikis();
				}
	 			?>
			</article>
			<div class="clear"></div>
			<div class="push"></div>
		</div>
		<footer>
			<a href="mailto:youri.mout@gmail.com">Mail Youri Mouton, Copyright © 2015.</a>
		</footer>
	</body>
</html>
