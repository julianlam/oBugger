<?php
	require_once 'common_run.inc.php';

	class Auth {
		public function isLoggedIn() {
			if (isset($GLOBALS['obugger']['accountID']) && $GLOBALS['obugger']['accountID'] > 0) return $GLOBALS['obugger']['accountID'];
			else return 0;
		}

		public function login($username, $password) {
			$db = db();
			global $config;
			$user = $db->run(
				"SELECT * FROM users WHERE username=:username AND password=:password",
				array(":username" => $_POST['username'], ":password" => md5($_POST['password'] . $config['security']['password_salt']))
			)->fetch();
			if ($user) {
				$_SESSION['obugger'] = $user;
				$GLOBALS['obugger'] = $user;
				return 1;
			}
			else return 0;
		}

		public function logout() {
			unset($GLOBALS['obugger']);
			unset($_SESSION['obugger']);
		}
	}
?>
