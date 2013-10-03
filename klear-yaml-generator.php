#!/usr/bin/php
<?php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

$currentPath = __DIR__;
$svnRevision = `svnversion $currentPath`;

define('VERSION', '0.1');
define('AUTHOR',  'Alayn Gortazar <alayn@irontec.com>');

try {

    $opts = new Generator_Getopt(
        array(
            'do-not-generate-links|L' => 'Generate links for each screen/dialog',
            'namespace|n-s' => 'Application namespace if none set the appnamespace is used'
        )
    );
    $opts->parse();
    $opts->checkRequired();
    $env = $opts->getEnviroment();

    $generateLinks = true;
    if ($opts->getOption('do-not-generate-links')) {
        $generateLinks = false;
    }

    $applicationIni = APPLICATION_PATH . '/configs/application.ini';

    //Init Db
    $application = new Zend_Application($env, $applicationIni);
    $application->bootstrap('db');

    //Get namespace
    $namespace = $opts->getOption('namespace');
    if (!$namespace) {
        $zendConfig = new Zend_Config_Ini($applicationIni, $env);
        $namespace = $zendConfig->appnamespace;
    }

    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }

    // Sistema actual en uso, no sobreescribe ficheros existentes
    $klearDir = APPLICATION_PATH . '/configs/klear';
    $yamlFactory = new Generator_Yaml_Factory($klearDir, $namespace);
    $yamlFactory->createAllFiles($generateLinks);

    // Sistema base en raw, siempre se sobreescribe
    $klearDirRaw = APPLICATION_PATH . '/configs/klearRaw';
    $rawYamlFactory = new Generator_Yaml_Factory($klearDirRaw, $namespace, true);
    $rawYamlFactory->createAllFiles($generateLinks);

    // genera y copia ficheros base de idiomas.
    $langs = new Generator_Languages_Config();
    $langs->createAllFiles();



    //Guardamos la revisión del svn actual

    $svnData = '[' . date('r') . ']' . ' revision: ' . $svnRevision;
    file_put_contents($klearDir . '/generator.log', $svnData, FILE_APPEND);
    file_put_contents($klearDirRaw . '/generator.log', $svnData, FILE_APPEND);
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    echo $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
