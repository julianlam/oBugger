<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>oBugger!</title>
		<link rel="stylesheet" href="css/cssloader.php" type="text/css" />
		<link rel="stylesheet" href="<?=LIBRARY_PATH?>MUX.Dialog/MUX.Dialog.css" type="text/css" />
		<script type="text/javascript" src="<?=JAVASCRIPT_PATH?>jsloader.php"></script>
		<script type="text/javascript" src="<?=LIBRARY_PATH?>mootools.js"></script>
		<script type="text/javascript" src="<?=LIBRARY_PATH?>mootools-more.js"></script>
		<script type="text/javascript" src="<?=LIBRARY_PATH?>MUX.Dialog/MUX.Dialog.js"></script>
	</head>
	<body>
		<div class="header">
			<div id="loginState">
				<?php
					if (isset($GLOBALS['obugger']['accountID']) && $GLOBALS['obugger']['accountID'] > 0) echo "<b>Logged in as " . $GLOBALS['obugger']['username'] . '</b> | <a onclick="settings_modal.open();">Settings</a> | <a href="?action=logout">Logout</a>';
					else echo '<form action="?action=login" method="POST">Username: <input type="text" name="username" /> &nbsp; Password: <input type="password" name="password" /> &nbsp; <input type="submit" value="login" /></form>';
				?>
			</div>
			<!--<h3>oBugger!</h3>-->
			<a href="?action=showbugs"><img border="0" src="<?=IMG_PATH?>logo.svg" title="oBugger!" /></a>
		</div>


