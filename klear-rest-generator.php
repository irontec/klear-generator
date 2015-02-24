#!/usr/bin/php
<?php
/**
 * Genera controladores basicos para una api rest, de las tablas con comentario "[rest]"
 * Sobre cada metodo se generaran una serie de parametros por defecto para ayudar a la documentación
 * con la libreria "apidoc" https://github.com/calinrada/php-apidoc culla instalacón tiene que ser manual.
 * Este sistema no sobreescribe ficheros existentes.
 */

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

$currentPath = __DIR__;

define('VERSION', '0.1');
define('AUTHOR', 'ddniel16 <dani@irontec.com>');

try {

    $opts = new Generator_Getopt(
        array(
            'namespace|n-s' => 'Application namespace if none set the appnamespace is used'
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
    $namespace = $opts->getOption('namespace');
    if (!$namespace) {
        $zendConfig = new Zend_Config_Ini($applicationIni, $env);
        $namespace = $zendConfig->appnamespace;
    }

    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }

    if (!is_null($zendConfig->restConfig->path) || !is_null($zendConfig->restConfig->namespace)) {
        $restNamespace = $zendConfig->restConfig->namespace;
        $restPath = $zendConfig->restConfig->path . '/controllers';
    } else {
        throw new Exception(
            'En el applications.ini es necesario definir los paramatros "restConfig.namespace" y "restConfig.path"'
        );
    }

    $restFactory = new Generator_Rest_Factory(
        $restPath,
        $restNamespace,
        $namespace
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