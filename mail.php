<?php

include_once 'session.php';
include_once 'jason.php';
include_once 'error.php';

class Mail {
	public static function getContactForm() { 
		print '
		<form id="contact_form" action="mail.php" method="POST" enctype="multipart/form-data">
			<div class="row">
				<label for="subject">Subject:</label><br />
				<input id="subject" class="input" name="subject" type="text" value="" size="30" /><br />
			</div>';

		print (!isLoggedIn()) ? 
				'<div class="row">
					<label for="email">Your email:</label><br />
					<input colspan="2" id="email" class="input" name="email" type="text" value="" size="30" /><br />
				</div>'	: '';

		print '<div class="row">
				<label for="message">Your message:</label><br />
				<textarea id="message" class="input" name="message" rows="7" cols="30"></textarea><br />
			</div>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="Send email" />
			</div>
		</form>	';
	}

	public static function getSuccessfulContactMessage() {
		print '<div id="register">Message successfully sent.</div>';
	}

	public function sendMailFromPost() {
		$to = Jason::getOnce("admin_mail");

		if (!isLoggedIn()) {
			$from = post("email");
		} else {
			$from = getSession("mail");
		}

		$subject = post("subject");

		$message = post("message");

		$headers  = "From: " . $from  . "\r\n";
		$headers .= "Reply-To: " . $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		mail($to, $subject, $message, $headers);

		header("Location: index.php?page=contactDone");
	}

	public static function validateEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function sendMail($to, $from, $subject, $msg) {
		if (!self::validateEmail($to)) {
			throw new Exception('invalid origin email address');
		}
		if (!self::validateEmail($from)) {
			throw new Exception('invalid destination email address');
		}
		if (empty($subject)) {
			throw new Exception('empty subject');
		}
		if (empty($msg)) {
			throw new Exception('empty message');
		}
		$html = Jason::getOnce('msg_allow_html');
		if ($html) {
			// XXX validate HTML messages to avoid XSS
			$message = $msg;
		} else {
			$message = htmlentities($msg);
		}

		$headers  = "From: " . $from  . "\r\n";
		$headers .= "Reply-To: " . $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/";
		$headers .= $html ? "html" : "plain";
		$headers .= "; charset=ISO-8859-1\r\n";

		mail($to, $subject, $message, $headers);
	}

}

if (!empty($_POST) && !empty(post('subject'))) {
	try {
		$mail = new Mail();
		$mail->sendMailFromPost();
	} catch (Exception $e) {
		Error::exception($e);
	}
}

?>