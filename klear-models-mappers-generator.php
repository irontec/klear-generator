#!/usr/bin/php
<?php

/**
 * TODO: select tables to (re)generate (now all tables are generated)
 */

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
            'application|a=s' => 'Zend Framework APPLICATION_PATH',
            'namespace|n-s' => 'Application namespace if none set the appnamespace is used'
        )
    );
    $opts->parse();

    if (!$opts->getOption('application')) {
        throw new Zend_Console_Getopt_Exception('Parse error', $opts->getUsageMessage());
    }

    define('APPLICATION_PATH', realpath($opts->getOption('application')));

    if (!file_exists(APPLICATION_PATH . '/configs/application.ini')) {
        throw new Exception('application.ini not found');
    }

    if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
        throw new Exception('klear.ini not found, should exist on application (config) dir');
    }

    $defaultValues = array(
        'dbtype' => 'mysql',
        'docs' => array(
            'author' => 'Anonymous',
            'license' => 'http://framework.zend.com/license/new-bsd     New BSD License',
            'copyright' => 'ZF model generator'
        ),
        'include' => array(
            'addrequire' => false,
            'path' => 'includes'
        ),
        'cache' => array(
            'manager_name' => '',
            'name' => 'model'
        ),
        'log' => array(
            'logger_name' => ''
        )
    );

    $applicationIni = APPLICATION_PATH . '/configs/application.ini';
    $klearIni = APPLICATION_PATH . '/configs/klear.ini';

    $klearConfig = new Zend_Config($defaultValues, true);
    $klearConfig->merge(new Zend_Config_Ini($klearIni, APPLICATION_ENV));

    $namespace = $opts->getOption('namespace');
    if (!$namespace) {
        $zendConfig = new Zend_Config_Ini($applicationIni, APPLICATION_ENV);
        $namespace = $zendConfig->appnamespace;
    }
    $klearConfig->namespace = $namespace;

    $application = new Zend_Application(APPLICATION_ENV, $applicationIni);
    $application->bootstrap('db');

    if (isset($klearConfig->klear->languages)) {

        /** Generate Model Configuration Files **/
        //         $tables = Zend_Db_Table::getDefaultAdapter()->listTables();


        //         foreach ($tables as $table) {
        //             $table = new Generator_Db_Table($table, $klearConfig);
        //             $table->generateMultilangFields();
        //             $table->generateFileFields();
        //         }

    }

    // Get generator
    $dbFilePath = implode(DIRECTORY_SEPARATOR, array(
            __DIR__,
            'class',
            'Make.' . $klearConfig->dbtype . '.php'
    ));

    $class = 'Make_' . $klearConfig->dbtype;
    if (!file_exists($dbFilePath)) {
        throw new Exception("Specified Database type is not supported\n");
    }
    require($dbFilePath);
    if (!class_exists($class)) {
        throw new Exception("Specified Database type is not supported\n");
    }

    $path = realpath(APPLICATION_PATH . '/../library') . DIRECTORY_SEPARATOR;

    $folderList = array(
            'Mapper',
            'Mapper/Sql',
            'Mapper/Sql/DbTable',
            'Mapper/Sql/Raw',
            'Model',
            'Model/Raw'
    );

    foreach ($folderList as $name) {
        $dir = $path . $namespace . '/' . $name;
        if (!is_dir($dir)) {
            if (!@mkdir($dir,0755,true)) {
                die("error: could not create directory $dir\n");
            }
        }
    }

    // Instanciamos el generador y creamos los modelos y mappers de las tablas
    echo "Generando modelos y mappers...\n";
    $modelCreator = new $class($klearConfig);

    $modelCreator->setLocation($path);

    $dbAdapter = Zend_Db_Table::getDefaultAdapter();
    $tables = $dbAdapter->listTables();

    $modelCreator->setTableList($tables);

    $modelCreator->doItAll();

    echo "Done!\n";
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    echo $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
