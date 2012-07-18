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

    if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
        throw new Exception('klear.ini not found, should exist on application dir');
    }

    $klearConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/klear.ini', APPLICATION_ENV);
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

    foreach ($klearDirs as $dir) {
        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                throw new Exception('Klear configuration dir could not be created in: ' . $dir);
            };
        }
    }

    $configWriter = new Zend_Config_Writer_Yaml();

    /** Generate Errors File */
    $errorsFile = $klearDirs['root'] . '/errors.yaml';
    if (file_exists($errorsFile)) {
        echo "Errors file allready exists in: " . $errorsFile . "\n";
    } else {
        $errorsConfig = new Generator_Yaml_ErrorsConfig();
        $configWriter->write($errorsFile, $errorsConfig->getConfig());
    }

    /** Copy Actions File*/
    $actionsFile = $klearDirs['conf.d'] . '/actions.yaml';
    if (file_exists($actionsFile)) {
        echo "Actions file allready exists in: " . $actionsFile . "\n";
    } else {
        copy(__DIR__ . "/Generator/Yaml/klear/conf.d/actions.yaml", $actionsFile);
    }

    /** Generate Model Configuration Files **/
    $dbAdapter = Zend_Db_Table::getDefaultAdapter();
    $tables = $dbAdapter->listTables();
    $entities = array();

    foreach ($tables as $table) {
        $modelFile = $klearDirs['model'] . '/' . ucfirst(Generator_Yaml_StringUtils::toCamelCase($table)) . '.yaml';
        if (file_exists($modelFile)) {
            echo "Model file allready exists in: " . $modelFile . "\n";
        } else {
            $modelConfig = new Generator_Yaml_ModelConfig($table, $namespace, $klearConfig);
            $configWriter->write($modelFile, $modelConfig->getConfig());
        }

        /** Generate ModelList files **/
        $tableComment = Generator_Db::tableComment($table);
        if (stristr($tableComment, '[entity]')) {
            $entities[] = $table;
            $listFile = $klearDirs['root'] . '/' . ucfirst(Generator_Yaml_StringUtils::toCamelCase($table)) . 'List.yaml';
            if (file_exists($listFile)) {
                echo "ModelList file allready exists in: " . $modelFile . "\n";
            } else {
                $listConfig = new Generator_Yaml_ListConfig($table);
                $configWriter->write($listFile, $listConfig->getConfig());
                $contents = "#include conf.d/mapperList.yaml\n";
                $contents .= "#include conf.d/actions.yaml\n\n";
                $contents .= file_get_contents($listFile);
                file_put_contents($listFile, $contents);
            }
        }
    }

    /** Generate Main Config File **/
    if (file_exists($klearConfigFile)) {
        echo "klear.ini file allready exists\n";
    } else {
        $mainConfig = new Generator_Yaml_MainConfig($entities);
        $configWriter->write($klearConfigFile, $mainConfig->getConfig());
    }


    /** Generate mapper list file **/
    $mappersFile = $klearDirs['conf.d'] . '/mapperList.yaml';
    if (file_exists($mappersFile)) {
        echo "Mappers file allready exists in: " . $mappersFile . "\n";
    } else {
        $mappersConfig = new Generator_Yaml_MappersFile($tables, $namespace);
        $configWriter->write($mappersFile, $mappersConfig->getConfig());
    }

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
