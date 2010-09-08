<?php

define('SYS_DIR', realpath('.'));
define('WWW_DIR', realpath('.'));
define('APP_DIR', SYS_DIR.'/application');
define('LIB_DIR', SYS_DIR.'/lib');
define('CONFIG_DIR', APP_DIR.'/etc');
define('DOMAIN', $_SERVER['HTTP_HOST']);

/**
 * a dynamic way to get the right BASE URL (only for linux I guess)
 */


require(CONFIG_DIR.'/config.inc.php');
require(LIB_DIR.'/util/functions.inc.php');
require(LIB_DIR.'/util/util.class.php');


//test($calledFilename);
//test($_SERVER);

set_error_handler('__errorHandler', E_ALL);
set_exception_handler('__exceptionHandler');

// import modules
Util::import(LIB_DIR.'/blabla');// HACK
Util::import(LIB_DIR.'/controller');
Util::import(LIB_DIR.'/data');
Util::import(LIB_DIR.'/formmapper');
Util::import(LIB_DIR.'/general');
Util::import(LIB_DIR.'/util');
Util::import(LIB_DIR.'/service');
Util::import(LIB_DIR.'/view');

Util::importModules(APP_DIR.'/modules');

Conf::setServer(DOMAIN);
Conf::setDirectory(CONFIG_DIR);

// Set lang dir.
// Change if you like in the config file
Lang::setDirectory(Conf::get('general.dir.lang'));
Lang::setLang(Conf::get('general.default_lang'));

View::setTemplateDirectory(Conf::get('general.dir.templates'));

// Only load when DB is needed?
// so call when a DB is asked. The Datafactory should lookup the right database
// Load the DB
$oDatabase = new PDO(Conf::get('database.dbtype').':dbname='.Conf::get('database.dbname').';host='.Conf::get('database.dbhost'),
					Conf::get('database.dbuser'),
					Conf::get('database.dbpass'));

$oDataFactory = DataFactory::getInstance();
$oDataFactory->addConnection($oDatabase, DataFactory::C_DEFAULT_DB);

define('WWW_URL', Conf::get('general.url.www'));

// language settings
setlocale(LC_ALL, Conf::get('locale.language'));

// set timezone settings
date_default_timezone_set(Conf::get('locale.timezone'));

function showActiveClass($page, $link) {
	if ($page == $link) {
		echo ' active ';
	} else {
		echo '';
	}
}


