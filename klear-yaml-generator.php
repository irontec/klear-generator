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

    if (!$opts->getOptions('application')) {
        throw new Zend_Console_Getopt_Exception('Parse error', $opts->getUsageMessage());
    }

    define('APPLICATION_PATH', realpath($opts->getOption('application')));

    if (!file_exists(APPLICATION_PATH . '/configs/application.ini')) {
        throw new Exception('Zend Application is not configured');
    }

    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
    $application->bootstrap('db');
    $zendConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    $namespace = $zendConfig->appnamespace;
    if (substr($namespace, -1) == '_') {
        $namespace = substr($namespace, 0, -1);
    }

    $klearDir = APPLICATION_PATH . '/configs/klear';
    $klearDirs = array(
        'root' => $klearDir,
        'model' => $klearDir . '/model',
        'conf.d' => $klearDir . '/conf.d'
    );
    $klearConfigFile = $klearDir . '/klear.yaml';

    if (file_exists($klearConfigFile)) {
        throw new Exception('Klear is allready configured in: ' . $klearConfigFile);
    }

    foreach ($klearDirs as $dir) {
        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                throw new Exception('Klear configuration dir could not be created in: ' . $dir);
            };
        }
    }

    /** Generate Main Config File **/
    $mainConfig = new Generator_Yaml_MainConfig();
    $configWriter = new Zend_Config_Writer_Yaml();
    $configWriter->write($klearConfigFile, $mainConfig->getConfig());

    /** Generate Errors File */
    $errorsConfig = new Generator_Yaml_ErrorsConfig();
    $configWriter = new Zend_Config_Writer_Yaml();
    $configWriter->write($klearDirs['root'] . '/errors.yaml', $errorsConfig->getConfig());

    /** Generate Model Configuration Files **/
    $dbAdapter = Zend_Db_Table::getDefaultAdapter();
    $tables = $dbAdapter->listTables();

    foreach ($tables as $table) {
        $modelConfig = new Generator_Yaml_ModelConfig($table, $namespace);
        $configWriter->write($klearDirs['model'] . '/' . ucfirst(Generator_Yaml_StringUtils::toCamelCase($table)) . '.yaml', $modelConfig->getConfig());
    }

    /** Generate mapper list file **/
    $mappersFile = new Generator_Yaml_MappersFile($tables, $namespace);
    $configWriter->write($klearDirs['conf.d'] . '/mapperList.yaml', $mappersFile->getConfig());

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
