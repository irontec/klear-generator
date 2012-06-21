<?php

/**
 * main class for files creation
 */
abstract class MakeDbTable {

    protected $_existingAttributes = array();
    

	/**
	 *  @var String $_tbname;
	 */
	protected $_tbname;

	/**
	 *
	 *  @var String $_dbname;
	 */
	protected $_dbname;

	/**
	 *  @var PDO $_pdo;
	 */
	protected $_pdo;


	/**
	 *   @var Array $_columns;
	 */
	protected $_columns = array();


	/**
	 * @var String $_className;
	 */
	protected $_className;

	/**
	 * @var Array $_classDesc;
	 */
	protected $_classDesc = array();

	/**
	 *   @var Array $_primaryKey;
	 */
	protected $_primaryKey = array();

	/**
	 *   @var String $_namespace;
	 */
	protected $_namespace;

    /**
     *   @var String $_namespace;
     */
    protected $_tplPrefix;

	/**
	 *  @var Array $_config;
	 */
	protected $_config;

	/**
	 *   @var Boolean $_addRequire;
	 */
	protected $_addRequire;

	/**
	 *   @var String $_author;
	 */
	protected $_author;

	/**
	 *   @var String $_license;
	 */
	protected $_license;

	/**
	 *   @var String $_copyright;
	 */
	protected $_copyright;

	/**
	 *   @var String $_includePath;
	 */
	protected $_includePath;


	/**
	 *   @var String $_includeModel;
	 */
	protected $_includeModel;

	/**
	 *   @var String $_includeTable
	 */
	protected $_includeTable;

	/**
	 *   @var String $_includeMapper
	 */
	protected $_includeMapper;

	/**
	 *
	 * @var String $_location;
	 */
	protected $_location;

	/**
	 * @var array $_tableList
	 */
	protected $_tableList;

	/**
	 *
	 * @var Array $_foreignKeysInfo
	 */
	protected $_foreignKeysInfo;

	/**
	 *
	 * @var Array $_dependentTables
	 */
	protected $_dependentTables;

	/**
	 * List of table name prefixes to automatically remove
	 * @var array
	 */
	protected $_tablePrefixes = array('tbl_', 'tbl', 't_', 'table');

	/**
	 * List of column name suffixes to automatically remove
	 * @var array
	 */
	protected $_columnSuffixes = array('_id', 'id', '_ident', 'ident', '_col', 'col');

	/**
	 * List of column names that indiciate the column is to be used as a soft-delete
	 * @var array
	 */
	protected $_softDeleteColumnNames = array('deleted', 'is_deleted');

	/**
	 * Name of the column to be used for soft-delete purposes
	 * @var string
	 */
	protected $_softDeleteColumn = null;

	/**
	 * Name of the Cache Manager to use. Left blank if the feature is to be disabled
	 * @var string
	 */
	protected $_cacheManagerName = '';

	/**
	 * Name of the cache to use
	 * @var string
	 */
	protected $_cacheName = 'model';

	/**
	 * Name of the Zend Log to use. Left blank if the feature is to be disabled
	 * @var string
	 */
	protected $_loggerName = '';

	/**
	 *
	 * @param array $info
	 */
	public function setForeignKeysInfo($info) {

		if (empty($this->_foreignKeysInfo)) {

			$this->_foreignKeysInfo = array();
		}

		$this->_foreignKeysInfo[$this->getTableName()] = $info;
	}

	/**
	 *
	 * @return array
	 */
	public function getForeignKeysInfo() {
		return $this->_foreignKeysInfo[$this->getTableName()];
	}

	/**
	 *
	 * @param string $location
	 */
	public function setLocation($location) {
		$this->_location=$location;
	}

	/**
	 *
	 * @return string
	 */
	public function getLocation() {
		return $this->_location . DIRECTORY_SEPARATOR . $this->_namespace;
	}

	/**
	 *
	 * @param string $table
	 */
	public function setTableName($table) {
		$this->_tbname=$table;
		$this->_className=$this->_getClassName($table);
	}

	/**
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->_tbname;
	}

	/**
	 *
	 * @param array $list
	 */
	public function setTableList($list) {
		$this->_tableList = $list;
	}

	/**
	 * @return array
	 */
	public function getTableList() {
		return $this->_tableList;
	}

	/**
	 *
	 * @param array $list
	 */
	public function setDependentTables($tables) {

		if (empty($this->_dependentTables)) {

			$this->_dependentTables = array();
		}

		$this->_dependentTables[$this->getTableName()] = $tables;
	}

