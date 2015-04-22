<?php
	include_once 'session.php';
	include_once 'jason.php';

	function getContactForm() { 
		echo '
		<form id="contact_form" action="contact.php" method="POST" enctype="multipart/form-data">
			<div class="row">
				<label for="subject">Subject:</label><br />
				<input id="subject" class="input" name="subject" type="text" value="" size="30" /><br />
			</div>';

		echo (!isLoggedIn()) ? 
				'<div class="row">
					<label for="email">Your email:</label><br />
					<input colspan="2" id="email" class="input" name="email" type="text" value="" size="30" /><br />
				</div>'	: '';

		echo '<div class="row">
				<label for="message">Your message:</label><br />
				<textarea id="message" class="input" name="message" rows="7" cols="30"></textarea><br />
			</div>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="Send email" />
			</div>
		</form>	';
	}

	function sendMail() {
		$to = Jason::getOnce("admin_mail");

		if (!isLoggedIn()) {
			$from = post("email");
		} else {
			$from = getSession("mail");
		}

		$subject = post("subject");

		$text = post("message");
		$message = wordwrap($text, 80, "\r\n");

		mail($to, $from, $subject, $message);
	}

	if (isPost("message")) {
		try {
			sendMail();
		} catch (Exception $e) {
			print $e;
		}
	}
?>