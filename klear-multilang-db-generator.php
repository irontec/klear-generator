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
        throw new Exception('application.ini not found');
    }

    if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
        throw new Exception('klear.ini not found, should exist on application dir');
    }

    $klearConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/klear.ini', APPLICATION_ENV);

    if (isset($klearConfig->klear->languages)) {
        $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $application->bootstrap('db');

        /** Generate Model Configuration Files **/
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $tables = $dbAdapter->listTables();

        foreach ($tables as $table) {
            $fieldsDescription = Generator_Db::describeTable($table);
            foreach ($fieldsDescription as $field) {
                if (isset($field['COMMENT']) && strstr($field['COMMENT'], '[ML]')) {
                    foreach ($klearConfig->klear->languages as $language) {
                        $newFieldName = $field['COLUMN_NAME'] . '_' . $language;

                        if (!isset($fieldsDescription[$newFieldName])) {
                            $query = 'ALTER TABLE ' . $dbAdapter->quoteIdentifier($field['TABLE_NAME'])
                                   . ' ADD ' . $dbAdapter->quoteIdentifier($newFieldName)
                                   . ' ' . $field['DATA_TYPE'] . '(' . $field['LENGTH'] . ')';

                            if (!$field['NULLABLE']) {
                                $query .= ' NOT NULL ';
                            }

                            if ($field['DEFAULT']) {
                                $query .= ' DEFAULT ' . $dbAdapter->quote($field['DEFAULT']);
                            }

                            $query .= ' AFTER ' . $dbAdapter->quoteIdentifier($newFieldName);

                            $dbAdapter->query($query);
                            echo  "$newFieldName added to $table \n";
                        }
                    }
                }
            }
        }
    }

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() .  "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: ";
    echo $e->getMessage() . "\n";
    exit(1);
}
