<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model Mapper
 *
 * @package <?=$namespace?>Mapper\Sql
 * @subpackage Raw
 * @author <?=$this->_author . "\n"?>
 * @copyright <?=$this->_copyright . "\n"?>
 * @license <?=$this->_license . "\n"?>
 */

/**
 * Data Mapper implementation for <?=$namespace?>Model\<?=$this->_className . "\n"?>
 *
 * @package <?=$namespace?>Mapper\Sql
 * @subpackage Raw
 * @author <?=$this->_author . "\n"?>
 */

namespace <?=$namespace?>Mapper\Sql\Raw;
class <?=$this->_className?> extends <?=$this->_includeMapper->getParentClass() . "\n"?>
{
    protected $_modelName = '<?=$namespace?>\Model\\<?=$this->_className?>';

<?php $vars = $this->_includeMapper->getVars();
if (!empty($vars)) {
echo "\n$vars\n";
}
?>
    /**
     * Returns an array, keys are the field names.
     *
     * @param <?=$namespace?>Model\<?=$this->_className?> $model
     * @return array
     */
    public function toArray($model)
    {
        if (!$model instanceof \<?=$namespace?>Model\<?=$this->_className?>) {
<?php if (!empty($this->_loggerName)):?>
            if (is_object($model)) {
                $message = get_class($model) . " is not a \<?=$namespace?>Model\<?=$this->_className?> object in toArray for " . get_class($this);
            } else {
                $message = "$model is not a \\<?=$namespace?>Model\\<?=$this->_className?> object in toArray for " . get_class($this);
            }

            $this->_logger->log($message, \Zend_Log::ERR);

<?php endif; ?>
            throw new \Exception('Unable to create array: invalid model passed to mapper', 2000);
        }

        $result = array(<?php echo "\n";
foreach ($this->_columns[$this->getTableName()] as $column):

    if (stristr($column['comment'], '[ml]')) {
        continue;
    }
            ?>
            '<?=$column['field']?>' => $model->get<?=$column['capital']?>(),
<?php endforeach;?>
        );

        return $result;
    }

    /**
     * Returns the DbTable class associated with this mapper
     *
     * @return <?=$namespace?>\Mapper\\Sql\\DbTable\\<?=$this->_className . "\n";?>
     */
    public function getDbTable()
    {
        if (is_null($this->_dbTable)) {
            $this->setDbTable('<?=$namespace?>\Mapper\\Sql\\DbTable\\<?=$this->_className?>');
        }

        return $this->_dbTable;
    }

