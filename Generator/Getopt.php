<?php
class Generator_Getopt extends Zend_Console_Getopt
{
    public function __construct($rules, $argv = null, $getoptConfig = array())
    {
        if (!is_array($rules)) {
            $rules = array();
        }
        $rules['application|a=s'] = 'Zend Framework APPLICATION_PATH';
        $rules['enviroment|e=s'] = 'Zend Framework APPLICATION_ENV';
        parent::__construct($rules, $argv, $getoptConfig);
    }

    public function checkRequired()
    {
        if (!$this->getOption('application')) {
            throw new Zend_Console_Getopt_Exception('Parse error', $this->getUsageMessage());
        }

        define('APPLICATION_PATH', realpath($this->getOption('application')));

        if (!file_exists(APPLICATION_PATH . '/configs/application.ini')) {
            throw new Exception('application.ini not found');
        }

        if (!file_exists(APPLICATION_PATH . '/configs/klear.ini')) {
            throw new Exception('klear.ini not found, should exist on application (config)  dir');
        }
    }

    public function getEnviroment()
    {

        $argumentEnv = $this->getOption('enviroment');
        $availableEnvs = $this->_getEnviroments();

        if (!in_array($argumentEnv, $availableEnvs)) {
            if (!empty($argumentEnv)) {

                throw new Exception(
                    "Unexpected environment " . $argumentEnv. ". Available environments are: " .
                    var_export($availableEnvs, true)
                );
            }

            // As defined @ bootstrap.php
            return APPLICATION_ENV;
        }

        return $argumentEnv;
    }

    protected function _getEnviroments()
    {
        $enviroments = array();
        $enviromentRawData = array_keys(parse_ini_file(APPLICATION_PATH. '/configs/application.ini', true));

        foreach ($enviromentRawData as $enviromentAndInheritance) {
            $item = explode(":", $enviromentAndInheritance);
            $enviroments[] = trim($item[0]);
        }

        return $enviroments;
    }

}