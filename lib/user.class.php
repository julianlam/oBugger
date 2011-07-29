<?php
	require_once 'common_run.inc.php';
	include_once 'auth.class.php';

	class User {
		public function getUserInfo() {
			$db = db();
			$auth = new Auth();

			$accountID = $auth->isLoggedIn();
			if ($accountID > 0) {
				return $db->run("SELECT accountID, username FROM users WHERE accountID=:accountID", array("accountID" => $accountID))->fetch();
			}
		}

		public function changePassword($current, $new) {
			$db = db();
			$auth = new Auth();
			global $config;

			$accountID = $auth->isLoggedIn();
			if ($accountID > 0) {
				$current_password = $db->run("SELECT password FROM users WHERE accountID=:accountID", array("accountID" => $accountID))->fetch();
				if ($current_password['password'] == md5($current . $config['security']['password_salt'])) {
					$db->run("UPDATE users SET password=:password WHERE accountID=:accountID", array("accountID" => $accountID, "password" => md5($new . $config['security']['password_salt'])));
					$status = $db->errorInfo();
					if ($status[0] == '00000') return 1;
					else return 0;
				}
				else return 0;
			}
		}
	}
?>