    /**
     * Deletes the current model
     *
     * @param <?=$namespace?>Model\<?=$this->_className?> $model The model to <?php if ($this->_softDeleteColumn != null): ?>mark as deleted
<?php else: ?>delete
<?php endif;?>
<?php if ($this->_softDeleteColumn == null): ?>
     * @see <?=$namespace?>Mapper\DbTable\TableAbstract::delete()
<?php endif;?>
     * @return int
     */
    public function delete(\<?=$namespace?>Model\Raw\ModelAbstract $model)
    {
        if (!$model instanceof \<?=$namespace?>Model\<?=$this->_className?>) {
<?php if (!empty($this->_loggerName)):?>
            if (is_object($model)) {
                $message = get_class($model) . " is not a \\<?=$namespace?>\Model\\<?=$this->_className?> object in delete for " . get_class($this);
            } else {
                $message = "$model is not a \\<?=$namespace?>\Model\\<?=$this->_className?> object in delete for " . get_class($this);
            }

            $this->_logger->log($message, \Zend_Log::ERR);

<?php endif; ?>
            throw new \Exception('Unable to delete: invalid model passed to mapper', 2000);
        }

        $useTransaction = true;

        try {

            $this->getDbTable()->getAdapter()->beginTransaction();

        } catch (\Exception $e) {

            //Transaction already started
            $useTransaction = false;
        }

        try {

            //onDeleteCascades emulation
            if ($this->_simulateReferencialActions && count($deleteCascade = $model->getOnDeleteCascadeRelationships()) > 0) {

                $depList = $model->getDependentList();

                foreach ($deleteCascade as $fk) {

                    $capitzalizedFk = '';
                    foreach (explode("_", $fk) as $part) {

                        $capitzalizedFk .= ucfirst($part);
                    }

                    if (!isset($depList[$capitzalizedFk])) {

                        continue;

                    } else {

                        $relDbAdapName = '<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $depList[$capitzalizedFk]["table_name"];
                        $depMapperName = '<?=$namespace?>\Mapper\\Sql\\' . $depList[$capitzalizedFk]["table_name"];
                        $depModelName = '<?=$namespace?>\Model\\' . $depList[$capitzalizedFk]["table_name"];

                        if ( class_exists($relDbAdapName) && class_exists($depModelName) ) {

                            $relDbAdapter = new $relDbAdapName;
                            $references = $relDbAdapter->getReference('<?=$namespace?>\Mapper\\Sql\\DbTable\\<?=$this->_className?>', $capitzalizedFk);

                            $targetColumn = array_shift($references["columns"]);
                            $where = $relDbAdapter->getAdapter()->quoteInto($targetColumn . ' = ?', $model->getPrimaryKey());

                            $depMapper = new $depMapperName;
                            $depObjects = $depMapper->fetchList($where);

                            if (count($depObjects) === 0) {

                                continue;
                            }

                            foreach ($depObjects as $item) {

                                $item->delete();
                            }
                        }
                    }
                }
            }

            //onDeleteSetNull emulation
            if ($this->_simulateReferencialActions && count($deleteSetNull = $model->getOnDeleteSetNullRelationships()) > 0) {

                $depList = $model->getDependentList();

                foreach ($deleteSetNull as $fk) {

                    $capitzalizedFk = '';
                    foreach (explode("_", $fk) as $part) {

                        $capitzalizedFk .= ucfirst($part);
                    }

                    if (!isset($depList[$capitzalizedFk])) {

                        continue;

                    } else {

                        $relDbAdapName = '<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $depList[$capitzalizedFk]["table_name"];
                        $depMapperName = '<?=$namespace?>\Mapper\\Sql\\' . $depList[$capitzalizedFk]["table_name"];
                        $depModelName = '<?=$namespace?>\Model\\' . $depList[$capitzalizedFk]["table_name"];

                        if ( class_exists($relDbAdapName) && class_exists($depModelName) ) {

                            $relDbAdapter = new $relDbAdapName;
                            $references = $relDbAdapter->getReference('<?=$namespace?>\Mapper\\Sql\\DbTable\\<?=$this->_className?>', $capitzalizedFk);

                            $targetColumn = array_shift($references["columns"]);
                            $where = $relDbAdapter->getAdapter()->quoteInto($targetColumn . ' = ?', $model->getPrimaryKey());

                            $depMapper = new $depMapperName;
                            $depObjects = $depMapper->fetchList($where);

                            if (count($depObjects) === 0) {

                                continue;
                            }

                            foreach ($depObjects as $item) {

                                $setterName = 'set' . ucfirst($targetColumn);
                                $item->$setterName(null);
                                $item->save();
                            } //end foreach

                        } //end if
                    } //end else

                }//end foreach ($deleteSetNull as $fk)
            } //end if

<?php if ($this->_softDeleteColumn != null):
        foreach ($this->_columns[$this->getTableName()] as $column):
            if ($column['field'] == $this->_softDeleteColumn) :

                $param = 1;

                if ($column['phptype'] == 'boolean') {
                    $param = 'true';
                } elseif (preg_match('/date/', $column['type'])) {
                    $param = '\Zend_Date::now()';
                }
?>
            $model->set<?=$column['capital']?>(<?=$param?>);
<?php
                break;
            endif;
        endforeach;
?>

        } catch (\Exception $e) {
<?php if (! empty($this->_loggerName)):?>
            $message = 'Exception encountered while attempting to delete ' . get_class($this);
            if (! empty($where)) {
                $message .= ' Where: ';
<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') : ?>
                foreach ($where as $where_clause) {
                    $message .= $where_clause;
                }
<?php else: ?>
                $message .= $where;
<?php endif; ?>
            } else {
                $message .= ' with an empty where';
            }

            $message .= ' Exception: ' . $e->getMessage();
            $this->_logger->log($message, \Zend_Log::ERR);
            $this->_logger->log($e->getTraceAsString(), \Zend_Log::DEBUG);

<?php endif; ?>

            if ($useTransaction) {

                $this->getDbTable()->getAdapter()->rollback();
            }

            $result = false;
        }

         $result = $model->save();

<?php else: ?>
<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') : ?>
            $where = array();
        <?php foreach ($this->_primaryKey[$this->getTablename()][$this->getTablename()]['fields'] as $key) : ?>

            $pk_val = $model->get<?=$key['capital']?>();
            if (is_null($pk_val)) {
<?php if (!empty($this->_loggerName)):?>
                $this->_logger->log('The value for <?=$key['capital']?> cannot be null in delete for ' . get_class($this), \Zend_Log::ERR);

<?php endif; ?>
                throw new \Exception('The value for <?=$key['capital']?> cannot be null', 2000);
            } else {
                $where[] = $this->getDbTable()->getAdapter()->quoteInto('<?=$key['field']?> = ?', $pk_val);
            }
<?php endforeach; ?>
<?php else :?>
            $where = $this->getDbTable()->getAdapter()->quoteInto('<?=$this->_primaryKey[$this->getTablename()]['field']?> = ?', $model->get<?=$this->_primaryKey[$this->getTablename()]['capital']?>());
<?php endif; ?>
            $result = $this->getDbTable()->delete($where);

            if ($this->_cache) {

                $this->_cache->remove(get_class($model) . "_" . $model->getPrimarykey());
            }

            $fileObjects = array();
            $availableObjects = $model->getFileObjects();

            foreach ($availableObjects as $fso) {

                $removeMethod = 'remove' . $fso;
                $model->$removeMethod();
            }

            if ($useTransaction) {
                $this->getDbTable()->getAdapter()->commit();
            }

        } catch (\Exception $exception) {
<?php if (!empty($this->_loggerName)):?>
            $message = 'Exception encountered while attempting to delete ' . get_class($this);
            if (!empty($where)) {
                $message .= ' Where: ';
<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') : ?>
                foreach ($where as $where_clause) {
                    $message .= $where_clause;
                }
<?php else: ?>
                $message .= $where;
<?php endif; ?>
            } else {
                $message .= ' with an empty where';
            }

            $message .= ' Exception: ' . $exception->getMessage();
            $this->_logger->log($message, \Zend_Log::ERR);
            $this->_logger->log($exception->getTraceAsString(), \Zend_Log::DEBUG);

<?php endif; ?>

            if ($useTransaction) {

                $this->getDbTable()->getAdapter()->rollback();
            }

            throw $exception;
        }

