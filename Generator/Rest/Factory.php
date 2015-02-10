<?php
class Generator_Rest_Factory
{

    protected $_restPath;
    protected $_restNamespace;
    protected $_namespace;

    protected $_override = false;

    protected $_tables = null;

    protected $_enabledLanguages = array();

    public function __construct($basePath, $apiNamespace, $namespace, $override = false)
    {

        $this->_restPath = $basePath;
        $this->_restNamespace = $apiNamespace;

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

        if (!file_exists($this->_restPath)) {
            if (!mkdir($this->_restPath, 0755, true)) {
                throw new Exception(
                    'No se puede crear el directorio rest: ' . $this->_restPath
                );
            };
        }

    }

    public function start()
    {
        echo " * klear-rest Start.\n";
        $this->createFiles();
        $this->createSystemDoc();
        echo " * klear-rest Done.\n";
    }

    public function createFiles()
    {

        $entities = $this->_getEntities();

        foreach ($entities as $table) {

            $controllerFile = $this->_restPath . '/' . $table . 'Controller.php';

            if (!file_exists($controllerFile)) {

                try {

                    $data = array(
                        'tableName' => $table,
                        'apiNamespace' => ucfirst($this->_restNamespace)
                    );

                    $controllerData = $this->getParsedTplContents(
                        'rest.tpl.php', $data
                    );

                    if (!file_put_contents($controllerFile, $controllerData)) {
                        die("could not write controller file $controllerFile\n");
                    } else {
                        echo " * Creado el controller $controllerFile \n";
                    }

                } catch (Exception $e) {

                    echo 'Error: ' . $e->getMessage() . "\n";

                }

            }
        }

        return $this;

    }

    public function createSystemDoc()
    {

        $cliFolder = APPLICATION_PATH . '/../cli';

        if (!file_exists($cliFolder)) {
            if (!mkdir($cliFolder, 0755, true)) {
                throw new Exception(
                    'No se puede crear el directorio cli: ' . $cliFolder
                );
            };
        }

        $cliDocs = $cliFolder . '/cliDocs.php';
        $runCliDocs = $cliFolder . '/runCliDocs.sh';

        if (!file_exists($cliDocs)) {

            try {

                $cliDocsData = $this->getParsedTplContents(
                    'cliDocs.tpl.php', array()
                );

                if (!file_put_contents($cliDocs, $cliDocsData)) {
                    die("could not write file $cliDocs\n");
                } else {
                    echo " * Creado el cliDocs $cliDocs \n";
                }

            } catch (Exception $e) {

                echo 'Error: ' . $e->getMessage() . "\n";

            }
        }

        if (!file_exists($runCliDocs)) {
            try {

                $runCliDocsData = $this->getParsedTplContents(
                    'runCliDocs.tpl.php', array()
                );

                if (!file_put_contents($runCliDocs, $runCliDocsData)) {
                    die("could not write file $runCliDocs\n");
                } else {
                    echo " * Creado el runCliDocs $runCliDocs \n";
                }

            } catch (Exception $e) {

                echo 'Error: ' . $e->getMessage() . "\n";

            }
        }

        $apidocs = APPLICATION_PATH . '/../public/apidocs';

        if (!file_exists($apidocs)) {
            if (!mkdir($apidocs, 0755, true)) {
                throw new Exception(
                    'No se puede crear el directorio apidocs: ' . $apidocs
                );
            } else {
                echo " * Creado el directorio apidocs dentro de public. \n";
            }
        }

        $composerFolder = APPLICATION_PATH . '/../library/Composer';

        if (!file_exists($composerFolder)) {
            if (!mkdir($composerFolder, 0755, true)) {
                throw new Exception(
                    'No se puede crear el directorio Composer: ' . $composerFolder
                );
            };
        }

        $composerJson = $composerFolder . '/composer.json';

        if (!file_exists($composerJson)) {
            try {

                $composerJsonData = $this->getParsedTplContents(
                    'composerJson.tpl.php', array()
                );

                if (!file_put_contents($composerJson, $composerJsonData)) {
                    die("could not write file $composerJson\n");
                } else {
                    echo " * En el directorio " . $composerFolder . " genera un composer install. \n";
                }

            } catch (Exception $e) {

                echo 'Error: ' . $e->getMessage() . "\n";

            }
        }

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

}
