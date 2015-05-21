<?php 

include_once 'parser.php';
include_once 'user.php';

class Wiki {

	public static function canEditTopic($id) {
		return (Utils::isLoggedIn() && SessionUser::canWiki() && (self::isAuthor($id) || self::canModerate($id)));
	}

	public static function isAuthor($id) {
		$db = new db;
		$db->request('SELECT 1 FROM topic WHERE tId = :id AND authorId = :uid');
		$db->bind(':id', $id);
		$db->bind(':uid', SessionUser::getUserId());
		$result = $db->getAssoc();
		return (!empty($result));
	}

	public static function canModerate($id) {
		return true;
	}

	public static function insertTopic($title, $descr, $visibility) {
		$db = new db;
		$db->request('INSERT INTO topic (tTitle, tDesc, authorId, tCreation, tLastModif, visibilityAuthorChoice) VALUES (:title, :descr, :uid, now(), now(), :v);');
		$db->bind(':title', $title);
		$db->bind(':descr', $descr);
		$db->bind(':uid', SessionUser::getUserId());
		$db->bind(':v', $visibility);
		$db->doquery();
	}

	public static function updatePage($id, $content) {
		$db = new db;
		$db->request('UPDATE page SET content = :content, pLastModif = now() WHERE pId = :id;');
		$db->bind(':id', $id);
		$db->bind(':content', $content);
		$db->doquery();
	}

	public static function findWord($word) {
		$db = new db;
		$db->request("SELECT 1 FROM page WHERE keyword = :word");
		$db->bind(':word', $word);
		$result = $db->getAssoc();
		return (!empty($result));
	}

	public static function actions() {
		if (Utils::get('action') === 'new') {
			self::createTopic();
		}
	}
	public static function createTopic() {
		if (!Utils::isLoggedIn()) {
			print '<div id="register">Only logged in users can create new topics.</div>';
			return;
		}
		if (Utils::isPost('topic')) {
			// create topic and assign visibility
			// auto assign to modo
			// create blank page
			// manage topics (admin,author,choice)-> visibility
			try {
				self::insertTopic(Utils::post('topic'), Utils::post('descr'), Utils::post('select'));
				var_dump(Utils::post('topic'), Utils::post('descr'), Utils::post('select'));
			} catch (Exception $e) {
				Error::exception($e);
			}
		} else {
			print '<form id="register" action="index.php?page=topics&action=new" method="post">
			<table border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
					<tr>
						<td id="regtitle">Create Topic</td>
					</tr>
					<tr id="formcontent">
						<td>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td colspan="2">Topic Name</td>
									</tr>
									<tr>
										<td colspan="2"><input type="text" name="topic" id="topic" maxlength="50" style="width: 100%" value="" required/></td>
									</tr>
									<tr>
										<td>Topic Description</td>
									</tr>
									<tr>
										<td colspan="2"><input type="text" name="descr" id="descr" maxlength="50" style="width: 100%" value=""/></td>
									<tr>
										<td>Topic Visibility</td>
									</tr>
									<tr>
										<td>
											<select id="select" name="select">';
												// get sure <= user hierarchically.
												// <option value="username">username</option>
												// <option value="email">email</option>
												// <option value="status">status</option>
												$groups = SessionUser::getLowerHierarchyGroups();
												foreach ($groups as $group) {
													print '<option value="' . $group . '">' . $group . '</option>';
												}
											print '</select>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
			<br />
			<div id="submit" align="center">
				<input type="submit" name="Submit" value="create" />
			</div>
			</form>';
			self::validateTopicCreation();
		}
	}

	public static function validateTopicCreation() {
		print '<script>
		$(function() {
			$("#register").validate({
				rules: {
					topic: "required",
					select: "required"
				},
				messages: {
					topic: "Please enter a topic name",
					select: "Please choose group visibility"
				}
			});
		});
		</script>';
	}

