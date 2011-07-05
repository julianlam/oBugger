<?php 
	session_start();
	require_once 'config.inc.php';
	require_once 'core.class.php';

	function render($file_name, $params = array(), $content=1) {
		extract($params);
		ob_start();
		include "templates/" . $file_name;
		$template = ob_get_contents();
		ob_end_clean();
		if ($content==1) include "templates/header.php";
		echo $template;
		if ($content==1) include "templates/footer.php";
	}

	// Save the user's lingobuddy information to the PHP session
	if (isset($_SESSION['obugger'])) $GLOBALS['obugger'] = $_SESSION['obugger'];
?>
