<?php

class Generator_Rest_Factory
{

    protected $_color;

    protected $_restPath;
    protected $_usersAuthTable;
    protected $_fieldUsername;
    protected $_fieldPassword;
    protected $_namespace;
    protected $_pathLibrary;

    protected $_tables = null;

    protected $_enabledLanguages = array();

    public function __construct($restConfig, $namespace, $pathLibrary)
    {

        require __DIR__ . '/../../class/Colors.php';
        $this->_color = new Colors();

        $this->_checkRestConfig($restConfig);

        $this->_namespace = $namespace;
        $this->_pathLibrary = $pathLibrary . '/';

        $this->_getLanguages();

    }

    /**
     * Comprueba que esten todos los parametros de "restConfig"
     * @param Zend_Config $restConfig
     */
    protected function _checkRestConfig($restConfig)
    {

        $status = true;

        if (is_null($restConfig->path)) {
            $status = false;
            $this->_logFail(
                'Required "restConfig.path"'
            );
            $this->_logSuccess(
                'Example -> restConfig.path = APPLICATION_PATH "/modules/rest/"'
            );
        } else {
            $this->_restPath = $restConfig->path;
        }

        if (is_null($restConfig->usersAuthTable)) {
            $status = false;
            $this->_logFail(
                'Required "restConfig.usersAuthTable"'
            );
            $this->_logSuccess(
                'Example -> restConfig.usersAuthTable = "KlearUsers"'
            );
        } else {
            $this->_usersAuthTable = $restConfig->usersAuthTable;
        }

        if (is_null($restConfig->fieldUsername)) {
            $status = false;
            $this->_logFail(
                'Required "restConfig.fieldUsername"'
            );
            $this->_logSuccess(
                'Example -> restConfig.fieldUsername = "login"'
            );
        } else {
            $this->_fieldUsername = $restConfig->fieldUsername;
        }

        if (is_null($restConfig->fieldPassword)) {
            $status = false;
            $this->_logFail(
                'Required "restConfig.fieldPassword"'
            );
            $this->_logSuccess(
                'Example -> restConfig.fieldPassword = "pass"'
            );
        } else {
            $this->_fieldPassword = $restConfig->fieldPassword;
        }

        if (!$status) {
            $this->_logFail(
                'Declare the parameters before proceeding!!!'
            );
            die();
        }

    }

    /**
     * Idiomas usados en el Proyecto
     */
    protected function _getLanguages()
    {
        $languages = new Generator_Languages_Config();
        $this->_enabledLanguages = $languages->getEnabledLanguages();
    }

    public function start()
    {

        $this->createDepsAuth();
        $this->createController();
        $this->createSystemDoc();

    }

    public function createDepsAuth()
    {

        $config = APPLICATION_PATH . '/configs/';
        $plugin = '/Controller/Plugin/';
        $pathPlugin = $this->_pathLibrary . $this->_namespace . $plugin;

        $this->_createFolders($this->_restPath . '/controllers');
        $this->_createFolders($this->_restPath . '/controllersRaw');

        $this->_createFolders($pathPlugin);

        /**
         * Plugin de Auth
         */
        $statusAuth = $this->_createFiles(
            $pathPlugin . 'Auth.php',
            'Auth.tpl.php',
            array()
        );

        if ($statusAuth) {
            $pluginAuth = $this->_namespace . "_Controller_Plugin_Auth";
            $this->_logSuccess('Pendiente inicializar el plugin ' . $pluginAuth);
            $this->_logSuccess('resources.frontController.plugins.authRest = "'.$pluginAuth.'"');
        }

        /**
         * Config Auth
         */
        $this->_createFiles(
            $config . 'restApi.ini',
            'restApi.tpl.ini',
            array()
        );

    }

    /**
     *
     * @return Generator_Rest_Factory
     */
    public function createController()
    {

        $entities = $this->_getEntities();

        if (empty($entities)) {
            return;
        }

        $controllers = $this->_restPath . '/controllers';

        foreach ($entities as $table) {

            $controllerFile = $controllers . '/' . $table . 'Controller.php';
            $controllerRawFile = $controllers . 'Raw/' . $table . 'Controller.php';

            $data = array(
                'tableName' => $table,
            );

            $this->_createFiles(
                $controllerFile,
                'rest.tpl.php',
                $data
            );

            $this->_createFiles(
                $controllerRawFile,
                'rest.tpl.php',
                $data,
                true
            );

        }

        return $this;

    }

    public function createSystemDoc()
    {

        $cliFolder = APPLICATION_PATH . '/../cli';

        $this->_createFolders($cliFolder);

        $cliDocs = $cliFolder . '/cliDocs.php';
        $runCliDocs = $cliFolder . '/runCliDocs.sh';

        $this->_createFiles($cliDocs, 'cliDocs.tpl.php');
        $this->_createFiles($runCliDocs, 'runCliDocs.tpl.php');

        $apidocs = APPLICATION_PATH . '/../public/apidocs';
        $composerFolder = APPLICATION_PATH . '/../library/Composer';
        $this->_createFolders($apidocs);
        $this->_createFolders($composerFolder);

        $composerJson = $composerFolder . '/composer.json';
        $apidocController = APPLICATION_PATH . '/controllers/ApidocController.php';
        $this->_createFiles($composerJson, 'composerJson.tpl.php');
        $this->_createFiles($apidocController, 'ApidocController.tpl.php');

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

    /**
     * @param String $folderPath
     * @throws Exception
     */
    protected function _createFolders($folderPath)
    {

        if (file_exists($folderPath)) {
            return;
        }

        if (!mkdir($folderPath, 0755, true)) {
            throw new Exception(
                'No se puede crear el directorio: ' . $folderPath
            );
        } else {
            $this->_logNew($folderPath);
        }

    }

    /**
     * @param String $filePath
     * @param String $tpl
     * @param Array $data
     * @throws Exception
     * @return boolean
     */
    protected function _createFiles($filePath, $tpl, $data = array(), $rewrite = false)
    {

        if (!$rewrite) {
            if (file_exists($filePath)) {
                return false;
            }
        }

        try {

            $restApiData = $this->getParsedTplContents(
                $tpl,
                $data
            );

            if (!file_put_contents($filePath, $restApiData)) {
                throw new Exception(
                    'No se puede crear: ' . $filePath
                );
            } else {
                if (!$rewrite) {
                    $this->_logNew($filePath);
                }
            }

        } catch (Exception $e) {
            throw new Exception(
                $e->getMessage()
            );
        }

        return true;

    }


    protected function _logNew($msg)
    {

        echo $this->_color->getColoredString(
            ' [new] ' . $msg,
            'light_cyan',
            null
        ) . "\n";

    }

    protected function _logSuccess($msg)
    {

        echo $this->_color->getColoredString(
            ' >> ' . $msg,
            'light_green',
            null
        ) . "\n";

    }

    protected function _logFail($msg)
    {

        echo $this->_color->getColoredString(
            ' [error] ' . $msg,
            'red',
            null
        ) . "\n";

    }

}