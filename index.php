<?php

/**
 * 
 *	Development server control panel.
 *	Version 0.1, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *	Version 0.2, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *	Version 0.3, written by Dennis Futselaar <dennis.futselaar@webgamic.nl> & 
 *							Gabor de Mooij <gabor.de.mooij@webgamic.nl>
 *	
 *	This page is used for controlling the development image, like:
 *
 *	-	Branch switching
 *	-	Snapshotting.
 * 
 * 	This script assumes, as all the other modules, that sudo is available and properly installed for use.
 * 	
 */

set_time_limit(0);

require_once('config/server.config.php');
require_once('config/modules.config.php');

date_default_timezone_set('Europe/Berlin');

define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/cp/');

// Connect to the MySQL server
mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die('Error: your MySQL server is not properly setup.');
mysql_select_db(MYSQL_DB);

// Get module name
$sModule	= (isset($_GET['module']) ? $_GET['module'] : 'general');

// Perform startup sanity checking
if (!isset($aModules['general'])) {
	echo 'Error: General module is not found.';
	exit;
}

// If the module does not exist, set module to general.
if (!isset($aModules[$sModule])) {
	$sModule = 'general';
}
$oSnapshot = "";
// Get page name
$sPage	= $aModules[$sModule]['vc'];
$sTitle	= ucfirst($sModule);

// Get all the output
//ob_start();
 
if (file_exists('vc/'.$sPage)) {
	
	// Let the script continue.
	$bContinue = true;
	
	// Load modules
	if (is_array($aModules[$sModule]['models'])) {
		foreach ($aModules[$sModule]['models'] as $sModelName => $sModelFilename) {
			if (!file_exists('models/'.$sModelFilename)) {
				$bContinue	= false;
				echo 'Error: Cannot find file '.$sModelFilename.'!<br>';
			}
			elseif ($bContinue) {

				require_once('models/'.$sModelFilename);

				// This can be a lot more graceful than its current form
				//
				$sModelName				= ucfirst($sModelName);
				$sObjectName			= 'o'.$sModelName;
				$$sObjectName			= new $sModelName;
			}
		}
	}
	
	if ($bContinue) {
		require_once('vc/'.$sPage);
	}
}
else {
	echo 'The module '.$sPage. ' does not seem to exist. Perhaps a faulty link?';
}
