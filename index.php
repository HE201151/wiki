<html>
	<head>
		<title>PHP Test</title>
	</head>
	<body>
		<header>
			Page d'accueil
		</header>
		<?php include 'session.php'; ?>
		<nav>
			<a href="user.php">user</a>
			<a href="admin.php">admin</a>
			<a href="log.php">logout</a>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				User: <input type="text" name="username"> Password: <input type="text" name="password">
				<input type="submit" name="login" value="login">
			</form>
		</nav>
		<article>
			<h1>AVANT</h1>
			<?php 
				showSession(); 
				showCount(); 
				incCount();
			?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input name="submit" type="submit" value="reset">
			</form>
			<h1>APRES</h1>
			<?php showSession()?>
			<?php echo handleUsers() ?>
			permission level <?php echo getSession('gid') . ' = ' . getPerm(); ?>
			<pre>$_GET : <?php print_r($_GET); ?></pre>
		</article>
	</body>
</html>
