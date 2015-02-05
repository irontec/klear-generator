<?php
class Generator_Rest_Factory
{

    protected $_restDir;
    protected $_override = false;
    protected $_namespace;

    protected $_tables = null;

    protected $_enabledLanguages = array();

    public function __construct($basePath, $namespace, $override = false)
    {
        $this->_restDir = $basePath;
        $this->_namespace = $namespace;
        $this->_override = (bool)$override;

        $this->_getLanguages();
        $this->_createDirStructure();

    }

    protected function _getLanguages()
    {
        $languages = new Generator_Languages_Config();
        $this->_enabledLanguages = $languages->getEnabledLanguages();
    }

    protected function _createDirStructure()
    {
        /**
         * If override is set, remove all existing config
         */
        if ($this->_override) {
            if (file_exists($this->_restDir)) {
                $this->_rrmdir($this->_restDir);
            }
        }

        if (!file_exists($this->_restDir)) {
            if (!mkdir($this->_restDir)) {
                throw new Exception(
                    'No se puede crear el directorio rest: ' . $dir
                );
            };
        }
    }

    /**
     * recursively remove a directory
     */
    protected function _rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if(is_dir($file))
                $this->_rrmdir($file);
            else
                unlink($file);
        }

        @rmdir($dir);

    }

    public function createFiles()
    {

        $entities = $this->_getEntities();

        foreach ($entities as $table) {
            $controllerFile = $this->_restDir . '/' . $table . 'Controller.php';

            if (!file_exists($controllerFile) || $this->_override) {
                try {

                    $data = array(
                        'tableName' => $table
                    );

                    $controllerData = $this->getParsedTplContents(
                        'rest.tpl.php', $data
                    );

                    if (!file_put_contents($controllerFile, $controllerData)) {
                        die(
                            "could not write controller file $controllerFile\n"
                        );
                    }

                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage() . "\n";
                }

            }
        }

        return $this;

    }

    /**
     *
     * parse a tpl file and return the result
     *
     * @param String $tplFile
     * @return String
     */
    public function getParsedTplContents($tplFile, $vars = array())
    {
        $tplPath = array(
            __DIR__,
            '../..',
            'templates',
            $tplFile
        );
        $tplPath = implode(DIRECTORY_SEPARATOR, $tplPath);

        if (!file_exists($tplPath)) {
            return '';
        }

        ob_start();
        extract($vars);
        require($tplPath);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    protected function _getEntities()
    {
        $entities = array();
        $tables = $this->_getTables();
        foreach ($tables as $table) {
            $tableComment = Generator_Db::tableComment($table);
            if (stristr($tableComment, '[rest]')) {
                $entities[] = $table;
            }
        }

        return $entities;
    }

    protected function _getTables()
    {
        if (!is_null($this->_tables)) {
            return $this->_tables;
        }

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $tables = $dbAdapter->listTables();

        foreach ($tables as $table) {

            $tableComment = Generator_Db::tableComment($table);
            if (!stristr($tableComment, '[ignore]')) {
                $this->_tables[] = $table;
            }
        }

        return $this->_tables;
    }


}
