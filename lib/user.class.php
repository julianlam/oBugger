<?php
	require_once 'common_run.inc.php';
	include_once 'auth.class.php';

	class User {
		public function getUserInfo() {
			$db = db();
			$auth = new Auth();

			$accountID = $auth->isLoggedIn();
			if ($accountID > 0) {
				return $db->run("SELECT accountID, username FROM users WHERE accountID=:accountID", array("accountID" => $accountID))->fetchall();
			}
		}
	}
?>
