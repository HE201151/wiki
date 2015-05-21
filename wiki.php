<?php 

include_once 'parser.php';
include_once 'user.php';

class Wiki {

	public static function canEditPage($tid) {
		return (Utils::isLoggedIn() && SessionUser::canWiki() && self::isAuthor($tid));
	}

	public static function isAuthor($tid) {
		$db = new db;
		$db->request('SELECT 1 FROM topic WHERE tId = :tid AND authorId = :uid');
		$db->bind(':tid', $tid);
		$db->bind(':uid', SessionUser::getUserId() == "admin" ? 0 : SessionUser::getUserId());
		$result = $db->getAssoc();
		return ($result);
	}

	public static function canSeeTopic($tid) {
		$db = new db;
		$db->request('SELECT visibilityAuthorChoice, visibilityModChoice, visibilityAdminChoice FROM topic WHERE tId = :tid;');
		$db->bind(':tid', $tid);
		$results = $db->getAssoc();
		if (empty($results['visibilityAdminChoice'])) {
			if (empty($results['visibilityModChoice'])) {
				$visibility = SessionUser::hasPermission($results['visibilityAuthorChoice']);
			} else {
				$visibility = SessionUser::hasPermission($results['visibilityModChoice']);
			}
		} else {
			$visibility = SessionUser::hasPermission($results['visibilityAdminChoice']);
		}
		return $visibility;
	}

	public static function canSeePage($pid) {
		$db = new db;
		$db->request('SELECT tId FROM page WHERE pId = :pid;');
		$db->bind(':pid', $pid);
		$result = $db->getAssoc();
		return self::canSeeTopic($result['tId']);
	}

	public static function canModerate($id) {
		return false;
	}

	public static function insertTopic($title, $descr, $visibility) {
		$db = new db;
		$db->request('INSERT INTO topic (tTitle, tDesc, authorId, tCreation, tLastModif, visibilityAuthorChoice) VALUES (:title, :descr, :uid, now(), now(), :v);');
		$db->bind(':title', $title);
		$db->bind(':descr', $descr);
		$db->bind(':uid', SessionUser::getUserId());
		$db->bind(':v', $visibility);
		$db->doquery();
		$db->request('SELECT tId FROM topic GROUP BY tId DESC');
		$tid = $db->getAssoc();
		return $tid['tId'];
	}

	public static function insertPage($tid) {
		$db = new db;
		$db->request('INSERT INTO page (tId, content, pCreation, pLastModif, pTitle, pDesc) VALUES (:tid, "empty", now(), now(), :ptitle, "empty");');
		$db->bind(':tid', $tid);
		$db->bind(':ptitle', Utils::isGet('keyword') ? Utils::get('keyword') : "empty");
		$db->doquery();
		$db->request('SELECT pId FROM page GROUP BY pId DESC');
		$tid = $db->getAssoc();
		return $tid['pId'];
	}

	public static function updatePage($id, $title, $descr, $content) {
		$db = new db;
		$db->request('UPDATE page SET pTitle = :title, content = :content, pDesc = :descr, pLastModif = now() WHERE pId = :id;');
		$db->bind(":title", $title);
		$db->bind(':id', $id);
		$db->bind(':content', $content);
		$db->bind(':descr', $descr);
		$db->doquery();
	}

	public static function findWord($word) {
		$db = new db;
		$db->request("SELECT pId FROM page WHERE pTitle = :word");
		$db->bind(':word', $word);
		$result = $db->getAssoc();
		return ($result['pId']);
	}

	public static function actions() {
		if (Utils::get('action') === 'newtopic') {
			self::createTopic();
		} else if (Utils::get('action') === 'newpage') {
			if (Utils::isGet('tid')) {
				if (self::canEditPage(Utils::get('tid'))) {
					$pid = self::insertPage(Utils::get('tid'));
					$db = new db;
					$db->request('SELECT pTitle, pDesc, content FROM page WHERE pId = :pid;');
					$db->bind(':pid', $pid);
					$result = $db->getAssoc();
					self::editPage($pid, $result['pTitle'], $result['pDesc'], $result['content']);
				} else {
					print '<div id="register">You are not allowed to create pages for this topic.</div>';
				}
			}
		} else if (Utils::get('action') === 'editpage') {
			if (Utils::isPost('content')) {
				try {
					self::updatePage(Utils::post('pid'), Utils::post('title'), Utils::post('descr'), Utils::post('content'));
				} catch (Exception $e) {
					Error::exception($e);
				}
			}
			self::getPage(Utils::get('pid'));
		}
	}