	/* function serving everyone, simply list wikis available to everyone */
	public static function getTopics() {
		$db = new db;
		$db->request('SELECT tId, authorId, tTitle, tDesc, tCreation FROM topic;');
		$results = $db->getAllAssoc();
		if (empty($results)) {
			print '<div id="register">No topics started yet. <a href="index.php?page=topics&action=new">Write on a new topic.</a></div>';
		} else {
			print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">
						<span style="float: left;">Topics</span>
						<span style="float: right;"><a href="index.php?page=topics&action=new">Write on a new topic.</a></span>
					</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td class="tcat">Topic</td>
										<td class="tcat">Description</td>
										<td class="tcat">Creation Date</td>
										<td class="tcat">Author</td>
									</tr>';
									foreach ($results as $topic => $content) {
										print '
											<tr>
												<td class="trow"><a href=index.php?page=topics&id=' . $content['tId'] . '>' . $content['tTitle'] . '</a></td>
												<td class="trow">' . (empty($content['tDesc']) ? "empty" : $content['tDesc']) . '</td>
												<td class="trow">' . $content['tCreation'] . '</td>
												<td class="trow">';
													if (!is_numeric($content['authorId'])) {
														print 'anonymous author';
													} else {
														$db->request('SELECT username FROM users WHERE id = :id;');
														$db->bind(':id', $content['authorId']);
														$uname = $db->getAssoc();
														print '<a href="index.php?page=profile&uid=' . $content['authorId'] . '">' . $uname['username'] . '</a>';
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

	public static function getTopicPages($tId) {
		$db = new db;
		$db->request('SELECT pId, pTitle, pDesc, pCreation, pLastModif FROM page WHERE tId = :tId;');
		$db->bind(':tId', $tId);
		$results = $db->getAllAssoc();
		if (empty($results)) {
			// if is author.
			print '<div id="register">No pages exist for this topic. <a href="index.php?page=topics&action=new">Write a new page for this topic.</a></div>';
		} else {
			print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">
						<span style="float: left;">Pages</span>
						<span style="float: right;"><a href="index.php?page=topics&action=new">Write a new page for this topic.</a></span>
					</td>
				</tr>
				<tr id="formcontent">
					<td>
						<fieldset>
							<table cellpadding="6" cellspacing="0" width=100%>
								<tbody>
									<tr>
										<td class="tcat">Page</td>
										<td class="tcat">Creation Date</td>
										<td class="tcat">Last Modif. Date</td>
									</tr>';
									foreach ($results as $thread => $content) {
										print '
											<tr>
												<td class="trow"><a href=index.php?page=topics&id=' . $tId . '&p= ' . $content['pId'] . '>' . $content['tTitle'] . '</a></td>
												<td class="trow">' . $content['pCreation'] . '</td>
												<td class="trow">' . $content['pLastModif'] . '</td>
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

	public static function getPage($tid) {
		$db = new db;
		$db->request('SELECT pId, pTitle, content, pCreation, pLastModif FROM page WHERE tId = :id;');
		$db->bind(':id', $tid);
		$result = $db->getAssoc();
		if (empty($result)) {
			print '<div id="register">This page does not exist.</div>';
		} else {
			print '
			<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
					<tr>
						<td id="regtitle">' . $result['pTitle'] . '</td>
					</tr>
					<tr id="formcontent">
						<td>
							<fieldset>
								<table cellpadding="6" cellspacing="0" width=100%>
									<tbody>
										<tr> '. Parser::get($result['content']) . '</tr>
									</tbody>
								</table>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>';
			self::editWiki($id, $result['content']);
		}
	}

	public static function editPage($id, $content) {
		if (self::canEditTopic($id)) {
			if (Utils::isGet('action')) {
				if (Utils::get('action') === 'edit') {
					if (Utils::isPost('content')) {
						if (Utils::post('id') === $id) {
							try {
								self::updatePage(Utils::post('id'), Utils::post('content'));
							} catch (Exception $e) {
								Error::exception($e);
							}
						}
					}
				}
			}
		print '
			<form id="register" action="index.php?page=topics&tId='.$id.'&action=edit" method="post" onsubmit="setTimeout(function () { window.location.reload(); }, 0)">
				<table border="0" cellspacing="0" cellpadding="6" class="tborder">
					<tbody>
						<input type="hidden" name="id" value="' . $id . '">
						<tr>
							<td id="regtitle">Page edit</td>
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
		}
	}
}

?>