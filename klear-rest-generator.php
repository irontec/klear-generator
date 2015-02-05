#!/usr/bin/php
<?php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

$currentPath = __DIR__;
$svnRevision = `svnversion $currentPath`;

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

    /**
     * Sistema actual en uso, no sobreescribe ficheros existentes
     */

    if (!is_null($zendConfig->restPath)) {
        $restDir = $zendConfig->restPath . '/controllers';
    } else {
        throw new Exception('Falta "restPath" en el applications.ini');
    }

    $restFactory = new Generator_Rest_Factory($restDir, $namespace);
    $restFactory->createFiles();

    /**
     * Guardamos la revisión del svn actual
     */
    $svnData = '[' . date('r') . ']' . ' revision: ' . $svnRevision;
    file_put_contents($restDir . '/generator.log', $svnData, FILE_APPEND);

} catch (Zend_Console_Getopt_Exception $e) {

    echo $e->getUsageMessage() .  "\n";
    echo $e->getMessage() . "\n";
    exit(1);

} catch (Exception $e) {

    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);

}