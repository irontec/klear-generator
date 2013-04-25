#!/usr/bin/php
<?php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

define('VERSION', '0.1');
define('AUTHOR',  'Alayn Gortazar <alayn@irontec.com>');

try {

    $opts = new Generator_Getopt(
        array(
            'generate-links|l' => 'Generate links for each screen/dialog'
        )
    );
    $opts->parse();
    $opts->checkRequired();
    $env = $opts->getEnviroment();
    
    $generateLinks = false;
    if ($opts->getOption('generate-links')) {
        $generateLinks = true;
    }

    //Init Db
    $application = new Zend_Application($env, APPLICATION_PATH . '/configs/application.ini');
    $application->bootstrap('db');

    //Get namespace
    $zendConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $env);
    $namespace = $zendConfig->appnamespace;
    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }

    // Sistema actual en uso, no sobreescribe ficheros existentes
    $klearDir = APPLICATION_PATH . '/configs/klear';
    $yamlFactory = new Generator_Yaml_Factory($klearDir, $namespace);
    $yamlFactory->createAllFiles();

    //Guardamos la revisión del svn actual
    $output = array();
    exec('svn info', $output);
    $revision = 'undefined';
    foreach ($output as $outLine) {
        $lineData = explode(':', $outLine, 2);
        if (isset($lineData[1]) && stristr($lineData[0], 'rev')) {
            $revision = trim($lineData[1]);
            break;
        }
    }
    $svnData = '[' . date('r') . ']' . ' revision: ' . $revision . "\n";
    file_put_contents($klearDir . '/generator.log', $svnData, FILE_APPEND);


    // Sistema base en raw, siempre se sobreescribe
    $klearDirRaw = APPLICATION_PATH . '/configs/klearRaw';
    $rawYamlFactory = new Generator_Yaml_Factory($klearDirRaw, $namespace, true);
    $rawYamlFactory->createAllFiles($generateLinks);

    file_put_contents($klearDirRaw . '/generator.log', $svnData, FILE_APPEND);


    $dirFiles=array(
    'languages/',
    'languages/common-strings.php',
    'languages/en_US',
    'languages/en_US/en_US.mo',
    'languages/en_US/en_US.po',
    'languages/es_ES',
    'languages/es_ES/es_ES.mo',
    'languages/es_ES/es_ES.po',
    'languages/eu_ES',
    'languages/eu_ES/eu_ES.mo',
    'languages/eu_ES/eu_ES.po'
            );

    foreach ($dirFiles as $file) {

        $orig = __DIR__ . '/' . $file;

        $dest = APPLICATION_PATH . '/' . $file;

        if (!file_exists($dest)) {

            if (is_dir($orig)) {
                mkdir($dest);
            } else {
                copy($orig , $dest);
            }

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
