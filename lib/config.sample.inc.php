<?php
	// Configuration file for obugger

	define('APPLICATION_LINK', "");	// Web accessible URL to oBugger (e.g. http://obugger.shadowytree.net/)
	define('ROOT_FOLDER', $_SERVER['DOCUMENT_ROOT']."/");	// Relative path to the application
	define('JAVASCRIPT_PATH', APPLICATION_LINK . "javascript/");
	define('LIBRARY_PATH', APPLICATION_LINK . "lib/");
	define('CSS_PATH', APPLICATION_LINK . "css/");
	define('IMG_PATH', APPLICATION_LINK . "images/");

	$_DB_CONFIG = array();
	$_DB_CONFIG['DB_PREFIX'] = "";	// Database Prefix for the database name
	$_DB_CONFIG['DB_NAME'] = "obugger";	// Database name
	$_DB_CONFIG['DB_HOST'] = "localhost";	// Database server hostname (localhost if unsure)
	$_DB_CONFIG['DB_DSN'] = 'mysql:host=' . $_DB_CONFIG['DB_HOST'] . ';dbname=' . (strlen($_DB_CONFIG['DB_PREFIX']) > 0 ? $_DB_CONFIG['DB_PREFIX'] . '_' : '') . $_DB_CONFIG['DB_NAME'];
	$_DB_CONFIG['DB_USER'] = 'obugger';	// Database username
	$_DB_CONFIG['DB_PASSWD'] = '';		// Database password

	// Access Restriction and Security Settings
	$config['security']['anon_access'] = array('r');	// Access levels for anonymous (non-logged-in users). Default: read-only
	$config['security']['auth_access'] = array('r', 'w');	// Access levels for logged-in users. Default: read-write
	$config['security']['password_salt'] = '';	// Put a whole bunch of random characters in here. This string is used to encrypt your passwords more securely.

	$config['priorities'] = array(
		array("Very Low", 'D6FFC2'),
		array("Low", 'BFFFE6'),
		array("Medium", 'FFEBA8'),
		array("High", 'FFD2A6'),
		array("Critical", 'ff8e8e'),
	);

	$config['states'] = array("Open", "Confirmed", "In Progress", "Resolved", "Closed");
?>