	/**
	 * @return array
	 */
	public function getDependentTables() {
		return $this->_dependentTables[$this->getTableName()];
	}

	/**
	 *
	 * @param string $location
	 */
	public function setIncludePath($path) {
		$this->_includePath = $path;
	}

	/**
	 *
	 * @return string
	 */
	public function getIncludePath() {
		return $this->_includePath;
	}

	/**
	 *
	 *  removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param String $str
	 * @return String
	 */
	protected function _getCapital($str) {
		$temp='';
		foreach (explode("_",$str) as $part) {
			$temp.=ucfirst($part);
		}
		
		$temp2 = '';
		foreach (explode("-",$temp) as $part) {
			$temp2.=ucfirst($part);
		}
		
		return $temp2;
	}
	

	/**
	 *
	 *  removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param String $str
	 * @return String
	 */
	protected function _normalize($str) {

		$temp = '';
		$iteration = 0;

		foreach (explode("-",$str) as $part) {

			if ($iteration > 0) {

				$part = ucfirst($part);
			}

			$temp .= $part;
			$iteration++;
		}
		
		return $temp;
	}

	/**
	 *	Removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _getClassName($str) {
		$temp='';
		// Remove common prefixes
		foreach ($this->_tablePrefixes as $prefix) {
		    if (preg_match("/^$prefix/i", $str)) {
		        // Only replace a single prefix
		        $str = preg_replace("/^$prefix/i", '', $str);
		        break;
		    }
		}

		// Remove common suffixes
		foreach ($this->_columnSuffixes as $suffix) {
		    if (preg_match("/$suffix$/i", $str)) {
		        // Only replace a single prefix
		        $str = preg_replace("/$suffix$/i", '', $str);
		        break;
		    }
		}

		foreach (explode("_",$str) as $part) {
			$temp.=ucfirst($part);
		}
		return $temp;
	}

	/**
	 *	Removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _getRelationName(array $relation_info, $type = 'parent', $allRelations = array()) {

        $md5 = $relation_info['key_name'] .$type;

        if (! isset($this->_existingAttributes[$this->getTableName()])) {

            $this->_existingAttributes[$this->getTableName()] = array();
        }

		if ($type == 'parent') {

			// Check if a column exists with the same resulting name
		    $strBase = $str = $this->_getClassName($relation_info['column_name']);
			
			foreach ($this->_columns[$this->getTableName()] as $column) {

				if ($column['capital'] == $str) { //TODO : Revisar

					$conflict = false;
					// Check if should use the table name so long as there is not another conflict
					foreach ($this->_dependentTables as $relation) {
						
						if (isset($relation['column_name'])) {
							
							$conflict = $conflict || $this->_getClassName($relation['column_name']) == $str;
						}
					}

                    if (!$conflict and count($allRelations) > 0) {

                        $kont = 0;

                        foreach ($allRelations as $item) {

                            if ($item['foreign_tbl_name'] == $relation_info['foreign_tbl_name']) {

                                $kont++;
                            }
                        }

                        if ($kont > 1) {

                            $conflict = true;
                        }
                    }

					if ($conflict) {
						$str = $this->_getClassName($relation_info['foreign_tbl_name']) . 'By' . $str;
					} else {
						$str = $this->_getClassName($relation_info['foreign_tbl_name']);
					}
				}
			}

		} else {

            $table_count = 0;
            // Determine if there are multiple fields that link to a single table
            foreach ($this->_dependentTables[$this->getTableName()] as $relation) {
                if ($relation_info['foreign_tbl_name'] == $relation['foreign_tbl_name']) {
                    $table_count++;
                }
            }

            $str = $this->_getClassName($relation_info['foreign_tbl_name']);

            if ($table_count > 1 ) {

                $str .= 'By' . $this->_getClassName($relation_info['column_name']);
            
            } else {

                foreach ($this->_existingAttributes[$this->getTableName()] as $key => $val) {
    
                    if ($val == $str and $key != $md5) {
    
                        $str .= 'By' . $this->_getClassName($relation_info['column_name']); 
                        break;   
                    }
                    
                }
            }
        }

		if (! isset($this->_existingAttributes[$this->getTableName()][$md5])) {
        	$this->_existingAttributes[$this->getTableName()][$md5] = $str;
		}

		return $str;
	}

    protected function _checkRelationConflict (array $relation_info, $type = 'dependant', $allRelations = array())
    {
        if ($type == 'parent') {

            // Check if a column exists with the same resulting name
            $str = $this->_getClassName($relation_info['column_name']);
            foreach ($this->_columns[$this->getTableName()] as $column) {
                if ($column['capital'] == $str) {
                    $conflict = false;
                    // Check if should use the table name so long as there is not another conflict
                    foreach ($this->_dependentTables as $relation) {
                        $conflict = $conflict || $this->_getClassName($relation['column_name']) == $str;
                    }

                    if (!$conflict and count($allRelations) > 0) {

                        $kont = 0;

                        foreach ($allRelations as $item) {

                            if ($item['foreign_tbl_name'] == $relation_info['foreign_tbl_name']) {

                                $kont++;
                            }
                        }

                        if ($kont > 1) {

                            $conflict = true;
                        }
                    }

                    
                }
            }

            if (isset($conflict)) {
                return $conflict;
            }

            //$relations = $this->_foreignKeysInfo;

        } else {

            $table_count = 0;
            // Determine if there are multiple fields that link to a single table
            foreach ($this->_dependentTables as $relation) {
                if ($relation_info['foreign_tbl_name'] == $relation['foreign_tbl_name']) {
                    $table_count++;
                }
            }

            $str = $this->_getClassName($relation_info['foreign_tbl_name']);
            if ($table_count > 1) {

                return true;
            }
        }

        return false;
    } 

	abstract public function getTablesNamesFromDb();

	/**
	 * converts database specific data types to PHP data types
	 *
	 * @param string $str
	 * @return string
	 */
	abstract protected function _convertTypeToPhp($str);

