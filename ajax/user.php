<?php
	require_once '../lib/common_run.inc.php';
	require '../lib/user.class.php';

	switch($_REQUEST['action']) {
		case 'getSettings':
			$user = new User();
			$settings = $user->getUserInfo();
			echo json_encode(array("status" => 1, "data" => $settings));
		break;
		case 'changePassword':
			$user = new User();
			$payload = json_decode($_POST['payload'], true);
			$settings = $user->changePassword($payload['current'], $payload['new']);

			echo json_encode(array("status" => $settings));
		break;
		default:
			echo json_encode(array("status" => 0, "comment" => "No action received"));
		break;
	}
?>
