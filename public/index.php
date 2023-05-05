<?php
// disable strict std
#error_reporting(E_ALL  ^ E_DEPRECATED & ~E_STRICT & ~E_WARNING);
#error_reporting(0); 
 

date_default_timezone_set('Europe/Rome');

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define path to public directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Define application environment  
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
// Ensure library/ is on include_path
set_include_path(
	implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/forms'),
    realpath(APPLICATION_PATH . '/services'),
    get_include_path(),
)));

include_once  APPLICATION_PATH . '/configs/constant.inc.php' ;
include_once  APPLICATION_PATH . '/configs/config.inc.php' ;

// funzioni varie customizzate
include 'functions.php';

/** Zend_Application */
require_once  'Zend/Application.php';
require_once  'Zend/Registry.php';
require_once  'Prisma/Config.php';
require_once  'Prisma/Logger.php';
$ini_file = Prisma_Config::getConfig();

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/' . $ini_file
);
 
$frontController = Zend_Controller_Front::getInstance();
$frontController->registerPlugin( new Prisma_Controller_Plugin_Acl() );
$frontController->registerPlugin( new Prisma_Controller_Plugin_LayoutSwitch() );
$frontController->registerPlugin( new Prisma_Controller_Plugin_Configuration() );

// MENU NAVIGATION
// @27/04/2021
//metto qui il caricamento dei menu anzichè nel Bootstrap.php
$madmin = include APPLICATION_PATH . '/configs/menu/admin.php' ;
$muser  = include APPLICATION_PATH . '/configs/menu/user.php' ;
$msost  = include APPLICATION_PATH . '/configs/menu/sostituto.php' ;
Zend_Registry::set('navAdmin', $madmin);
Zend_Registry::set('navUser', $muser);
Zend_Registry::set('navSost', $msost);


//START APP
$application->bootstrap()->run();