	public function parseTable() {
		$this->parseDescribeTable();
		$this->parseForeignKeys();
		$this->parseDependentTables();
	}

	abstract public function parseForeignKeys();

	abstract public function parseDependentTables();

	abstract public function parseDescribeTable();

    abstract protected function getPDOString($host, $port, $dbname);
    
    abstract protected function getPDOSocketString($socket, $dbname);
    
	/**
	 *
	 *  the class constructor
	 *
	 * @param Array $config
	 * @param String $dbname
	 * @param String $namespace
	 */
	function __construct($config,$dbname,$namespace, $tplPrefix = '') {

		$columns=array();
		$primaryKey=array();

		$this->_config = $config;
		$this->_addRequire=$config['include.addrequire'];
		$pdoString = "";
	    if ($this->_config['db.socket']) {

	     	$pdoString=$this->getPDOSocketString($this->_config['db.socket'],$dbname);
	     	
	    } else {

	    	$pdoString=$this->getPDOString($this->_config['db.host'], $this->_config['db.port'], $dbname);
	    }
		try {
		 $pdo = new PDO($pdoString,
		    $this->_config['db.user'],
		    $this->_config['db.password']
		 );

		 if (isset($this->_dbAdapter)) {

			require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
			 
			$this->_dbAdapter = Zend_Db::factory('Pdo_Mysql', array(
                'host'     => $this->_config['db.host'],
                'dbname'   => $dbname,
                'username' => $this->_config['db.user'],
                'password' => $this->_config['db.password'],
			));
		 }

		 $this->_pdo=$pdo;
		} catch (Exception $e) {
			die("pdo error: ".$e->getMessage()."\n");
		}

		//$this->_tbname=$tbname;
		$this->_namespace=$namespace;

        //tplPrefix
        if (empty($tplPrefix)) {

            $this->_tplPrefix = '';

        } else {

            $this->_tplPrefix = $tplPrefix.'_';
        }

		//docs section
		$this->_author = $this->_config['docs.author'];
		$this->_license = $this->_config['docs.license'];
		$this->_copyright = $this->_config['docs.copyright'];

		$this->_cacheManagerName = $this->_config['cache.manager_name'];
		$this->_cacheName = $this->_config['cache.name'];

		$this->_loggerName = $this->_config['log.logger_name'];

		$path = $this->_config['include.path'];
		if ( ! is_dir($path)) {
		    // Use path relative to root of the application
		    $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->_config['include.path'];
		}

		$this->setIncludePath($path . DIRECTORY_SEPARATOR);

		if (file_exists($this->getIncludePath() . 'IncludeDefault.php')) {
		    require_once $this->getIncludePath() . 'IncludeDefault.php';
		} else {
		    require_once __DIR__.DIRECTORY_SEPARATOR.'IncludeDefault.php';
		}
	}

