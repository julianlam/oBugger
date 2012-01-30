<?php
	require_once 'common_run.inc.php';
	require_once ROOT_FOLDER . 'lib/markdown/markdown.php';

	class Bugs {
		public function get_bugs($bugIDs=null, $markdown=1, $keyed=0) {
			$db = db();
			if ($bugIDs != null) {
				if (!is_array($bugIDs)) $bugIDs = array($bugIDs);
				$bugID_sql = '';
				foreach($bugIDs as $bugID) {
					$bugID = intval($bugID);
					if ($bugID > 0) $bugID_sql .= (strlen($bugID_sql) > 0 ? ' OR ' : '') . "bugID='$bugID'";
				}
				$bugs = $db->run("SELECT bugs.*, u1.username AS assignee, u2.username AS filedBy FROM bugs LEFT JOIN users u1 ON bugs.assignedTo=u1.accountID LEFT JOIN users u2 ON bugs.filedBy=u2.accountID WHERE $bugID_sql")->fetchall(PDO::FETCH_ASSOC);
				if ($markdown == 1) {
					$bug_count = count($bugs);
					for($i=0;$i<$bug_count;$i++) {
						$bugs[$i]['description'] = Markdown($bugs[$i]['description']);
					}
				}
				if ($keyed == 1) {
					foreach($bugs as $bug) $keyed_bugs[$bug['bugID']] = $bug;
					$bugs = $keyed_bugs;
				}
				return $bugs;
			}
			else {
				// Gets a listing of all bugs, sorted into two arrays, open and closed
				$open_bugs = $db->run("SELECT bugs.*, u1.username AS assignee, u2.username AS filedB FROM bugs LEFT JOIN users u1 ON bugs.assignedTo=u1.accountID LEFT JOIN users u2 ON bugs.filedBy=u2.accountID WHERE state != 'closed' ORDER BY fileDate DESC")->fetchall(PDO::FETCH_ASSOC);
				$closed_bugs = $db->run("SELECT bugs.*, u1.username AS assignee, u2.username AS filedB FROM bugs LEFT JOIN users u1 ON bugs.assignedTo=u1.accountID LEFT JOIN users u2 ON bugs.filedBy=u2.accountID WHERE state = 'closed' ORDER BY fileDate DESC")->fetchall(PDO::FETCH_ASSOC);
				if ($markdown == 1) {
					$open_bug_count = count($open_bugs);
					for($i=0;$i<$open_bug_count;$i++) {
						$open_bugs[$i]['description'] = Markdown($open_bugs[$i]['description']);
					}
					$closed_bug_count = count($closed_bugs);
					for($i=0;$i<$closed_bug_count;$i++) {
						$closed_bugs[$i]['description'] = Markdown($closed_bugs[$i]['description']);
					}
				}
				if ($keyed == 1) {
					foreach($open_bugs as $bug) $open_bugs_keyed[$bug['bugID']] = $bug;
					foreach($closed_bugs as $bug) $closed_bugs_keyed[$bug['bugID']] = $bug;
					$open_bugs = $open_bugs_keyed;
					$closed_bugs = $closed_bugs_keyed;
				}

				return array("open" => $open_bugs, "closed" => $closed_bugs);
			}
		}

		public function add_bug($payload) {
			$db = db();
			$bug_info = json_decode($payload,true);

			$add = $db->run(
				"INSERT INTO bugs VALUES (DEFAULT, :name, :description, 'open', :priority, :filer, 0, " . time() . ", " . time() . ")",
				array(":name" => rawurldecode($bug_info['name']), ":description" => rawurldecode($bug_info['description']), ":priority" => $bug_info['priority'], "filer" => $bug_info['filer'])
			);
			$bugID = $db->last();
			$status = $add->errorInfo();

			if ($status[0] == '00000') return array("status" => 1, "bugID" => $bugID, "name" => stripslashes($bug_info['name']), "description" => stripslashes($bug_info['description']));
			else return array("status" => 0, "dump" => $status);
		}

		public function modify_bug($payload, $modifierID) {
			$db = db();
			$bug_info = json_decode($payload,true);
			if ($modifierID != $bug_info['assignee']) {
				$old_info = array_shift($this->get_bugs(array($bug_info['bugID']), 0));
				$changes = array();
				foreach($bug_info as $option => $value) {
					if ($option == 'assignee') $option = 'assignedTo';	// Handling an exception in expected info... grr...
					if ($value != $old_info[$option]) {
						$changes[$option]['old'] = $old_info[$option];
						$changes[$option]['new'] = $value;
					}
				}
			}
			$modify = $db->run(
				"UPDATE bugs SET name=:name, description=:description, state=:state, priority=:priority, assignedTo=:assignedTo, lastUpdated=:lastUpdated WHERE bugID=:bugID",
				array("bugID" => $bug_info['bugID'], ":name" => rawurldecode($bug_info['name']), ":description" => rawurldecode($bug_info['description']), ":state" => $bug_info['state'], ":priority" => $bug_info['priority'], "assignedTo" => $bug_info['assignee'], "lastUpdated" => time())
			);
			$status = $db->errorInfo();

			if ($status[0] == '00000') {
				if (sizeOf($changes) > 0 && $modifierID != $bug_info['assignedTo']) $this->mail_bug_notif($bug_info['bugID'], $changes);
				return 1;
			}
			else return 0;
		}

		public function mail_bug_notif($bugID, $changes) {
			// Necessary library to retrieve user information
			require '../lib/user.class.php';
			$bug_info = array_shift($this->get_bugs(array($bugID)));
			$user = new User();
			$account = $user->getUserInfo($bug_info['assignedTo']);

			if (strlen($account['email']) > 0) {
				// Enabling HTML mail
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: oBugger <noreply@' . HOSTNAME . '>' . "\r\n";
				$to = $bug_info['assignee'] . '<' . $account['email'] . '>';
				$subject = '[oBugger] Bug #' . $bugID . ' - ' . substr($bug_info['name'], 0, 29) . '...';

				$changes_html = '';
				foreach($changes as $option => $value) {
					if ($option == 'assignedTo' && $value['old'] == 0) $changes_html .= '<li>You were assigned to this bug</li>';
					else if ($option == 'assignedTo' && $value['old'] != 0) $changes_html .= '<li>' . $option . ': <i>' . $value['old'] . '</i> <strong>was changed to</strong> <i>' . $value['new'] . '</i> (This is you!)</li>'; 
					else $changes_html .= '<li>' . $option . ': <i>' . $value['old'] . '</i> <strong>was changed to</strong> <i>' . $value['new'] . '</i></li>'; 
				}

				$body = "
					<body style=\"font-size: 12px; font-size: sans, arial, tahoma, verdana; color: #333;\">
						<p>
							{$account['username']},
						</p>
						<p>
							The bug \"{$bug_info['name']}\" was updated, and the following information was changed:
							<ul>
								{$changes_html}
							</ul>
						</p>
						<p>
							You may view this bug here: <a href=\"" . APPLICATION_LINK . "#?bugID={$bugID}\">" . APPLICATION_LINK . "#?bugID={$bugID}</a>
						</p>
						<p>
							You are receiving this email because this bug was modified by someone other than yourself, and you are the user assigned to it.
						</p>
						<p>
							Thanks,<br /><i>oBugger</i>
						</p>
					</body>
				";

				mail($to, $subject, $body, $headers);
			}
		}
	}
?>
