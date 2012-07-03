<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model Mapper
 *
 * @package <?=$namespace?>Mapper\Sql
 * @subpackage Raw
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */

/**
 * Abstract class that is extended by all mappers
 *
 * @package <?=$namespace?>Mapper\Sql
 * @subpackage Raw
 * @author <?=$this->_author."\n"?>
 */

namespace <?=$namespace?>Mapper\Sql\Raw;
abstract class MapperAbstract
{
    /**
     * $_dbTable - instance of <?=$namespace?>Model\DbTable\TableAbstract
     *
     * @var <?=$namespace?>Model\DbTable\TableAbstract
     */
    protected $_dbTable;

<?php if (! empty($this->_cacheManagerName)):?>
    /**
     * $_cache - Zend_Cache object as configured by Cache manager
     *
     * @var Zend_Cache
     */
    protected $_cache;

<?php endif; ?>
<?php if (! empty($this->_loggerName)):?>
    /**
     * $_logger - Zend_Log object
     *
     * @var Zend_Log
     */
    protected $_logger;

<?php endif; ?>

    /**
     * Simulate on delete cascade / set null / cascade for thoose
     * DB engines that do not trigger by their own (MySql cluster, MyIsam)
     */
    protected $_simulateReferencialActions = false;

    protected $_relationObjectName = null;

    /**
     * Setup the default configuration for the Mapper
     */
    public function __construct()
    {
<?php if (! empty($this->_cacheManagerName)):?>

        if( false and \Zend_Registry::isRegistered('memcache') ) {

            $this->_cache = \Zend_Registry::get('memcache');

        } else {

            $this->_cache = null;
        }

<?php else: ?>
        $this->_cache = false;
<?php endif; ?>
<?php if (! empty($this->_loggerName)):?>
        $this->_logger = \Zend_Registry::get('<?=$this->_loggerName ?>');
<?php endif; ?>

        $bootstrap = \Zend_Controller_Front::getInstance()->getParam('bootstrap');

        if (is_null($bootstrap)) {

            $conf = new \Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini',APPLICATION_ENV);
            $conf = (Object) $conf->toArray();

        } else {

            $conf = (Object) $bootstrap->getOptions();
        }

        if (isset($conf->mappers)) {

            $mapperConf = (Object) $conf->mappers;

            //Check for mappers.simulateReferencialActions in application.ini
            if (isset($mapperConf->simulateReferencialActions) and $mapperConf->simulateReferencialActions) {

                $this->_simulateReferencialActions = true;
            }
        }
    }

    protected function getAdapter()
    {
        return $this->getDbTable()->getAdapter();
    }

    /**
     * Sets the dbTable class
     *
     * @param <?=$namespace?>Model\DbTable\TableAbstract $dbTable
     * @return <?=$namespace?>Mapper\Sql\Raw\MapperAbstract
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }

        if (! $dbTable instanceof \Zend_Db_Table_Abstract) {
<?php if (! empty($this->_loggerName)):?>
            if (is_object($dbTable)) {
                $message = get_class($dbTable) . " is not a Zend_Db_Table_Abstract object in setDbTable for " . get_class($this);
            } else {
                $message = "$dbTable is not a Zend_Db_Table_Abstract object in setDbTable for " . get_class($this);
            }

            $this->_logger->log($message, \Zend_Log::ERR);

<?php endif; ?>
            throw new \Exception('Invalid table data gateway provided', 1006);
        }

        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * Returns the underlying Zend Rowset for the mapped class given
     * the primary key
     *
     * @param array|string $primary_key Primary Key
     * @return Zend_Db_Table_Rowset_Abstract|null
     */
    protected function getRowset($primary_key)
    {
        $composite = false;

        if (is_array($primary_key)) {
            // Determine if this is array to include multiple rows, or an array for a composite key
            $keys = array_keys($primary_key);
            foreach ($keys as $key) {
                if (! is_numeric($key)) {
                    $composite = true;
                }
            }
        }

        if ($composite) {
            $key_components = array();
            // Split the array into multiple arrays to maintain key matching
            foreach ($primary_key as $key_name => $key_value) {
                $key_components[] = array($key_name => $key_value);
            }

            $result = call_user_func_array(array($this->getDbTable(), 'find'), $key_components);
        } else {
            $result = $this->getDbTable()->find($primary_key);
        }


        if (count($result) == 0) {
            return null;
        }

        return $result;
    }

