<?php
	//error_reporting(E_ALL);
	include 'lib/common_run.inc.php';

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
			if ($_GET['action'] == 'logout') {
				unset($GLOBALS['obugger']);
				unset($_SESSION['obugger']);
			}
			else if ($_GET['action'] == 'login') {
				$user = $db->run(
					"SELECT * FROM users WHERE username=:username AND password=:password",
					array(":username" => $_POST['username'], ":password" => md5($_POST['password']))
				)->fetch();
				if ($user) {
					$params['status'] = 1;
					$_SESSION['obugger'] = $user;
					$GLOBALS['obugger'] = $user;
				}
				else $params['status'] = 0;
			}

			$params['priorities'] = $config['priorities'];
			$params['states'] = $config['states'];
			$params['bugs'] = $db->run("SELECT * FROM bugs WHERE state != 'closed' ORDER BY fileDate DESC")->fetchall(PDO::FETCH_ASSOC);
			$params['closed_bugs'] = $db->run("SELECT * FROM bugs WHERE state = 'closed' ORDER BY fileDate DESC")->fetchall(PDO::FETCH_ASSOC);

			if (isset($_GET['slim'])) render('index/index.php', $params, 0);
			else render('index/index.php', $params);
		break;
	}
?>
