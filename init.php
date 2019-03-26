<?php

// install directory
$globalBaseDir = __DIR__;

date_default_timezone_set('America/Denver'); // all dates stored in database are mountain

// includes
require_once($globalBaseDir . '/vendor/smarty/smarty/libs/Smarty.class.php');
require_once($globalBaseDir . '/libs/Wyolution/SmartyMLHelpers.php');

$f3 = require('vendor/bcosca/fatfree-core/base.php');

$f3->set('DEBUG',0);
$f3->set('_version','2.0.0');

$f3->set('_applicationName','NCSC');
// If app name isn't a suitable filename, change the following line
$f3->set('logFileRoot', 'ncsc');

$f3->set('AUTOLOAD',$globalBaseDir.'/;vendor/bcosca/fatfree-core/;vendor/smarty/libs/;libs/;/');
$f3->set('BASEDIR',$globalBaseDir);
$f3->set('LOCALES',$globalBaseDir.'/public/lang/');
$f3->set('cache',$globalBaseDir.'/data/cache');
$f3->set('RESOURCE_FOLDER',$globalBaseDir.'/resources/');
$f3->set('UPLOADS','data/uploads/');
$f3->set('UNLOAD','\\Wyolution\\F3Helpers::executionTime');

// read defaults
$f3->config('configs/defaults.ini');

if (file_exists('configs/local.ini')) {
	$f3->config('configs/local.ini');
}

// After the app configuration is all sorted out, we will know what our own debug level is
// and can set F3's
if (\Wyolution\Logger::getLogLevel() == \Wyolution\Logger::DEBUG) {
	$f3->set('DEBUG', 1);
} 
else {
	$f3->set('DEBUG', 0);
}


// init database
$f3->set('DB',
	new DB\SQL(
	'mysql:host=' . $f3->get('dbHost') . ';port=' . $f3->get('dbPort') . ';dbname=' . $f3->get('dbName'),
	$f3->get('dbUsername'),
	$f3->get('dbPassword')
	)
);

// setup smarty
$f3->set(
	'smarty',
	new \Wyolution\SmartyML( $globalBaseDir.'/data', $globalBaseDir.'/views', $globalBaseDir.'/locales', 'en', $f3->get('smartyCache')));


// set up the app-specific smarty viewhelper object if there is one
$f3->set(
	'viewHelper',
	new \NCSC\ViewHelper()
);

// set up the app-specific permissions
$f3->set(
    'appPermissions',
    new \NCSC\Permissions()
);

// set up the app-specific permissions
$f3->set(
    'appConstants',
    new \NCSC\Constants()
);

?>
