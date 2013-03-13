#!/usr/bin/php
<?php
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define('VERSION', '0.1');
define('AUTHOR',  'Javier Infante <jabi@irontec.com>');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Generator');

try {
    $opts = new Zend_Console_Getopt(
        array(
            'application|a=s' => 'Zend Framework APPLICATION_PATH',
            'poblate-countries|c' => 'Poblate Country Tables',
            'verbose|v' => 'Verbose Mode On'
        )
    );
    $opts->parse();

    if (!$opts->getOption('application')) {
        throw new Zend_Console_Getopt_Exception('Parse error', $opts->getUsageMessage());
    }

    $verbose = $opts->getOption('verbose');

    
    define('APPLICATION_PATH', realpath($opts->getOption('application')));

    if (!file_exists(APPLICATION_PATH . '/configs/application.ini')) {
        throw new Exception('application.ini not found');
    }

    if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
        throw new Exception('klear.ini not found, should exist on application (config)  dir');
    }

    $klearConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/klear.ini', APPLICATION_ENV);

    if (!isset($klearConfig->klear->languages)) {
        throw new Exception('No languages found in klear.ini');
    }
    
    //Init Application && Db Connection 
    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
    $application->bootstrap('db');
    
    
    // Get Application Namespace
    $zendConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    $namespace = $zendConfig->appnamespace;
    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }
    
    
    
    if ($opts->getOption('poblate-countries')) {
        if (!$klearConfig->klear->countries) {
            throw new Exception("No configuration found for countries in klear.ini\n\tklear.countries.table\n\tklear.countries.name\n\tklear.countries.code\n");
        }
        
        $countryParser = new Generator_Country_Parser();
        $countryParser->setNamespace($namespace);
        if ($verbose) {
            $countryParser->setVerbosed();
        }
        $countryParser->setConfig($klearConfig->klear->countries);
        $countryParser->setLanguages($klearConfig->klear->languages);
        
        $totalProcessed = $countryParser->parseAll();
        
        if ($verbose) {
            echo $totalProcessed . " new countries added.\n";
        }
    
    }

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    echo $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
