#!/usr/bin/php
<?php

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

define('VERSION', '0.1');
define('AUTHOR',  'Javier Infante <jabi@irontec.com>');

try {
    $opts = new Generator_Getopt(
        array(
            'poblate-countries|c' => 'Poblate Country Tables',
            'poblate-timezones|t' => 'Poblate Timezones',
            'verbose|v' => 'Verbose Mode On',
            'namespace|n-s' => 'Application namespace if none set the appnamespace is used'
        )
    );
    
    $opts->parse();
    $opts->checkRequired();
    $env = $opts->getEnviroment();
    $verbose = $opts->getOption('verbose');

    $enviroment = $opts->getOption('enviroment') || APPLICATION_ENV; 
    
    $klearConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/klear.ini', $env);

    if (!isset($klearConfig->klear->languages)) {
        throw new Exception('No languages found in klear.ini');
    }

    //Init Application && Db Connection
    $application = new Zend_Application($env, APPLICATION_PATH . '/configs/application.ini');
    $application->bootstrap('db');

    // Get Application Namespace
    $namespace = $opts->getOption('namespace');
    if (!$namespace) {
        $zendConfig = new Zend_Config_Ini($applicationIni, $env);
        $namespace = $zendConfig->appnamespace;
    }
    
    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }



    if ($opts->getOption('poblate-countries')) {
        if (!$klearConfig->klear->countries) {
            throw new Exception("No configuration found for countries in klear.ini\n\tklear.countries.table\n\tklear.countries.name\n\tklear.countries.code\n");
        }

        $countryParser = new Generator_Assets_CountryImporter();
        $countryParser->setNamespace($namespace);
        if ($verbose) {
            $countryParser->setVerbosed();
        }
        $countryParser->setConfig($klearConfig->klear->countries);
        $countryParser->setLanguages($klearConfig->klear->languages);

        $totalProcessed = $countryParser->parseAll();

        if ($verbose) {
            echo $totalProcessed . " new countries added/updated.\n";
        }
    }

    
    if ($opts->getOption('poblate-timezones')) {
        if (!$klearConfig->klear->timezones) {
            throw new Exception("No configuration found for timezones in klear.ini\n\tklear.timezones.table\n\tklear.timezones.tz\n\tklear.timezones.comment\n");
        }
        
    
        $tzParser = new Generator_Assets_TimezoneImporter();
        $tzParser->setNamespace($namespace);
        if ($verbose) {
            $tzParser->setVerbosed();
        }
        $tzParser->setConfig($klearConfig->klear);
        
        $totalProcessed = $tzParser->parseAll();
    
        if ($verbose) {
            echo $totalProcessed . " timezones added/updated.\n";
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
