<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>PHP Test</title>
		<link rel="stylesheet" type="text/css" href="/global.css" />
	</head>
	<body>
		<?php
			include 'menu.php';
			//include 'settings.php'; 
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
				getRegisterForm();
			} else {
				echo getSession("username");
			}
 			?>
		</article>
		<footer>
			<a href="mailto:youri.mout@gmail.com">Youri Mouton.</a>
		</footer>
	</body>
</html>
