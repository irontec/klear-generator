#!/usr/bin/php
<?php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

define('VERSION', '0.1');
define('AUTHOR',  'Alayn Gortazar <alayn@irontec.com>');

try {
    $opts = new Generator_Getopt(
        array(
            'generate-delta|d=s' => 'Generate Delta instead of modifying database'
        )
    );
    $opts->parse();
    $opts->checkRequired();
    $env = $opts->getEnviroment();
    
    $deltaWriter = null;
    if ($opts->getOption('generate-delta')) {
        $deltaPath = realpath($opts->getOption('generate-delta'));
        $deltaWriter = new Generator_Db_FakeAdapter($deltaPath);
    }

    $klearConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/klear.ini', $env);

    if (isset($klearConfig->klear->languages)) {
        $application = new Zend_Application($env, APPLICATION_PATH . '/configs/application.ini');
        $application->bootstrap('db');

        /** Generate Model Configuration Files **/
        $tables = Zend_Db_Table::getDefaultAdapter()->listTables();
        foreach ($tables as $table) {
            $table = new Generator_Db_Table($table, $klearConfig, $deltaWriter);
            $table->generateAllFields();
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