    protected function setRelationObjectName ($name)
    {
        $this->_relationObjectName = $name;
    }

    public function getRelationObjectName()
    {
        return $this->_relationObjectName;
    }


    /**
     * Fetches related object and sets it for the instance.
     *
     * @param string $name Key or table name of the relation (key or table) to load
     * @param <?=$namespace?>Model\ModelAbstract $model
     * @throws \Exception If the relation could not be found
     * @return $model
     */
    public function loadRelated($name, $model, $conditions = '', $orderBy = '')
    {
        // Create a Zend_Db_Table_Row from the data in $model
        $row = $this->getDbTable()->createRow($this->toArray($model));

        $parents = $model->getParentList();
        $dependents = $model->getDependentList();

        $method = 'find';
        $type = 'dependent';

        $name = ucfirst($name);

        // Determine what $name is: key name or table name. Try keys first
        if (array_key_exists($name, $parents)) {

            $property = $parents[$name]['property'];
            $object_table_name = $parents[$name]['table_name'];
            $table_class = '<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $object_table_name;
            $rule = $name;
            $type = 'parent';

        } elseif (array_key_exists($name, $dependents)) {

            $property = $dependents[$name]['property'];
            $object_table_name = $dependents[$name]['table_name'];
            $ref_table_name = '<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $object_table_name;
            $rule = $name;

        } else {
<?php if (! empty($this->_loggerName)):?>
            $this->_logger->log("$name is not a defined relationship in loadRelated for " . get_class($this), \Zend_Log::ERR);

<?php endif; ?>
            throw new \Exception("Relationship $name not found", 1006);
        }

        $this->setRelationObjectName($object_table_name);

        if ($this->_cache &&
            $this->_cache->test(get_class($model) . "_" . $model->getPrimaryKey(). "_" . $name)
        ) {

            $tmp = $this->_cache->load(get_class($model) . "_" . $model->getPrimaryKey(). "_" . $name);

            $missingPKs = array();
            $primaryKeys = $tmp->results;
            $className = $tmp->className;

            $obj = array();

            foreach ($primaryKeys as $pk) {

                if ($this->_cache->test($className . "_" . $pk)) {

                    $newObj = new $className();
                    $data = $this->_cache->load($className . "_" . $pk);

                    $newObj->setOptions($data);

                    $obj[$pk] = $newObj;

                } else {

                    $missingPKs[] = $pk;
                }
            }

            if (count($missingPKs) > 0) {

                $targetObj = new $className();
                $targetMapper = $targetObj->getMapper();

                $where = $targetObj->getPrimaryKeyName(). " in (".implode("," , $missingPKs).")";
                $uncachedResults = $targetMapper->fetchList($where);

                foreach ($uncachedResults as $item) {

                    foreach ($missingPKs as $key => $val) {

                        if ($val == $item->getPrimarykey()) {

                            unset($missingPKs[$key]);
                        }
                    }

                    $obj[$item->getPrimarykey()] = $item;
                    $this->_cache->save($item->toArray(), get_class($item) . "_" . $item->getPrimaryKey());
                }

                //Eliminar elementos perdidos de la relación
                if (count($missingPKs) > 0) {

                    foreach ($primaryKeys as $key => $val) {

                        if (in_array($val, $missingPKs)) {

                            unset($primaryKeys[$key]);
                        }
                    }

                    $cachedRelation->results = $primaryKeys;

                    $this->_cache->save($cachedRelation, get_class($model) . "_" . $model->getPrimaryKey(). "_" . $name);
                }
            }

        } else {

            if ($type == 'parent') {
                $method .= 'ParentRow';
                $ref_table = $this->getDbTable();
                $column_type = 'columns';
                $table_name = $table_class;
            } else {
                $method .= 'DependentRowset';
                $ref_table = new $ref_table_name();
                $column_type = 'refColumns';
                $table_name = get_class($this->getDbTable());
            }


            $reference = $ref_table->getReference($table_name, $rule);
            if (empty($reference)) {
    <?php if (! empty($this->_loggerName)):?>
                $this->_logger->log("Could not find a reference for $rule in $table_name in loadRelated for " . get_class($this), \Zend_Log::ERR);

    <?php endif; ?>
                throw new \Exception("Relationship not found: $table_name; rule: $rule", 1006);
            }

            // Check to make sure the foreign key value is set
            // Return early as relationships cannot be joined against null values
            $columns = $reference[$column_type];

            if (is_array($columns)) {

                foreach ($columns as $column) {
                    if ($model->$column === null) {

                        return $model;
                    }
                }

            } else {

                if ($model->$columns === null) {

                    return $model;
                }
            }

            //parent ¿?
            $where = array();
            $pos = 0;
            foreach ($reference["refColumns"] as $key => $val) {

                if (isset($reference["columns"][$pos])) {

                    $fieldName = $reference["columns"][$pos];

                    if (isset($row->$fieldName)) {

                        $where[$val] =  $row->$fieldName;
                    }
                }

                $pos++;
            }

            //dependant ¿?
            if (count($where) < 1) {

                $pos = 0;
                foreach ($reference["columns"] as $key => $val) {

                    if (isset($reference["refColumns"][$pos])) {

                        $fieldName = $reference["refColumns"][$pos];

                        if (isset($row->$fieldName)) {

                            $where[$val] = $row->$fieldName;
                        }
                    }

                    $pos++;
                }

            }

            if (count($where) < 1) {

                return $model;
            }

            $relMapperClassName = "\\<?=$namespace?>\Mapper\\Sql\\" . $object_table_name;
            $relMapper = new $relMapperClassName;

            if ($type == 'parent' and count($where) === 1) {

                $obj = $relMapper->find(current($where));

            } else {

                $tmp = array();
                foreach ($where as $key => $value) {

                    $tmp[] = $key . " = '" . $value . "'";
                }
                $where = implode(" AND ", $tmp);

                $binding = false;

                if (! empty($conditions)) {

                    if (is_array($conditions)) {

                        if (
                            count($conditions) == 2 and is_string($conditions[0])
                            and is_array($conditions[1]) and count($conditions[1]) > 0
                        ) {

                            $keys = array_keys($conditions[1]);
                            $binding = true;

                            foreach ($keys as $key) {

                                if (strpos($conditions[0], $key) === false) {

                                    $binding = false;
                                }
                            }

                        } else {

                            $tmp = array();
                            foreach ($conditions as $key => $value) {

                                $tmp[] = $key . " = '" . $value . "'";
                            }

                            $conditions = implode(", ", $tmp);
                        }
                    }

                    if (empty($where)) {

                        $where = $conditions;

                    } else {

                        if (!is_array($conditions) or $binding = false) {

                            $where .= ' AND ' . $conditions;

                        } else {

                            $conditions[0] = $where . ' AND ' . $conditions[0];
                            $where = $conditions;
                        }
                    }
                }

                $obj = $relMapper->fetchList($where, $orderBy);

                if (! empty($obj) and is_array($obj) and $this->_cache) {

                    $pks = array();
                    $objClass = null;

                    foreach ($obj as $item) {

                        $pks[] = $item->getPrimaryKey();
                        $this->_cache->save($item->toArray(), get_class($item)."_".$item->getPrimaryKey());

                        if (is_null($objClass) and is_object($item)) {

                            $objClass = get_class($item);
                        }
                    }

                    if (count($pks) > 0) {

                        $relationCache = new \stdClass();
                        $relationCache->className = $objClass;
                        $relationCache->results = $pks;

                        $this->_cache->save($relationCache, get_class($model) . "_" . $model->getPrimaryKey(). "_" . $name);
                    }
                }
            }
        }

        //-------------------------------------------------------------------------------------------------------------

        if (! empty($obj)) {

            $model_class = '<?=$namespace?>\Model\\' . $object_table_name;

            if (is_array($obj)) {

                if (method_exists($model, 'add' . $property)) {
                    $class_method = 'add' . $property;

                    foreach ($obj as $related) {

                        $model->$class_method($related);
                    }

                } else {

                    $class_method = 'set' . $property;
                    if (count($obj) == 1) {

                        $obj = array_shift($obj);
                    }

                    $model->$class_method($obj);
                }

            } else {

                $method_name = 'set' . $property;
                $model->$method_name($obj);
            }
        }

        return $model;
    }

