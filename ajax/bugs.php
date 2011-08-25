<?php
	require_once '../lib/common_run.inc.php';
	require_once '../lib/bugs.class.php';
	$bugs = new Bugs();

	switch($_REQUEST['action']) {
		case 'getbug':
			$db = db();
			$bugID = $_POST['bugID'];
			$bug_info = $bugs->get_bugs($bugID);
			$bug_info = reset($bug_info);
			$bug_info['priority_nice'] = ucwords(str_replace('_', ' ', $bug_info['priority']));
			$bug_info['state_nice'] = ucwords(str_replace('_', ' ', $bug_info['state']));
			if ($bug_info) echo json_encode(array("status" => 1, "data" => $bug_info));
			else echo json_encode(array("status" => 0, "comment" => "Bug info could not be retrieved"));
		break;
		case 'render_bug_viewer':
			echo '
				<div style="margin-top: 4.5em; font-weight: bold; text-align: center; font-size: 12px;">Loading &nbsp; <img style="position: relative; top: 4px;" src="' . IMG_PATH . 'loader.gif">
			';
		break;
		case 'newbug':
			$add_bug = $bugs->add_bug($_POST['payload']);

			if ($add_bug['status'] == 1) echo json_encode(array("status" => 1, "comment" => "Bug successfully added", "bugID" => $add_bug['bugID'], "name" => stripslashes($add_bug['name']), "description" => stripslashes($add_bug['description']), "priority" => ucwords(str_replace("_"," ",$add_bug['priority'])), "date" => date('r')));
			else echo json_encode(array("status" => 0, "comment" => "Bug could not be added to database"));
		break;
		case 'modifybug':
			$modify_bug = $bugs->modify_bug($_POST['payload']);

			if ($modify_bug) echo json_encode(array("status" => 1, "comment" => "Bug successfully modified"));
			else echo json_encode(array("status" => 0, "comment" => "Bug could not be modified"));
		break;
		default:
			echo json_encode(array("status" => 0, "comment" => "No action received"));
		break;
	}
?>
