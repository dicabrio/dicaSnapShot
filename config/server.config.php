<?php
/**
 * 
 *	Development server control panel.
 *	Version 0.1, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *	
 *	This configuration contains things like database configuration.
 *
 */

// Database settings.
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', 'DCrob1981');

define('MYSQL_DB', 'dev-server');

define('SNAP_PATH', '/Users/robertcabri/Sites/snapshots/');
//define('ADUSREV_FILE', '/usr/projects/ge/temp/adusrev');
define('ADUSREV_FILE', 'C:/projects/torpia2/git/adusrev');
//define('MYSQL_LOC', '/usr/bin/');
define('MYSQL_LOC', '/usr/local/mysql/bin/');
//define('MYSQL_LOC', 'C:/wamp/bin/mysql/mysql5.1.36/bin/');
//define('MYSQL_LOC', '');


// Snapshot settings
define('FTP_HOST', '172.16.112.6');
define('FTP_USER', 'snapshot');
define('FTP_PASS', 'snapshot');
define('FTP_DIR', '/');


?>
