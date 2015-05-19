<?php
class Generator_Yaml_Factory
{
    protected $_klearDirs;
    protected $_override = false;
    protected $_namespace;

    protected $_klearConfig;
    protected $_configWriter;

    protected $_tables = null;
    protected $_dependantTables = null;

    protected $_enabledLanguages = array();

    public function __construct($basePath, $namespace, $override = false)
    {
        $this->_klearDirs = array(
                'root' => $basePath,
                'model' => $basePath . '/model',
                'conf.d' => $basePath . '/conf.d'
        );
        $this->_namespace = $namespace;
        $this->_override = (bool)$override;


        $this->_klearConfig = new Zend_Config_Ini(APPLICATION_PATH. '/configs/klear.ini', APPLICATION_ENV);

        $this->_getLanguages();

        $this->_configWriter = new Zend_Config_Writer_Yaml();

        $this->_createDirStructure();
    }

    protected function _getLanguages()
    {
        $languages = new Generator_Languages_Config();
        $this->_enabledLanguages = $languages->getEnabledLanguages();
    }

    protected function _createDirStructure()
    {
        //If override is set, remove all existing config
        if ($this->_override) {
            if (file_exists($this->_klearDirs['root'])) {
                $this->_rrmdir($this->_klearDirs['root']);
            }
        }

        foreach ($this->_klearDirs as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir)) {
                    throw new Exception('Klear configuration dir could not be created in: ' . $dir);
                };
            }
        }
    }

    # recursively remove a directory
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

    public function createErrorsFile()
    {
        $errorsFile = $this->_klearDirs['root'] . '/errors.yaml';
        if (!file_exists($errorsFile) || $this->_override) {
            $errorsConfig = new Generator_Yaml_ErrorsConfig();
            $this->_configWriter->write($errorsFile, $errorsConfig->getConfig());
        }
        return $this;
    }

    public function createActionsFile()
    {
        $actionsFile = $this->_klearDirs['conf.d'] . '/actions.yaml';
        if (!file_exists($actionsFile) || $this->_override) {
            copy(__DIR__ . "/klear/conf.d/actions.yaml", $actionsFile);
        }
        return $this;
    }

    public function createModelFiles($raw = false)
    {
        $entities = $this->_getEntities();
        foreach ($entities as $table) {
            $modelFile = $this->_klearDirs['model'] . '/' . ucfirst(Generator_StringUtils::toCamelCase($table)) . '.yaml';
            if (!file_exists($modelFile) || $this->_override) {
                try {
                    if (!$raw) {
                        echo "> Nuevo modelo: " . ucfirst(Generator_StringUtils::toCamelCase($table)) . ".yaml \n";
                    }
                    $modelConfig = new Generator_Yaml_ModelConfig($table, $this->_namespace, $this->_klearConfig, $this->_enabledLanguages);
                    $this->_configWriter->write($modelFile, $modelConfig->getConfig());
                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage() . "\n";
                }
            }
        }
        return $this;
    }

    public function createModelListFiles($generateLinks = false, $raw = false)
    {
        $entities = $this->_getEntities();
        foreach ($entities as $table) {
            $listFile = $this->_klearDirs['root'] . '/' . ucfirst(Generator_StringUtils::toCamelCase($table)) . 'List.yaml';
            if (!file_exists($listFile) || $this->_override) {

                $dependantTables = $this->_getDependantTables($table);
                
                $listConfig = new Generator_Yaml_ListConfig($table, $dependantTables, $this->_klearConfig, $this->_enabledLanguages);

                if (!$raw) {
                    echo "> Nuevo List: " . ucfirst(Generator_StringUtils::toCamelCase($table)) . "List.yaml \n";
                }

                $this->_configWriter->write($listFile, $listConfig->getConfig());
                $contents = "#include conf.d/mapperList.yaml\n";
                $contents .= "#include conf.d/actions.yaml\n";

                foreach ($dependantTables as $tableName => $relFieldName) {
                    $tableName = ucfirst(Generator_StringUtils::toCamelCase($tableName));
                    $contents .= "#include ". $tableName . "List.yaml\n";    
                }

                $contents .= "\n\n";
                $contents .= file_get_contents($listFile);

                if ($generateLinks) {
                    $contents = $this->_insertLinks($contents);
                }

                file_put_contents($listFile, $contents);
            }
        }
        return $this;
    }

    protected function _insertLinks($contents)
    {
        return preg_replace('/\n\s{1,}&:\s([^\n]+)/', '&$1 ', $contents);
    }

    public function createMainConfigFile()
    {
        $mainConfigFile = $this->_klearDirs['root'] . '/klear.yaml';
        if (!file_exists($mainConfigFile) || $this->_override) {
            $mainConfig = new Generator_Yaml_MainConfig($this->_getEntities(), $this->_enabledLanguages);
            $this->_configWriter->write($mainConfigFile, $mainConfig->getConfig());
        }
    }

    protected function _getEntities()
    {
        $entities = array();
        $tables = $this->_getTables();
        foreach ($tables as $table)
        {
            $tableComment = Generator_Db::tableComment($table);
            if (stristr($tableComment, '[entity]')) {
                $entities[] = $table;
            }
        }

        return $entities;
    }

    public function createMappersListFile()
    {

        /** Generate mapper list file **/
        $mappersFile = $this->_klearDirs['conf.d'] . '/mapperList.yaml';

        $mappersConfig = new Generator_Yaml_MappersFile($this->_getTables(), $this->_namespace);
        $this->_configWriter->write($mappersFile, $mappersConfig->getConfig());

        return $this;

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
    
    /**
     * @return array of tableNames
     */
    protected function _getDependantTables($parentTableName)
    {
        if (is_null($this->_dependantTables)) {
            $this->_loadDependantTables();
        }

        if (!isset($this->_dependantTables[$parentTableName])) {
            return array();
        }

        return $this->_dependantTables[$parentTableName];
    }
    

    protected function _loadDependantTables()
    {
        if (!is_null($this->_tables)) {
            $this->_getTables();
        }
        $this->_dependantTables = array();       

        $entities = $this->_getEntities();
        foreach ($entities as $table) {

            if (! array_key_exists($table, $this->_dependantTables)) {
                $this->_dependantTables[$table] = array();
            }

            $dependantTables = Generator_Db::getDependantTables($this->_namespace, $table, $entities);

            foreach ($dependantTables as $dependantTable) {

                if (isset($dependantTable['foreign_tbl_name'])) {

                    $dependantTableName = ucfirst(Generator_StringUtils::toCamelCase($dependantTable['foreign_tbl_name']));
                    $relationColumnName = $dependantTable['column_name'];

                    if ($table == $dependantTableName) {
                        continue;
                    }

                    $this->_dependantTables[$table][$dependantTableName] = $relationColumnName;
                }
            }
        }
    }

    public function createAllFiles($generateLinks = false, $raw = false)
    {
        $this->createErrorsFile();
        $this->createMappersListFile();
        $this->createActionsFile();
        $this->createModelFiles($raw);
        $this->createModelListFiles($generateLinks, $raw);
        $this->createMainConfigFile();
    }
}
