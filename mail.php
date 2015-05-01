<?php

include_once 'db.php';
include_once 'utils.php';
include_once 'jason.php';
include_once 'error.php';
include_once 'user.php';

class Mail {
	public static function getContactForm() { 
		print self::preSubmitValidation() . '
		<form id="contact_form" action="post.php?action=message" method="POST" enctype="multipart/form-data">
			<div class="row">
				<label for="subject">Subject:</label><br />
				<input id="subject" class="input" name="subject" type="text" value="" size="30" required/><br />
			</div>';

		print (!Utils::isLoggedIn()) ? 
				'<div class="row">
					<label for="email">Your email:</label><br />
					<input colspan="2" id="email" class="input" name="email" type="text" value="" size="30" required/><br />
				</div>'	: '';

		print '<div class="row">
				<label for="message">Your message:</label><br />
				<textarea id="message" class="input" name="message" rows="7" cols="30" required></textarea><br />
			</div>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="Send email" />
			</div>
		</form>	';
	}

	public static function preSubmitValidation() {
		print '<script>
		$(function() {
			$("#contact_form").validate({
				rules: {
					subject: "required", 
					email: {
						required: true,
						email: true
					},
					message: "required"
				},
				messages: {
					subject: "Please enter a subject",
					email: {
						required: "Please provide an email address",
						email: "Please enter a valid email address"
					},
					message: "Please enter a message"
				}
			});
		});
		</script>';
	}

	public static function getSuccessfulContactMessage() {
		print '<div id="register">Message successfully sent.</div>';
	}

	public function sendMailFromPost() {
		$to = Jason::getOnce("admin_mail");

		if (!Utils::isLoggedIn()) {
			$from = Utils::post("email");
		} else {
			$from = Utils::getSession("email");
		}

		$html = Jason::getOnce('msg_allow_html');

		$subject = Jason::getOnce("banner") . ' - ';
		$subject .= Utils::post("subject");

		$message = Utils::post("message");

		$headers  = "From: " . $from  . "\r\n";
		$headers .= "Reply-To: " . $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		if ($html === "true") {
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		} else {
			$headers .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		}

		mail($to, $subject, $message, $headers);

		/* insert message into db */
		$db = new db;
		$db->request('INSERT INTO messages (subject, email, user_id) VALUES (:subject, :email, :user_id);');
		$db->bind(':subject', $subject);
		$db->bind(':email', $from);
		$db->bind(':user_id', (!empty(SessionUser::getUserId())) ? SessionUser::getUserId() : null);
		$db->doquery();

		$db = null;
		Error::alliswell();
	}

	public static function validateEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public static function sendMail($to, $from, $subject, $msg, $html) {
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

		if ($html) {
			// XXX validate HTML messages to avoid XSS
			$message = $msg;
		} else {
			$message = htmlentities($msg);
		}

		$msubject = Jason::getOnce("banner") . ' - ';
		$msubject .= $subject;

		$headers  = "From: " . $from  . "\r\n";
		$headers .= "Reply-To: " . $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/";
		$headers .= $html ? "html" : "plain";
		$headers .= "; charset=ISO-8859-1\r\n";

		mail($to, $msubject, $message, $headers);
	}

}
?>