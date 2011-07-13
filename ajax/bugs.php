<?php
	require_once '../lib/common_run.inc.php';

	switch($_POST['action']) {
		case 'getbug':
			$db = db();
			$bugID = $_POST['bugID'];
			$bug_info = $db->run("SELECT * FROM bugs WHERE bugID='$bugID'")->fetch();
			if ($bug_info) echo json_encode(array("status" => 1, "data" => $bug_info));
			else echo json_encode(array("status" => 0, "comment" => "Bug info could not be retrieved"));
		break;
		case 'newbug':
			$db = db();
			$bug_info = json_decode(stripslashes($_POST['payload']),true);
			$add = $db->run(
				"INSERT INTO bugs VALUES (DEFAULT, :name, :description, 'open', :priority, " . time() . ", " . time() . ")",
				array(":name" => $bug_info['name'], ":description" => $bug_info['description'], ":priority" => $bug_info['priority'])
			);
			$bugID = $db->last();
			$status = $db->errorInfo();

			if ($status[0] == '00000') echo json_encode(array("status" => 1, "comment" => "Bug successfully added", "bugID" => $bugID, "name" => $bug_info['name'], "description" => $bug_info['description'], "priority" => ucwords(str_replace("_"," ",$bug_info['priority'])), "date" => date('r')));
			else echo json_encode(array("status" => 0, "comment" => "Bug could not be added to database"));
		break;
		case 'modifybug':
			$db = db();
			$bug_info = json_decode(stripslashes($_POST['payload']),true);
			$modify = $db->run(
				"UPDATE bugs SET name=:name, description=:description, state=:state, priority=:priority, lastUpdated=:lastUpdated WHERE bugID=:bugID",
				array("bugID" => $bug_info['bugID'], ":name" => $bug_info['name'], ":description" => $bug_info['description'], ":state" => $bug_info['state'], ":priority" => $bug_info['priority'], "lastUpdated" => time())
			);
			$status = $db->errorInfo();

			if ($status[0] == '00000') echo json_encode(array("status" => 1, "comment" => "Bug successfully modified"));
			else echo json_encode(array("status" => 0, "comment" => "Bug could not be modified"));
		break;
		default:
			echo json_encode(array("status" => 0, "comment" => "No action received"));
		break;
	}
?>
