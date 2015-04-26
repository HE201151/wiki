<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>PHP Test</title>
		<link rel="stylesheet" type="text/css" href="global.css" />
	</head>
	<body>
		<?php
			include 'menu.php';
		?>
		<header>
			<?php showBanner(); showLogin(); ?>
		</header>
		<nav>
			<?php getNavigation(); ?>
		</nav>
		<article>
			<?php
			if (page() === "register") {
				include 'register.php';
				Register::getRegisterForm();

			} else if (page() === "registerDone") {
				include 'register.php';
				Register::getSuccessfulRegistrationMessage();

			} else if (page() === "activation") {
				include 'register.php';
				Register::activate();

			} else if (page() === "contact") {
				include 'mail.php';
				Mail::getContactForm();

			} else if (page() === "contactDone") {
				include 'mail.php';
				Mail::getSuccessfulContactMessage();

			} else if (page() === "logout") {
				include 'log.php';
				Log::logout();

			} else {
				if (isLoggedIn()) {
					echo getSession("user_id") . '<a href="mailto:'.getSession("mail").'">'.getSession("username").'</a>';
				} else {
					echo "You are not logged in.";
				}
			}
 			?>
		</article>
		<footer>
			<a href="mailto:youri.mout@gmail.com">Youri Mouton.</a>
		</footer>
	</body>
</html>
