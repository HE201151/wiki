<?php

include_once 'db.php';
include_once 'utils.php';
include_once 'jason.php';
include_once 'error.php';
include_once 'user.php';

class Mail {
	const status = [ "noreply" => 0, "replied" => 1, "deleted" => 2 ];

	public static function getStatus($id) {
		return array_search($id, self::status);
	}

	public static function getContactForm() {
		if (Utils::isLoggedIn()) {
			if (isset($_GET['mid'])) {
				$db = new db;
				$db->request('SELECT id, user_id, subject, message, date FROM messages WHERE id = :id OR parent_id = :id ORDER BY date ASC');
				$db->bind(':id', Utils::get('mid'));
				$msgArray = $db->getAllAssoc();
				print '
		        <table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
		        <tbody>
		            <tr>
		                <td id="regtitle">' . $msgArray[0]['subject'] . '</td>
		            </tr>
		            <tr id="formcontent">
		                <td>
		                    <table cellpadding="6" cellspacing="0" width=100%>
		                        <tbody>';
		                            foreach ($msgArray as $key => $value) {
		                            	$db->request('SELECT id, username, avatar FROM users WHERE id = (SELECT user_id FROM messages WHERE user_id = :uid LIMIT 1);');
		                            	$db->bind(':uid', $value['user_id']);
		                            	$newresult = $db->getAssoc();
		                                print  '<tr>
			                                		<tr>
			                                			<td>
			                                				<table class="avatartable" style="border: none!important;" cellspacing="0" cellpadding="0" border="0">
			                                					<tbody>
			                                						<tr>
			                                            				<td class="avatartd"><a href="index.php?page=profile&uid="' . $newresult['id'] . '"><img src="' . $newresult['avatar']  . '" width=70 height=70 /></a></td>
			                                            				<td><a href="index.php?page=profile&uid="' . $newresult['id'] . '">' . $newresult['username'] . '</a></td>
			                                            			</tr>
			                                            		</tbody>
			                                            	</table>
			                                            </td>
			                                        </tr>
			                                        <tr>
			                                            <td class="msgtd">' . $value['message'] . '</td>
			                                        </tr>
			                                        <tr>
			                                            <td class="datetd">' . $value['date'] . '</td>
			                                        </tr>
		                                        </tr>';
		                            }
		                        print '</tbody>
		                    </table>
		                </td>
		            </tr>
	                <tr class="msgactions">
						<td><a href="index.php?page=contact&mid=' . Utils::get('mid') . '&action=reply"><input id="submit_button" type="submit" value="Reply" /></a></td>
					</tr>
		        </tbody>
		        </table>';
		        if (isset($_GET['action'])) {
					if (Utils::get('action') === "reply") {
						print '
						<form id="contact_form" action="post.php?action=message&mid=' . Utils::get('mid') . '" method="POST">
							<textarea id="message" class="input" name="message" rows="7" cols="30" required></textarea><br />
							<div align="center" id="submit">
								<input id="submit_button" type="submit" value="Send reply" />
							</div>
						</form>
						';
					}
				}
	        	$db = null;
				return;
			}
		} else {
			self::noPermission();
		}

		print self::preSubmitValidation() . '
		<form id="contact_form" action="post.php?action=message" method="POST">
			<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
			            <tr>
			                <td id="regtitle">Compose Message</td>
			            </tr>
			            <tr id="formcontent">
			                <td>
			                    <table cellpadding="6" cellspacing="0" width=100%>
			                        <tbody>
			                        	<tr>
											<td><label for="subject">Subject:</label></td>
											<td><input id="subject" class="input" name="subject" type="text" value="" required/></td>
										</tr>';

								print (!Utils::isLoggedIn()) ? 
										'<tr>
											<td><label for="email">Your email:</label></td>
											<td><input colspan="2" id="email" class="input" name="email" type="text" value="" size="30" required/></td>
										</tr>'	: '';
								print '<tr>
											<td><label for="message">Your message:</label></td>
											<td><textarea id="message" class="input" name="message" rows="7" cols="30" required></textarea></td>
										</tr>
									</tbody>
			                    </table>
			                </td>
			            </tr>
			    </tbody>
			</table>
			<div align="center" id="submit">
				<input id="submit_button" type="submit" value="Send Message" />
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

	public static function noPermission() {
		print '<div id="register">Message not found</div>';
	}

	public function sendMailFromPost() {
		$to = Jason::getOnce("admin_mail");

		if (!Utils::isLoggedIn()) {
			$from = Utils::post("email");
		} else {
			$from = Utils::getSession("email");
		}

		$html = Jason::getOnce('msg_allow_html');

		$subject = Utils::post("subject");
		$message = Utils::post("message");

		$headers  = "From: " . $from  . "\r\n";
		$headers .= "Reply-To: " . $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		if ($html === "true") {
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		} else {
			$headers .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		}

		mail($to, Jason::getOnce("banner") . ' - ' . $subject, $message, $headers);

		/* insert message into db */
		$db = new db;
		$db->request('INSERT INTO messages (subject, email, user_id, date, message, status) VALUES (:subject, :email, :user_id, now(), :msg, :status);');
		$db->bind(':subject', $subject);
		$db->bind(':email', $from);
		$db->bind(':user_id', (!empty(SessionUser::getUserId())) ? SessionUser::getUserId() : null);
		$db->bind(':msg', $message);
		$db->bind(':status', self::status['noreply']);
		$db->doquery();

		$db = null;
		Error::alliswell();
	}

	public static function reply($parent) {
		if (!Utils::isLoggedIn()) {
			throw new Exception('Please log in to answer messages');
		}

		if (empty($parent)) {
			throw new Exception('empty parent');
		}
		/* select email and subject from parent */
		$db = new db;
		$db->request('SELECT email, subject FROM messages WHERE id = :parent;');
		$db->bind(':parent', $parent);
		$result = $db->getAssoc();

		if (empty($result)) {
			throw new Exception('parent message not found');
		}

		/* insert new reply */
		$db->request('INSERT INTO messages (subject, email, user_id, date, message, parent_id, status) VALUES (:subject, :email, :user_id, now(), :msg, :parent, :status);');
		$db->bind(':subject', $result['subject']);
		$db->bind(':email', $result['email']);
		$db->bind(':user_id', SessionUser::getUserId());
		$db->bind(':msg', Utils::post('message'));
		$db->bind(':parent', $parent);
		$db->bind(':status', self::status['noreply']);
		$db->doquery();

		/* update parent message as replied */
		$db->request('UPDATE messages SET status=:status where id=:id');
		$db->bind(':status', self::status["replied"]);
		$db->bind(':id', $parent);
		$db->doquery();

		$db = null;

		/* send email with message */
		try {
			self::sendMail($result['email'], SessionUser::getEmail(), $result['subject'], Utils::post('message'), false);
		} catch (Exception $e) {
			Error::exception($e);
		}
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