	/**
	 *
	 * parse a tpl file and return the result
	 *
	 * @param String $tplFile
	 * @return String
	 */
	public function getParsedTplContents($tplFile, $vars = array()) {
		extract($vars);
        
        //var_dump($this->_tplPrefix); .$tplFile."\n";
        
		ob_start();

        if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$tplFile)) { //$this->_tplPrefix.$tplFile

          require(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$tplFile);  //$this->_tplPrefix.$tplFile

        } /*else {

            
           require(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$tplFile); 
        }*/

		$data=ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/**
	 * creates the DbTable class file
	 */
	function makeDbTableFile() {

		$class = 'DbTable\\' . $this->_className;
		$file = $this->getIncludePath() . $class . '.inc.php';
		if (file_exists($file)) {
			include_once $file;
			$include = new $class($this->_namespace);
			$this->_includeTable = $include;
		} else {
			$this->_includeTable = new DbTable_Default($this->_namespace);
		}

		$referenceMap='';
		$dbTableFile = $this->getLocation() . DIRECTORY_SEPARATOR . 'Mapper/Sql/DbTable' . DIRECTORY_SEPARATOR . $this->_className . '.php';

		$foreignKeysInfo=$this->getForeignKeysInfo();
		$references=array();
		foreach ($foreignKeysInfo as $info) {
			$refTableClass = $this->_namespace . '\\\\Mapper\\\\Sql\\\\DbTable\\\\' . $this->_getClassName($info['foreign_tbl_name']);
			$key = $this->_getCapital($info['key_name']);
			if (is_array($info['column_name'])) {
			    $columns = 'array(\'' . implode("', '", $info['column_name']) . '\')';
			} else {
			    $columns = "'" . $info['column_name'] . "'";
			}
			if (is_array($info['foreign_tbl_column_name'])) {
			    $refColumns = 'array(\'' . implode("', '", $info['foreign_tbl_column_name']) . '\')';
			} else {
			    $refColumns = "'" . $info['foreign_tbl_column_name'] . "'";
			}

			$references[]="
        '$key' => array(
          	'columns' => {$columns},
            'refTableClass' => '{$refTableClass}',
            'refColumns' => {$refColumns}
        )";
		}

		if (sizeof($references)>0) {
			$referenceMap="protected \$_referenceMap = array(".
			join(',',$references). "\n    );";
		}

		$dependentTables = '';
		$dependents = array();
		foreach ($this->getDependentTables() as $info) {

			$table = $this->_getClassName($info['foreign_tbl_name']);

			if (! in_array($table, $dependents)) {

				$dependents[] = $this->_getClassName($info['foreign_tbl_name']);
			}
		}

		if (sizeof($dependents) > 0) {
			$dependentTables = "protected \$_dependentTables = array(\n        '".
			join("',\n        '",$dependents). "'\n    );";
		}

		$vars = array('referenceMap' => $referenceMap, 'dependentTables' => $dependentTables);

		if (isset($this->_dbAdapter)) {

			$metadata = $this->_dbAdapter->describeTable($this->getTableName());
			$metadata = var_export($metadata, true);

			$vars['metadata'] = 'protected $_metadata = ' . str_replace("\n", "\n\t", $metadata) . ';';

		} else {

			$vars['metadata'] = '';
		}

		$vars['multilang'] = $this->_columns[$this->getTableName()];

		foreach ($vars['multilang'] as $key => $val) {

			if ($val['comment'] !== '[ML]') {

				unset($vars['multilang'][$key]);

			} else {

				$vars['multilang'][$key] = $val['field'];
			}
		}
		
		$vars['FSO'] = $this->_columns[$this->getTableName()];

		foreach ($vars['FSO'] as $key => $val) {

			if ($val['comment'] !== '[FSO]') {

				unset($vars['FSO'][$key]);

			} else {

				$vars['FSO'][$key] = $val['field'];
			}
		}

        /****************************
         ********** DBTable *********
         ****************************/
		$dbTableData=$this->getParsedTplContents('dbtable.tpl', $vars);

		if (!file_put_contents($dbTableFile, $dbTableData))
			die("Error: could not write db table file $dbTableFile.");

	}

	/**
	 * creates the Mapper class file
	 */
	function makeMapperFile() {

		$class = 'Mapper\\' . $this->_className;
		$file = $this->getIncludePath() . $class . '.inc.php';
		if (file_exists($file)) {
			include_once $file;
			$include = new $class($this->_namespace);
			$this->_includeMapper = $include;
		} else {
			$this->_includeMapper = new Mapper_Default($this->_namespace);
		}

        /********************************
         ************* Smart ************
         ********************************/
		$mapperFile = $this->getLocation().DIRECTORY_SEPARATOR.'Mapper/Sql'.DIRECTORY_SEPARATOR.$this->_className.'.php';

		$mapperData = $this->getParsedTplContents('mapper.tpl');

		if (!file_put_contents($mapperFile, $mapperData)) {
			die("Error: could not write mapper file $mapperFile.");
		}


        /********************************
         ************* Raw ************
         ********************************/
        $mapperFile = $this->getLocation().DIRECTORY_SEPARATOR.'Mapper/Sql/Raw'.DIRECTORY_SEPARATOR.$this->_className.'.php';

        $mapperData = $this->getParsedTplContents('raw_mapper.tpl');

        if (!file_put_contents($mapperFile, $mapperData)) {
            die("Error: could not write mapper file $mapperFile.");
        }

	}

	/**
	 * creates the model class file
	 */
	function makeModelFile() 
	{
		$class = 'Model\\' . $this->_className;

		$file = $this->getIncludePath() . $class . '.inc.php';
		if (file_exists($file)) {
			include_once $file;
			$include = new $class($this->_namespace);
			$this->_includeModel = $include;
		} else {
			$this->_includeModel = new Model_Default($this->_namespace);
		}

		$modelFile=$this->getLocation().DIRECTORY_SEPARATOR."Model/Raw/" . $this->_className.'.php';
		$modelData=$this->getParsedTplContents('model.tpl');

        $smartModelFile = $this->getLocation().DIRECTORY_SEPARATOR."Model/" . $this->_className.'.php';
        $smartModelData = $this->getParsedTplContents('smart_model.tpl');

		if (!file_put_contents($modelFile, $modelData)) {
			die("Error: could not write model file $modelFile.");
		}

        if (!file_put_contents($smartModelFile, $smartModelData)) {
            die("Error: could not write model file $modelFile.");
        }
	}

	/**
	 *
	 * creates all class files
	 *
	 * @return Boolean
	 */
	function doItAll() {

		$this->makeDbTableFile();
		$this->makeMapperFile();
		$this->makeModelFile();

        /****************************
         ********** Models *********
         ****************************/
		$modelFile = $this->getLocation() . DIRECTORY_SEPARATOR . 'Model/ModelAbstract.php';
        $rawModelFile = $this->getLocation() . DIRECTORY_SEPARATOR . 'Model/Raw/ModelAbstract.php';

		$rawModelData = $this->getParsedTplContents('raw_model_class.tpl');

        if (!file_put_contents($rawModelFile, $rawModelData))
            die("Error: could not write model file $rawModelFile.");

		$paginatorFile=$this->getLocation().DIRECTORY_SEPARATOR.'Model/Paginator.php';
		$paginatorData=$this->getParsedTplContents('paginator_class.tpl');

		if (!file_put_contents($paginatorFile, $paginatorData))
			die("Error: could not write model file $paginatorFile.");

		$mapperFile = $this->getLocation() . DIRECTORY_SEPARATOR . 'Mapper/Sql/Raw' . DIRECTORY_SEPARATOR . 'MapperAbstract.php';

        /****************************
         ********** Mappers *********
         ****************************/ 
		$mapperData = $this->getParsedTplContents('mapper_class.tpl');
        $rawMapperData = $this->getParsedTplContents('raw_mapper_class.tpl');

		if (!file_put_contents($mapperFile, $rawMapperData))
			die("Error: could not write mapper file $mapperFile.");

		$tableFile = $this->getLocation().DIRECTORY_SEPARATOR.'Mapper/Sql/DbTable'.DIRECTORY_SEPARATOR.'TableAbstract.php';

        /****************************
         ********** DBTable *********
         ****************************/
		$tableData = $this->getParsedTplContents('dbtable_class.tpl');

		if (!file_put_contents($tableFile, $tableData))
			die("Error: could not write model file $tableFile.");

		// Copy all files in include paths

		if (is_dir($this->getIncludePath() . 'model')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'model', $this->getLocation());
		} else {
		    echo $this->getIncludePath() . 'model';
		}

		if (is_dir($this->getIncludePath() . 'mapper')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'mapper', $this->getLocation() . 'mappers');
		}

		if (is_dir($this->getIncludePath() . 'dbtable')) {
			$this->copyIncludeFiles($this->getIncludePath() . 'dbtable', $this->getLocation() . 'DbTable');
		}

		return true;
	}

	protected function copyIncludeFiles($dir, $dest)
	{
	    $files = array();
	    $directory = opendir($dir);

	    while ($item = readdir($directory)){
		    // Ignore hidden files ('.' as first character)
	    	if (preg_match('/^\./', $item)) {
	        	continue;
	        }

	        copy($dir . DIRECTORY_SEPARATOR . $item, $dest . DIRECTORY_SEPARATOR . $item);
	    }
	    closedir($directory);
	}

}
