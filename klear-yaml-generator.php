#!/usr/bin/php
<?php
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define('VERSION', '0.1');
define('AUTHOR',  'Alayn Gortazar <alayn@irontec.com>');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Generator');

try {
    $opts = new Zend_Console_Getopt(
            array(
                    'application|a=s' => 'Zend Framework APPLICATION_PATH'
            )
    );
    $opts->parse();

    if (!$opts->getOption('application')) {
        throw new Zend_Console_Getopt_Exception('Parse error', $opts->getUsageMessage());
    }

    define('APPLICATION_PATH', realpath($opts->getOption('application')));

    if (!file_exists(APPLICATION_PATH . '/configs/application.ini')) {
        throw new Exception('Zend Application is not configured');
    }

    if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
        throw new Exception('klear.ini not found, should exist on application (config) dir');
    }

    //Init Db
    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
    $application->bootstrap('db');

    //Get namespace
    $zendConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    $namespace = $zendConfig->appnamespace;
    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }

    // Sistema actual en uso, no sobreescribe ficheros existentes
    $klearDir = APPLICATION_PATH . '/configs/klear';
    $yamlFactory = new Generator_Yaml_Factory($klearDir, $namespace);
    $yamlFactory->createAllFiles();

    // Sistema base en raw, siempre se sobreescribe
    $klearDirRaw = APPLICATION_PATH . '/configs/klearRaw';
    $rawYamlFactory = new Generator_Yaml_Factory($klearDirRaw, $namespace, true);
    $rawYamlFactory->createAllFiles();

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    echo $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