    /**
     * Returns the number of rows in the table
     *
     * @see <?=$namespace?>Model\DbTable\TableAbstract::countAllRows()
     * @return int The total count
     */
    public function countAllRows()
    {
        return $this->getDbTable()->countAllRows();
    }

    /**
     * Returns the count of this object
     * Optionally with a where parameter specified
     *
     * @see <?=$namespace?>Model\DbTable\TableAbstract::countByQuery()
     * @return int Count with the given parameters
     */
    public function countByQuery($where = '')
    {
        return $this->getDbTable()->countByQuery($where);
    }

    /**
     * Deletes the current model
     *
     * @param <?=$namespace?>Model\ModelAbstract $model The model to delete
     * @return int
     */
    public abstract function delete(\<?=$namespace?>Model\Raw\ModelAbstract $model);

    /**
     * Creates a Zend_Paginator class by a given select
     *
     * @param Zend_Db_Select $query
     * @return Zend_Paginator
     */
    protected function selectToPaginator(Zend_Db_Select $select)
    {
        $adapter = new \<?=$namespace?>Model\Paginator($select, $this);
        $paginator = new \Zend_Paginator($adapter);

        return $paginator;
    }

    /**
     * Fetches all rows
     *
     * @return array
     */
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = $this->loadModel($row, null);
            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * Fetch all rows into a Zend_Paginator
     *
     * @return Zend_Paginator
     */
    public function fetchAllToPaginator()
    {
        return $this->selectToPaginator($this->getDbTable()
                    ->select()
                    ->from($this->getDbTable()->getTableName()));
    }

