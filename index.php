<html>
	<head>
		<title>PHP Test</title>
		<link rel="stylesheet" type="text/css" href="/global.css" />
	</head>
	<body>
		<?php
			session_start();
			include 'menu.php';
			//include 'settings.php'; 
		?>
		<header>
			<?php showBanner(); showLogin(); ?>
		</header>
		<nav>
		</nav>
		<article>
			<?php echo $_SESSION["username"]; ?>
		</article>
	</body>
</html>
