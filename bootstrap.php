<?php
/**
 * Define application environment
 */
$env = (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', $env);

$autoloadComposer = __DIR__ . '/../../autoload.php';
if (file_exists($autoloadComposer)) {
    require_once $autoloadComposer;
}

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once 'Zend/Loader/Autoloader.php';
$loader = \Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Generator');
