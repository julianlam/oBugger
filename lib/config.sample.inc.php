<?php
	// Configuration file for obugger

	define('APPLICATION_LINK', "");	// Web accessible URL to oBugger (e.g. http://obugger.shadowytree.net/)
	define('ROOT_FOLDER', $_SERVER['DOCUMENT_ROOT']."/obugger/");	// Relative path to the application
	define('JAVASCRIPT_PATH', APPLICATION_LINK . "js/");
	define('CSS_PATH', APPLICATION_LINK . "css/");
	define('IMG_PATH', APPLICATION_LINK . "images/");

	$_DB_CONFIG = array();
	$_DB_CONFIG['DB_PREFIX'] = "";	// Database Prefix for the database name
	$_DB_CONFIG['DB_NAME'] = "obugger";	// Database name
	$_DB_CONFIG['DB_HOST'] = "localhost";	// Database server hostname (localhost if unsure)
	$_DB_CONFIG['DB_DSN'] = 'mysql:host=' . $_DB_CONFIG['DB_HOST'] . ';dbname=' . (strlen($_DB_CONFIG['DB_PREFIX']) > 0 ? $_DB_CONFIG['DB_PREFIX'] . '_' : '') . $_DB_CONFIG['DB_NAME'];
	$_DB_CONFIG['DB_USER'] = 'obugger';	// Database username
	$_DB_CONFIG['DB_PASSWD'] = '';		// Database password

	$config['priorities'] = array(
		array("Very Low", 'b8f4fc'),
		array("Low", 'b8fcca'),
		array("Medium", 'f2fcb8'),
		array("High", 'fac4af'),
		array("Critical", 'ff6e6e'),
	);

	$config['states'] = array("Open", "Confirmed", "In Progress", "Resolved", "Closed");
?>
