#!/usr/bin/php
<?php
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

if (!is_file(dirname(__FILE__).'/config/config.php')) {
    die("please copy config/config.php-default to config/config.php and modify.");
}

define('VERSION', '0.6');
define('AUTHOR',  'Kfir Ozer <kfirufk@gmail.com>; Mikel Madariaga <mikel@irontec.com>; Alayn Gortazar <alayn@irontec.com>');


require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();

require_once('class/MakeDbTable.php');
require_once('class/ArgvParser.php');
require_once('config/config.php');

if (!ini_get('short_open_tag')) {
    die("please enable short_open_tag directive in php.ini\n");
}

if (!ini_get('register_argc_argv')) {
    die("please enable register_argc_argv directive in php.ini\n");
}

$db_type = $config['db.type'];
$class = 'Make_' . $db_type;

$dbFilePath = array(
    __DIR__,
    'class',
    'Make.' . $db_type . '.php'
);
require(implode(DIRECTORY_SEPARATOR, $dbFilePath));

if (!class_exists($class)) {
    die ("Database type specified is not supported\n");
}

$parser = new ArgvParser($argv,AUTHOR,VERSION);
$params = $parser->checkParams();

$namespace = $config['namespace.default'];

if (sizeof($params['--application']) == 1) {
    $applicationPath = $params['--application'][0];
    define('APPLICATION_PATH', $applicationPath);

    $application = new Zend_Application(APPLICATION_ENV, $applicationPath . '/configs/application.ini');
    $application->bootstrap('db');
    $dbAdapter = Zend_Db_Table::getDefaultAdapter();
    $dbParams = $dbAdapter->getConfig();

    // Define application environment
    $zendConfig = new Zend_Config_Ini($applicationPath . '/configs/application.ini', APPLICATION_ENV);

    $params['--location'][0] = APPLICATION_PATH . '/../library';
    $params['--database'][0] = $dbParams['dbname'];
    $params['--namespace'][0] = $zendConfig->appnamespace;
}

if (sizeof($params['--namespace']) == 1) {
    $namespace = $params['--namespace'][0];
}

$dbname = $params['--database'][0];
echo $class. "\n";
$modelCreator = new $class($config, $namespace);
$tables = array();
if ($params['--all-tables'] || sizeof($params['--tables-regex'])>0) {
    $tables = $modelCreator->getTablesNamesFromDb();
}

$tables = $parser->compileListOfTables($tables, $params);

if (sizeof($tables) == 0) {
    die("error: please provide at least one table to parse.\n");
}

$path = array();
if (sizeof($params['--location']) == 1) {
    // Check if a relative path
    if (!realpath($params['--location'][0])) {
        $path[] = __DIR__;
        $path[] = $params['--location'][0];
    } else {
        $path[] = $params['--location'][0];
    }
} else {
    $path[] = __DIR__;
    $PATH[] = $params['--database'][0];
}
$path = realpath(implode(DIRECTORY_SEPARATOR, $path));
$modelCreator->setLocation($path);
$path .= DIRECTORY_SEPARATOR;
echo "\n\nEl path:\n\t".$path."\n\n";

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

$modelCreator->setTableList($tables);

$modelCreator->create();

foreach ($tables as $table) {

    $modelCreator->setTableName($table);
    try {
        $modelCreator->parseTable();
    } catch (Exception $e) {
        echo "Warning: Failed to process $table: " . $e->getMessage(). " ... Skipping\n";
    }

}

foreach ($tables as $table) {
    $modelCreator->setTableName($table);
    try {
        $modelCreator->doItAll();
    } catch (Exception $e) {
        echo "Warning: Failed to process $table: " . $e->getMessage(). " ... Skipping\n";
    }

}


echo "done!\n";