    /**
     * Fetch all rows in a 3-dimensional array
     *
     * @return array
     */
    public function fetchAllToArray()
    {
        $resultSet = $this->getDbTable()->fetchAll()->toArray();
        return $resultSet;
    }

    /**
     * Fetches all rows optionally filtered by where, order, count, and offset
     *
     * @param string $where Where clause
     * @param string $order Fields to order by
     * @param int $count Number to limit
     * @param int $offset Initial offset for query
     * @return array All rows with the given parameters as objects
     */
    public function fetchList($where = null, $order = null, $count = null,
        $offset = null
    ) {
        $resultSet = $this->getDbTable()
                          ->fetchAll(
                                $this->getDbTable()
                                     ->fetchList($where, $order, $count, $offset));
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = $this->loadModel($row, null);
            $entries[] = $entry;
        }

        if ($count == 1) {

            return array_shift($entries);
        }

        return $entries;
    }

    /**
     * Fetches all rows
     * optionally filtered by where, order, count and offset
     * returns a 3d-array of the result
     *
     * @return array
     */
    public function fetchListToArray($where = null, $order = null,
        $count = null, $offset = null
    ) {
        return $this->getDbTable()
                    ->fetchAll($this->getDbTable()
                                    ->fetchList($where, $order, $count, $offset))
                    ->toArray();
    }

    /**
     * Fetches all rows
     * Optionally filtered by where, order, count and offset
     *
     * @return Zend_Paginator Paginator with the given parameters
     */
    public function fetchListToPaginator($where = null, $order = null,
        $count = null, $offset = null
    ) {
        return $this->selectToPaginator(
            $this->getDbTable()
                 ->fetchList($where, $order, $count, $offset)
        );
    }

    /**
     * Finds rows where $field equals $value.
     *
     * If field is an associative array, this will expect a column to value
     * matching. If field is a non-associative array, it will expect that value
     * is an array that is either associative with the key being the column
     * name, or that it is in the same order as the columns in the field.
     *
     * @param string|array $field The field or fields to search by
     * @param mixed|array $value Value(s) to search for
     * @return array All <?=$namespace?>Model\ModelAbstract meeting the criteria
     */
    public function findByField($field, $value = null)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $result = array();

        if (is_array($field)) {
            // Check if $field is an associative array
            if (isset($field[0]) && is_array($value)) {
                // If field and value are arrays, match them up
                foreach ($field as $column) {
                    if (isset($value[$column])) {
                        $select->where("{$column} = ?", $value[$column]);
                    } else {
                        $select->where("{$column} = ?", array_shift($value));
                    }
                }
            } else {
                // field is an associative array, use the values from the field
                foreach ($field as $column => $value) {
                    $select->where("{$column} = ?", $value);
                }
            }
        } else {
            $select->where("{$field} = ?", $value);
        }

        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            $model = $this->loadModel($row, null);
            $result[] = $model;
        }

        return $result;
    }

    /**
     * Finds a row where $field equals $value. If $field is an array, then the
     * value should also be an array.
     *
     * @param string|array $field The field or fields to search by
     * @param mixed|array $value
     * @param <?=$namespace?>Model\ModelAbstract|null $model
     * @return <?=$namespace?>Model\ModelAbstract|null The matching models found or null if not found
     */
    public function findOneByField($field, $value = null, $model = null)
    {
        $table = $this->getDbTable();
        $select = $table->select();

        if (is_array($field)) {
            // Check if $field is an associative array
            if (isset($field[0]) && is_array($value)) {
                // If field and value are arrays, match them up
                foreach ($field as $column) {
                    if (isset($value[$column])) {
                        $select->where("{$column} = ?", $value[$column]);
                    } else {
                        $select->where("{$column} = ?", array_shift($value));
                    }
                }
            } else {
                // field is an associative array, use the values from the field
                foreach ($field as $column => $value) {
                    $select->where("{$column} = ?", $value);
                }
            }
        } else {
            $select->where("{$field} = ?", $value);
        }

        $row = $table->fetchRow($select);
        if (count($row) === 0) {
            return null;
        }

        $model = $this->loadModel($row, $model);

        return $model;
    }


    /**
     * Return the Zend_Db_Table_Select class
     *
     * @param bool $withFromPart
     * @return Zend_Db_Table_Select
     */
    public function getSelect($withFromPart = true, $resetColumns = true,
        $resetOrder = true, $resetLimitOffset = true
    ) {
        $select = $this->getDbTable()->select($withFromPart);

        if ($resetColumns) {
            $select->reset(\Zend_Db_Select::COLUMNS);
        }

        if ($resetOrder) {
            $select->reset(\Zend_Db_Select::ORDER);
        }

        if ($resetLimitOffset) {
            $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        }

        return $select;
    }

    /**
     * Returns a Zend_Paginator class from a query string
     *
     * @param string $sql
     * @return Zend_Paginator
     */
    public function queryToPaginator($sql)
    {
        $result = $this->getDbTable()->getAdapter()->fetchAll($sql);
        $paginator = \Zend_Paginator::factory($result);
        return $paginator;
    }

    /**
     * Returns the dbTable class
     *
     * @return <?=$namespace?>Model\DbTable\TableAbstract
     */
    public abstract function getDbTable();

    /**
     * Returns an array, keys are the field names.
     *
     * @param new <?=$namespace?>Model\ModelAbstract $model
     * @return array
     */
    public abstract function toArray($model);

    /**
     * Loads the model specific data into the model object
     *
     * @param Zend_Db_Table_Row_Abstract|array $data The data as returned from a Zend_Db query
     * @param <?=$namespace?>Model\ModelAbstract|null $entry The object to load the data into, or null to have one created
     */
    protected abstract function loadModel($data, $entry);

    /**
     * Finds row by primary key
     *
     * @param string|array $primary_key
     * @param <?=$namespace?>Model\ModelAbstract $model
     */
    public abstract function find($primary_key, $model);
}
