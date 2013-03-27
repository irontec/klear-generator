<?php
class Generator_Getopt extends Zend_Console_Getopt
{
    public function __construct($rules, $argv = null, $getoptConfig = array())
    {
        $rules['application|a=s'] = 'Zend Framework APPLICATION_PATH';
        parent::__construct($rules, $argv, $getoptConfig);
    }

    public function checkRequired()
    {
        if (!$this->getOption('application')) {
            throw new Zend_Console_Getopt_Exception('Parse error', $opts->getUsageMessage());
        }

        define('APPLICATION_PATH', realpath($this->getOption('application')));

        if (!file_exists(APPLICATION_PATH . '/configs/application.ini')) {
            throw new Exception('application.ini not found');
        }

        if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
            throw new Exception('klear.ini not found, should exist on application (config)  dir');
        }
    }
}