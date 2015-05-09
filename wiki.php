<?php 

include_once 'parser.php';
include_once 'user.php';

class Wiki {

	public static function canEditWiki($user, $id) {
		return true;
	}

	public static function updateWiki($id, $content) {
		$db = new db;
		$db->request('UPDATE wiki SET msgContent = :content, msgLastModif = now() WHERE msgId = :id;');
		$db->bind(':id', $id);
		$db->bind(':content', $content);
		$db->doquery();
	}

	public static function findWord($word) {
		$db = new db;
		$db->request("SELECT 1 FROM wiki WHERE msgKeyword = :word");
		$db->bind(':word', $word);
		$result = $db->getAssoc();
		return (!empty($result));
	}

	/* function serving everyone, simply list wikis available to everyone */
	public static function getAllWikis() {
		$db = new db;
		$db->request('SELECT msgId, msgSubject, msgContent, msgDateCrea FROM wiki;');
		$results = $db->getAllAssoc();
		if (empty($results)) {
			print '<div id="register">No wikis started yet.</div>';
		} else {
			print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle"> Wikis </td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td class="tcat">Subject</td>
										<td class="tcat">Author</td>
									</tr>';
									foreach ($results as $thread => $content) {
										print '
											<tr>
												<td class="trow">
													<a href=index.php?page=wiki&wiki=' . $content['msgId'] . '>' . $content['msgSubject'] . '</a>
												</td>
												<td class="trow">';
													if (empty($content['authorId'])) {
														print 'anonymous author';
													} else {
														print '<a href="index.php?page=profile&uid=' . $value['authorId'] . '">author</a>';
													}
												'</td>
											</tr>';
									}
									print '
									</tbody>
							</table>
						</fieldset>
					</td>
				</tr>
				</tbody>
			</table>';
		}
	}

	public static function getWiki($id) {
		$db = new db;
		$db->request('SELECT msgId, msgSubject, msgContent, msgDateCrea FROM wiki WHERE msgId = :id;');
		$db->bind(':id', $id);
		$result = $db->getAssoc();
		if (empty($result)) {
			print '<div id="register">This wiki does not exist.</div>';
		} else {
			print '
			<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
					<tr>
						<td id="regtitle">' . $result['msgSubject'] . '</td>
					</tr>
					<tr id="formcontent">
						<td>
							<fieldset>
								<table cellpadding="6" cellspacing="0" width=100%>
									<tbody>
										<tr> '. Parser::get($result['msgContent']) . '</tr>
									</tbody>
								</table>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>';
			self::editWiki($id, $result['msgContent']);
		}
	}

	public static function editWiki($id, $content) {
		if (self::canEditWiki(SessionUser::getStatus(), $id)) {
			if (Utils::isGet('action')) {
				if (Utils::get('action') === 'edit') {
					if (Utils::isPost('content')) {
						if (Utils::post('id') === $id) {
							try {
								self::updateWiki(Utils::post('id'), Utils::post('content'));
								print '<tr><td>Wiki entry updated successfully, reloading wiki.</td></tr>';
							} catch (Exception $e) {
								Error::exception($e);
							}
						}
					}
				}
			}
		print '
			<form id="register" action="index.php?page=wiki&wiki='.$id.'&action=edit" method="post" onsubmit="setTimeout(function () { window.location.reload(); }, 0)">
				<table border="0" cellspacing="0" cellpadding="6" class="tborder">
					<tbody>
						<input type="hidden" name="id" value="' . $id . '">
						<tr>
							<td id="regtitle">Wiki edit</td>
						</tr>
						<tr id="formcontent">
							<td>
								<fieldset>
									<table cellpadding="6" cellspacing="0" width=100%>
										<tbody>
											<tr>
												<textarea id="wikicontent" name="content" rows="15">' . $content . '</textarea>
											</tr>
										</tbody>
									</table>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<div align="center" id="submit">
					<input id="submit_button" type="submit" value="submit" />
				</div>
			</form>';
		} else {
			print '
				<tr>
					<td><a href="index.php?page=wiki&wiki=' . $id . '&action=edit">Edit wiki</a></td>
				</tr>';
		}
	}
}

?>