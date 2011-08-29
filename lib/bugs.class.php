<?php
	require_once 'common_run.inc.php';
	require_once ROOT_FOLDER . 'lib/markdown/markdown.php';

	class Bugs {
		public function get_bugs($bugIDs=null, $markdown=1) {
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

			if ($status[0] == '00000') return array("status" => 1, "bugID" => $bugID, "name" => stripslashes($bug_info['name']), "description" => stripslashes($bug_info['description']), "priority" => ucwords(str_replace("_"," ",$bug_info['priority'])), "date" => date('r'));
			else return array("status" => 0, "dump" => $status);
		}

		public function modify_bug($payload) {
			$db = db();
			$bug_info = json_decode($payload,true);
			$modify = $db->run(
				"UPDATE bugs SET name=:name, description=:description, state=:state, priority=:priority, assignedTo=:assignedTo, lastUpdated=:lastUpdated WHERE bugID=:bugID",
				array("bugID" => $bug_info['bugID'], ":name" => rawurldecode($bug_info['name']), ":description" => rawurldecode($bug_info['description']), ":state" => $bug_info['state'], ":priority" => $bug_info['priority'], "assignedTo" => $bug_info['assignee'], "lastUpdated" => time())
			);
			$status = $db->errorInfo();

			if ($status[0] == '00000') return 1;
			else return 0;
		}
	}
?>