        return $result;
<?php endif; ?>
    }

    /**
     * Saves current row
     * @return boolean If the save action was successful
     */
    public function save(\<?=$namespace?>Model\<?=$this->_className?> $model)
    {
        return $this->_save($model, false, false);
    }

    /**
     * Saves current and all dependent rows
     *
     * @param \<?=$namespace?>Model\<?=$this->_className?> $model
     * @param boolean $useTransaction Flag to indicate if save should be done inside a database transaction
     * @return boolean If the save action was successful
     */
    public function saveRecursive(\<?=$namespace?>Model\<?=$this->_className?> $model, $useTransaction = true, $transactionTag = null)
    {
        return $this->_save($model, true, $useTransaction, $transactionTag);
    }

    protected function _save(\<?=$namespace?>Model\<?=$this->_className?> $model,
        $recursive = false, $useTransaction = true, $transactionTag = null
    )
    {
        $this->_setCleanUrlIdentifiers($model);

        $fileObjects = array();

        $availableObjects = $model->getFileObjects();
        $fileSpects = array();

        foreach ($availableObjects as $item) {

            $objectMethod = 'fetch' . $item;
            $fso = $model->$objectMethod(false);

            if (!is_null($fso) && $fso->mustFlush()) {

                $fileObjects[$item] = $fso;
                $specMethod = 'get' . $item . 'Specs';
                $fileSpects[$item] = $model->$specMethod();

                $fileSizeSetter = 'set' . $fileSpects[$item]['sizeName'];
                $baseNameSetter = 'set' . $fileSpects[$item]['baseNameName'];
                $mimeTypeSetter = 'set' . $fileSpects[$item]['mimeName'];

                $model->$fileSizeSetter($fso->getSize())
                      ->$baseNameSetter($fso->getBaseName())
                      ->$mimeTypeSetter($fso->getMimeType());
            }
        }

        $data = $model->sanitize()->toArray();

<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') : ?>
        $primaryKey = array();
<?php foreach ($this->_primaryKey[$this->getTablename()]['fields'] as $key) : ?>

        $pk_val = $model->get<?=$key['capital']?>();
        if (is_null($pk_val)) {
<?php if (!empty($this->_loggerName)):?>
            $this->_logger->log('The value for <?=$key['capital']?> cannot be null in save for ' . get_class($this), \Zend_Log::ERR);
<?php endif; ?>
            Throw new \Exception("The value for <?=$key['capital']?> cannot be null", 2000);
        } else {
            $primaryKey['<?=$key['field']?>'] = $pk_val;
        }
<?php endforeach; ?>

        $exists = $this->find($primaryKey);
        $success = true;

        if ($useTransaction) {

            try {

                if ($recursive && is_null($transactionTag)) {

                    //$this->getDbTable()->getAdapter()->query('SET transaction_allow_batching = 1');
                }

                $this->getDbTable()->getAdapter()->beginTransaction();

            } catch (\Exception $e) {

                //transaction already started
            }

            if (is_null($transactionTag)) {

                $transactionTag = 't_' . rand(1, 999) . str_replace(array(".", " "), "", microtime());
            }
        }

        try {
            // Check for current existence to know if needs to be inserted
            if (is_null($exists)) {
                $this->getDbTable()->insert($data);
<?php else :?>
        $primaryKey = $model->get<?=$this->_primaryKey[$this->getTablename()]['capital']?>();
        $success = true;

        if ($useTransaction) {

            try {

                if ($recursive && is_null($transactionTag)) {

                    //$this->getDbTable()->getAdapter()->query('SET transaction_allow_batching = 1');
                }

                $this->getDbTable()->getAdapter()->beginTransaction();

            } catch (\Exception $e) {

                //transaction already started
            }


            $transactionTag = 't_' . rand(1, 999) . str_replace(array('.', ' '), '', microtime());
        }

<?php if (!$this->_primaryKey[$this->getTablename()]['foreign_key']): ?>
        unset($data['<?=$this->_primaryKey[$this->getTablename()]['field']?>']);

        try {
            if (is_null($primaryKey) || empty($primaryKey)) {
<?php else: ?>
        $exists = $this->find($primaryKey);

        try {
            if (is_null($exists)) {
<?php endif; ?>
                $primaryKey = $this->getDbTable()->insert($data);
                if ($primaryKey) {
                    $model->set<?=$this->_primaryKey[$this->getTablename()]['capital']?>($primaryKey);
                } else {
                    Throw new \Exception("Insert sentence did not return a valid primary key", 9000);
                }

                if ($this->_cache) {

                    $parentList = $model->getParentList();

                    foreach ($parentList as $constraint => $values) {

                        $refTable = $this->getDbTable();

                        $ref = $refTable->getReference('<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $values["table_name"], $constraint);
                        $column = array_shift($ref["columns"]);

                        $cacheHash = '<?=$namespace?>\Model\\' . $values["table_name"] . '_' . $data[$column] .'_' . $constraint;

                        if ($this->_cache->test($cacheHash)) {

                            $cachedRelations = $this->_cache->load($cacheHash);
                            $cachedRelations->results[] = $primaryKey;

                            if ($useTransaction) {

                                $this->_cache->save($cachedRelations, $cacheHash, array($transactionTag));

                            } else {

                                $this->_cache->save($cachedRelations, $cacheHash);
                            }
                        }
                    }
                }
<?php endif;?>
            } else {
                $this->getDbTable()
                     ->update(
                         $data,
                         array(<?php echo "\n                                 ";
            if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') {
                $fields = count($this->_primaryKey[$this->getTablename()]['fields']);
                $i = 0;
                foreach ($this->_primaryKey[$this->getTablename()]['fields'] as $key) {
                    echo '\'' . $key['field'] . ' = ?\' => $primaryKey[\'' . $key['field'] . '\']';
                    $i++;
                    if ($i != $fields) {
                        echo ",\n                                 ";
                    }
                }
            } else {
                echo '\'' . $this->_primaryKey[$this->getTablename()]['field'] . ' = ?\' => $primaryKey';
            }
            echo "\n";?>
                         )
                     );
            }

            if (is_numeric($primaryKey) && !empty($fileObjects)) {

                foreach ($fileObjects as $key => $fso) {

                    $baseName = $fso->getBaseName();

                    if (!empty($baseName)) {

                        $fso->flush($primaryKey);
                    }
                }
            }

<?php if (count($this->getDependentTables()) > 0) :?>

            if ($recursive) {
<?php foreach ($this->getDependentTables() as $key) : ?>
                if ($model->get<?=$this->_getRelationName($key, 'dependent')?>(null, null, true) !== null) {
<?php if ($key['type'] !== 'many') : ?>
                    $model->get<?=$this->_getRelationName($key, 'dependent')?>()
<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] !== 'array') : ?>
                          ->set<?=$this->_getCapital($key['column_name'])?>($primaryKey)
<?php endif;?>
                          ->saveRecursive(false, $transactionTag);
<?php else: ?>
<?php
        $relatedModelVarName = lcfirst($this->_getClassName($key['foreign_tbl_name']));
?>
                    $<?=$relatedModelVarName?> = $model->get<?=$this->_getRelationName($key, 'dependent')?>();

                    if (!is_array($<?=$relatedModelVarName?>)) {

                        $<?=$relatedModelVarName?> = array($<?=$relatedModelVarName?>);
                    }

                    foreach ($<?=$relatedModelVarName?> as $value) {
                        $value<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] !== 'array') : ?>
->set<?=$this->_getCapital($key['column_name'])?>($primaryKey)
<?php elseif (is_array($key['column_name'])) :
    foreach ($key['column_name'] as $column) : ?>
->set<?=$this->_getCapital($column)?>($primaryKey['<?php echo $column ?>'])
<?php endforeach; ?>
<?php endif;?>
                              ->saveRecursive(false, $transactionTag);
                    }
<?php endif; ?>
                }

<?php endforeach; ?>
            }
<?php endif; ?>

            if ($success === true) {

                foreach ($model->getOrphans() as $itemToDelete) {

                    $itemToDelete->delete();
                }

                $model->resetOrphans();
            }

            if ($useTransaction && $success) {

                $this->getDbTable()->getAdapter()->commit();

            } elseif ($useTransaction) {

                $this->getDbTable()->getAdapter()->rollback();

                if ($this->_cache) {

                    $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($transactionTag));
                }
            }

        } catch (\Exception $e) {
<?php if (!empty($this->_loggerName)):?>
            $message = 'Exception encountered while attempting to save ' . get_class($this);
            if (!empty($primaryKey)) {
<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') : ?>
                $message .= ' id:';
<?php foreach ($this->_primaryKey[$this->getTablename()]['fields'] as $key) : ?>
                $message .= ' <?=$key['field']?> => ' . $primaryKey['<?=$key['field']?>'];
<?php endforeach; ?>
<?php else: ?>
                $message .= ' id: ' . $primaryKey;
<?php endif; ?>
            } else {
                $message .= ' with an empty primary key ';
            }

            $message .= ' Exception: ' . $e->getMessage();
            $this->_logger->log($message, \Zend_Log::ERR);
            $this->_logger->log($e->getTraceAsString(), \Zend_Log::DEBUG);

<?php endif; ?>
            if ($useTransaction) {
                $this->getDbTable()->getAdapter()->rollback();

                if ($this->_cache) {

                    if ($transactionTag) {

                        $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($transactionTag));

                    } else {

                        $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG);
                    }
                }
            }

            Throw $e;
        }

        if ($success && $this->_cache) {

            if ($useTransaction) {

                $this->_cache->save($model->toArray(), get_class($model) . "_" . $model->getPrimaryKey(), array($transactionTag));

            } else {

                $this->_cache->save($model->toArray(), get_class($model) . "_" . $model->getPrimaryKey());
            }
        }

        if ($success === true) {

            return $primaryKey;
        }

        return $success;
    }

    /**
     * Loads the model specific data into the model object
     *
     * @param \Zend_Db_Table_Row_Abstract|array $data The data as returned from a \Zend_Db query
     * @param <?=$namespace?>Model\<?=$this->_className?>|null $entry The object to load the data into, or null to have one created
     * @return <?=$namespace?>Model\<?=$this->_className?> The model with the data provided
     */
    public function loadModel($data, $entry = null)
    {
        if (!$entry) {
            $entry = new \<?=$namespace?>Model\<?=$this->_className?>();
        }

        if (is_array($data)) {
            $entry<?php
                $count = count($this->_columns[$this->getTableName()]);
                foreach ($this->_columns[$this->getTableName()] as $column):
                $count--;

                if (stristr($column['comment'], '[ml]')) {

                    continue;
                }

              ?>->set<?=$column['capital']?>($data['<?=$column['field']?>'])<?if ($count> 0) echo "\n                  ";
              endforeach; ?>;
        } else if ($data instanceof \Zend_Db_Table_Row_Abstract || $data instanceof \stdClass) {
            $entry<?php
                $count = count($this->_columns[$this->getTableName()]);
                foreach ($this->_columns[$this->getTableName()] as $column):
                $count--;

                if (stristr($column['comment'], '[ml]')) {

                    continue;
                }

              ?>->set<?=$column['capital']?>($data->{'<?=$column['field']?>'})<?if ($count> 0) echo "\n                  ";
              endforeach; ?>;

        } else if ($data instanceof \<?=$namespace?>Model\<?=$this->_className?>) {
            $entry<?php
                $count = count($this->_columns[$this->getTableName()]);
                foreach ($this->_columns[$this->getTableName()] as $column):
                $count--;

                if (stristr($column['comment'], '[ml]')) {

                    continue;
                }

              ?>->set<?=$column['capital']?>($data->get<?=$column['capital']?>())<?if ($count> 0) echo "\n                  ";
              endforeach; ?>;

        }

        $entry->resetChangeLog()->initChangeLog()->setMapper($this);

        return $entry;
    }
<?php $functions = $this->_includeMapper->getFunctions();
if (!empty($functions)) {
echo "\n$functions\n";
} ?>
}
