#!/usr/bin/php
<?php

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Iron' . DIRECTORY_SEPARATOR . 'Translate' . DIRECTORY_SEPARATOR . 'Adapter' . DIRECTORY_SEPARATOR . 'GettextKlear.php');
include_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'klear' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'YamlStream.php');

define('VERSION', '0.1');
define('AUTHOR',  'Victor Vargas <victor@irontec.com>');

    $opts = new Generator_Getopt(
        array(
            'verbose|v' => 'Verbose Mode On',
            'namespace|n-s' => 'Application namespace if none set the appnamespace is used'
        )
    );

    $opts->parse();
    $opts->checkRequired();
    $env = $opts->getEnviroment();
    $verbose = $opts->getOption('verbose');

    $enviroment = $opts->getOption('enviroment') || APPLICATION_ENV;

    $applicationIni = APPLICATION_PATH . '/configs/application.ini';
    $klearIni = APPLICATION_PATH . '/configs/klear.ini';
    $klearYaml = APPLICATION_PATH. '/configs/klear/klear.yaml';
    
    stream_wrapper_register("klear.yaml", "Klear_Model_YamlStream");
    
    if (file_exists($klearYaml)) {
        Generator_Doc_YamlImporter::index();
        Generator_Doc_YamlImporter::index(true);
    } else {
        throw new Exception("No existe el archivo klear.yaml para generar el documento\n");
    }
    exit;