<?php
	require_once 'common_run.inc.php';

	class Bugs {
		public function get_bugs($bugIDs=null) {
			$db = db();
			if ($bugIDs != null) {
				if (!is_array($bugIDs)) $bugIDs = array($bugIDs);
				$bugID_sql = '';
				foreach($bugIDs as $bugID) {
					$bugID = intval($bugID);
					if ($bugID > 0) $bugID_sql .= (strlen($bugID_sql) > 0 ? ' OR ' : '') . "bugID='$bugID'";
				}
				$bugs = $db->run("SELECT * FROM bugs WHERE $bugID_sql")->fetchall(PDO::FETCH_ASSOC);
				return $bugs;
			}
			else {
				// Gets a listing of all bugs, sorted into two arrays, open and closed
				$open_bugs = $db->run("SELECT * FROM bugs WHERE state != 'closed' ORDER BY fileDate DESC")->fetchall(PDO::FETCH_ASSOC);
				$closed_bugs = $db->run("SELECT * FROM bugs WHERE state = 'closed' ORDER BY fileDate DESC")->fetchall(PDO::FETCH_ASSOC);

				return array("open" => $open_bugs, "closed" => $closed_bugs);
			}
		}

		public function add_bug($payload) {
			$db = db();
			$bug_info = json_decode($payload,true);
			$add = $db->run(
				"INSERT INTO bugs VALUES (DEFAULT, :name, :description, 'open', :priority, 0, " . time() . ", " . time() . ")",
				array(":name" => rawurldecode($bug_info['name']), ":description" => rawurldecode($bug_info['description']), ":priority" => $bug_info['priority'])
			);
			$bugID = $db->last();
			$status = $db->errorInfo();

			if ($status[0] == '00000') return array("status" => 1, "bugID" => $bugID, "name" => stripslashes($bug_info['name']), "description" => stripslashes($bug_info['description']), "priority" => ucwords(str_replace("_"," ",$bug_info['priority'])), "date" => date('r'));
			else return array("status" => 0);
		}

		public function modify_bug($payload) {
			$db = db();
			$bug_info = json_decode($payload,true);
			$modify = $db->run(
				"UPDATE bugs SET name=:name, description=:description, state=:state, priority=:priority, lastUpdated=:lastUpdated WHERE bugID=:bugID",
				array("bugID" => $bug_info['bugID'], ":name" => rawurldecode($bug_info['name']), ":description" => rawurldecode($bug_info['description']), ":state" => $bug_info['state'], ":priority" => $bug_info['priority'], "lastUpdated" => time())
			);
			$status = $db->errorInfo();

			if ($status[0] == '00000') return 1;
			else return 0;
		}
	}
?>
