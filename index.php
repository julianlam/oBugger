<?php
	//error_reporting(E_ALL);
	include 'lib/common_run.inc.php';
	include 'lib/bugs.class.php';

	if (!isset($_GET['action'])) $_GET['action'] = '';
	switch($_GET['action']) {
		case 'editbug':
			$db = db();
			if (!$_POST['save']) {
				$params['bug'] = $db->run(
					"SELECT * FROM bugs WHERE bugID=:bugID",
					array(":bugID" => $_GET['bugID'])
				)->fetch(PDO::FETCH_ASSOC);
				$params['priorities'] = $config['priorities'];
				$params['states'] = $config['states'];

				if (isset($_GET['slim'])) render('index/index.php', $params, 0);
				else render('index/index.php', $params);
			}
			else {
				$params['status'] = $db->run(
					"UPDATE bugs SET name=:name, description=:description, priority=:priority, state=:state WHERE bugID=:bugID",
					array(":name" => $_POST['name'], ":description" => $_POST['description'], ":priority" => $_POST['priority'], ":state" => $_POST['state'], ":bugID" => $_POST['bugID'])
				);
				$params['prevpage'] = 'editbug';

				header('Location: ' . APPLICATION_LINK . '?action=showbugs');
			}
		break;
		case 'closebug':
			$db = db();
			$params['status'] = $db->run(
				"UPDATE bugs SET state='closed' WHERE bugID=:bugID",
				array(":bugID" => $_GET['bugID'])
			);
			$params['prevpage'] = 'closebug';

			header('Location: ' . APPLICATION_LINK . '?action=showbugs');
		break;
		case 'reopenbug':
			$db = db();
			$params['status'] = $db->run(
				"UPDATE bugs SET state='open' WHERE bugID=:bugID",
				array(":bugID" => $_GET['bugID'])
			);
			$params['prevpage'] = 'reopenbug';

			header('Location: ' . APPLICATION_LINK . '?action=showbugs');
		break;
		default:
			$db = db();
			include 'lib/auth.class.php';
			$auth = new Auth();
			$bugs = new Bugs();

			if ($_GET['action'] == 'logout') {
				$auth->logout();
			}
			else if ($_GET['action'] == 'login') {
				$user = $auth->login($_POST['username'], $_POST['password']);
				if ($user) {
					$params['status'] = 1;
					$params['loggedIn'] = 1;
				}
				else $params['status'] = 0;
			}
			else $params['loggedIn'] = $auth->isLoggedIn();

			$params['config'] = array("anon_access" => $config['security']['anon_access'], "auth_access" => $config['security']['auth_access']);

			if (in_array('r', $config['security']['anon_access']) || ($params['loggedIn'] == 1 && in_array('r', $config['security']['auth_access']))) {
				$params['priorities'] = $config['priorities'];
				$params['states'] = $config['states'];
				$bugs_list = $bugs->get_bugs();
				$params['bugs'] = $bugs_list['open'];
				$params['closed_bugs'] = $bugs_list['closed'];
			}

			if (isset($_GET['slim'])) render('index/index.php', $params, 0);
			else render('index/index.php', $params);
		break;
	}
?>
