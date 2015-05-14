#!/usr/bin/php
<?php
/**
 * @author ddniel16 <dani@irontec.com>
 */

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

$currentPath = __DIR__;

define('VERSION', '0.1');
define('AUTHOR', 'ddniel16 <dani@irontec.com>');

try {

    $opts = new Generator_Getopt(
        array(
            'namespace|n-s' => 'Application namespace'
        )
    );

    $opts->parse();
    $opts->checkRequired();
    $env = $opts->getEnviroment();

    $applicationIni = APPLICATION_PATH . '/configs/application.ini';

    /**
     * Init Db
     */
    $application = new Zend_Application($env, $applicationIni);
    $application->bootstrap('db');

    /**
     * Get namespace
     */

    $zendConfig = new Zend_Config_Ini($applicationIni, $env);
    $namespace = $zendConfig->get('appnamespace');

    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }

    $msgError = 'Faltan los parametros de configuración "restConfig"';

    $restConfig = $zendConfig->restConfig;
    if (is_null($restConfig)) {
        throw new Exception(
            $msgError
        );
    }

    $includePaths = $zendConfig->get('includePaths');
    $pathLibrary = $includePaths->library;

    $restFactory = new Generator_Rest_Factory(
        $restConfig,
        $namespace,
        $pathLibrary
    );

    $restFactory->start();

} catch (Zend_Console_Getopt_Exception $e) {

    echo $e->getUsageMessage() .  "\n";
    echo $e->getMessage() . "\n";
    exit(1);

} catch (Exception $e) {

    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);

}