	public static function createTopic() {
		if (!Utils::isLoggedIn()) {
			print '<div id="register">Only logged in users can create new topics.</div>';
			return;
		}
		if (Utils::isPost('topic')) {
			// XXX manage topics (admin,author,choice)-> visibility -> modoid
			try {
				$tid = self::insertTopic(Utils::post('topic'), Utils::post('descr'), Utils::post('select'));
				$pid = self::insertPage($tid);
				self::editPage($pid);
			} catch (Exception $e) {
				Error::set("This topic name already exist, please choose another one.");
				Utils::goBack();
			}
		} else {
			print '<form id="register" action="index.php?page=topics&action=newtopic" method="post">
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
			print '<div id="register">No topics started yet. <a href="index.php?page=topics&action=newtopic">Write on a new topic.</a></div>';
		} else {
			print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
			<tbody>
				<tr>
					<td id="regtitle">
						<span style="float: left;">Topics</span>
						<span style="float: right;"><a href="index.php?page=topics&action=newtopic">Write on a new topic.</a></span>
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
										if (self::canSeeTopic($content['tId'])) {
										print '
											<tr>
												<td class="trow"><a href=index.php?page=topics&tid=' . $content['tId'] . '>' . $content['tTitle'] . '</a></td>
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
		if (!self::canSeeTopic($tId)) {
			print '<div id="register">You have no permission to see this topic.</div>';
			return;
		} else {
			$db = new db;
			$db->request('SELECT tTitle, pId, pTitle, pDesc, pCreation, pLastModif FROM topic as t, page as p WHERE p.tId = :tId AND t.tId = p.tId');
			$db->bind(':tId', $tId);
			$results = $db->getAllAssoc();
			if (empty($results)) {
				if (self::canEditPage($tId)) {
					print '<div id="register">No pages exist for this topic. <a href="index.php?page=topics&tid=' . $tId . '&action=newpage">Write a new page for this topic.</a></div>';
				} else {
					print '<div id="register">No pages exist for this topic.</div>';
				}
			} else {
				print '<table id="register" border="0" cellspacing="0" cellpadding="6" class="tborder">
				<tbody>
					<tr>
						<td id="regtitle">
							<span style="float: left;">' . $results[0]['tTitle'] . '</span>
							<span style="float: right;"><a href="index.php?page=topics&tid=' . $tId . '&action=newpage">Write a new page for this topic.</a></span>
						</td>
					</tr>
					<tr id="formcontent">
						<td>
							<fieldset>
								<table cellpadding="6" cellspacing="0" width=100%>
									<tbody>
										<tr>
											<td class="tcat">Page</td>
											<td class="tcat">Description</td>
											<td class="tcat">Creation Date</td>
											<td class="tcat">Last Modification Date</td>
										</tr>';
										foreach ($results as $thread => $content) {
											print '
												<tr>
													<td class="trow"><a href=index.php?page=topics&pid=' . $content['pId'] . '>' . $content['pTitle'] . '</a></td>
													<td class="trow">' . $content['pDesc'] . '</td>
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
	}

	public static function getPage($pid) {
		if (!self::canSeePage($pid)) {
			print '<div id="register">You have no permission to see this topic.</div>';
			return;
		} else {
			$db = new db;
			$db->request('SELECT t.tId, pId, pTitle, content, pDesc, pCreation, pLastModif FROM page as p, topic as t WHERE pId = :pid AND t.tId = p.tId;');
			$db->bind(':pid', $pid);
			$result = $db->getAssoc();
			if (empty($result)) {
				print '<div id="register">This page does not exist.</div>';
			} else {
				print '
				<table id="register" border="0" cellspacing="0" cellpadding="3" class="tborder">
					<tbody>
						<tr>
							<td id="regtitle">
								<span style="float:left;">' . $result['pTitle'] . '</span>
								<span style="float:right;">' . $result['pDesc'] . '</span>
							</td>
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
				if (self::canEditPage($result['tId']))
					self::editPage($result['pId'], $result['pTitle'], $result['pDesc'], $result['content']);
			}
		}
	}

	public static function editPage($pid, $title, $descr, $content) {
		print '
			<form id="register" action="index.php?page=topics&pid='.$pid.'&action=editpage" method="post">
				<table border="0" cellspacing="0" cellpadding="6" class="tborder">
					<tbody>
						<input type="hidden" name="pid" value="' . $pid . '">
						<tr>
							<td id="regtitle">Page edit</td>
						</tr>
						<tr>
							<td>Edit Page Title</td>
						</tr>
						<tr>
							<td><input type="text" name="title" id="title" maxlength="50" style="width: 100%" value="' . $title . '" required/>
						</tr>
						<tr>
							<td>Page Description</td>
						</tr>
						<tr>
							<td><input type="text" name="descr" id="descr" maxlength="50" style="width: 100%" value="' . $descr . '"/>
						</tr>
						<tr>
							<td>Edit Page Content</td>
						</tr>
						<tr>
							<td><textarea id="wikicontent" name="content" rows="15">' . $content . '</textarea></td>
						</tr>
					</tbody>
				</table>
				<div align="center" id="submit">
					<input id="submit_button" type="submit" value="submit" />
				</div>
			</form>';
	}
}

?>