<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
$tableName = $this->getTableName();
$fields = Generator_Db::describeTable($tableName);
$primaryKey = $fields->getPrimaryKey();
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

<?php
if ($this->_urlIdentifiers) :
?>

    protected $_urlIdentifiers = array(
<?php
    foreach ($this->_urlIdentifiers as $key => $value) :
?>
        '<?php echo $key?>' => '<?php echo $value?>',
<?php
    endforeach;
?>
    );
<?php
else:
?>

    protected $_urlIdentifiers = array();
<?php
endif;
?>

<?php
$vars = $this->_includeMapper->getVars();
if (!empty($vars)) {
    echo "\n$vars\n";
}
?>
    /**
     * Returns an array, keys are the field names.
     *
     * @param <?=$namespace?>Model\Raw\<?=$this->_className?> $model
     * @return array
     */
    public function toArray($model, $fields = array())
    {

        if (!$model instanceof \<?=$namespace?>Model\Raw\<?=$this->_className?>) {
            if (is_object($model)) {
                $message = get_class($model) . " is not a \<?=$namespace?>Model\Raw\<?=$this->_className?> object in toArray for " . get_class($this);
            } else {
                $message = "$model is not a \\<?=$namespace?>Model\\<?=$this->_className?> object in toArray for " . get_class($this);
            }

            $this->_logger->log($message, \Zend_Log::ERR);
            throw new \Exception('Unable to create array: invalid model passed to mapper', 2000);
        }

        if (empty($fields)) {
            $result = array(<?php
echo "\n";
foreach ($fields as $column):
    if (!$column->isMultilang()) :
        if ($column->getComment() !== 'password') :
?>
                '<?=$column->getName()?>' => $model->get<?=$column->getNormalizedName('upper')?>(),
<?php
endif;
    endif;
endforeach;
?>
            );
        } else {
            $result = array();
            foreach ($fields as $fieldData) {
                $trimField = trim($fieldData);
                if (!empty($trimField)) {
                    if (strpos($trimField, ":") !== false) {
                        list($field,$params) = explode(":", $trimField, 2);
                    } else {
                        $field = $trimField;
                        $params = null;
                    }
                    $get = 'get' . ucfirst($field);
                    $value = $model->$get($params);

                    if (is_array($value) || is_object($value)) {
                        if (is_array($value) || $value instanceof Traversable) {
                            foreach ($value as $key => $item) {
                                if ($item instanceof \Itourbasque\Model\Raw\ModelAbstract) {
                                    $value[$key] = $item->toArray(); 
                                }
                            }
                        } else if ($value instanceof \Itourbasque\Model\Raw\ModelAbstract) {
                            $value = $value->toArray();
                        }
                    }
                    $result[lcfirst($field)] = $value;
                }
            }
        }

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
     * @param <?=$namespace?>Model\Raw\<?=$this->_className?> $model The model to <?=$fields->hasSoftDelete()? 'mark as deleted' : 'delete'?>
<?php
if (!$fields->hasSoftDelete()):
?>

     * @see <?=$namespace?>Mapper\DbTable\TableAbstract::delete()
<?php
endif;
?>
     * @return int
     */
    public function delete(\<?=$namespace?>Model\Raw\ModelAbstract $model)
    {
        if (!$model instanceof \<?=$namespace?>Model\Raw\<?=$this->_className?>) {
            if (is_object($model)) {
                $message = get_class($model) . " is not a \\<?=$namespace?>\Model\\<?=$this->_className?> object in delete for " . get_class($this);
            } else {
                $message = "$model is not a \\<?=$namespace?>\Model\\<?=$this->_className?> object in delete for " . get_class($this);
            }

            $this->_logger->log($message, \Zend_Log::ERR);
            throw new \Exception('Unable to delete: invalid model passed to mapper', 2000);
        }

        $useTransaction = true;

        $dbTable = $this->getDbTable();
        $dbAdapter = $dbTable->getAdapter();

        try {

            $dbAdapter->beginTransaction();

        } catch (\Exception $e) {

            //Transaction already started
            $useTransaction = false;
        }

        try {

            //onDeleteCascades emulation
            if ($this->_simulateReferencialActions && count($deleteCascade = $model->getOnDeleteCascadeRelationships()) > 0) {

                $depList = $model->getDependentList();

                foreach ($deleteCascade as $fk) {

                    $capitalizedFk = '';
                    foreach (explode("_", $fk) as $part) {

                        $capitalizedFk .= ucfirst($part);
                    }

                    if (!isset($depList[$capitalizedFk])) {

                        continue;

                    } else {

                        $relDbAdapName = '<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $depList[$capitalizedFk]["table_name"];
                        $depMapperName = '<?=$namespace?>\Mapper\\Sql\\' . $depList[$capitalizedFk]["table_name"];
                        $depModelName = '<?=$namespace?>\Model\\' . $depList[$capitalizedFk]["table_name"];

                        if ( class_exists($relDbAdapName) && class_exists($depModelName) ) {

                            $relDbAdapter = new $relDbAdapName;
                            $references = $relDbAdapter->getReference('<?=$namespace?>\Mapper\\Sql\\DbTable\\<?=$this->_className?>', $capitalizedFk);

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

                    $capitalizedFk = '';
                    foreach (explode("_", $fk) as $part) {

                        $capitalizedFk .= ucfirst($part);
                    }

                    if (!isset($depList[$capitalizedFk])) {

                        continue;

                    } else {

                        $relDbAdapName = '<?=$namespace?>\Mapper\\Sql\\DbTable\\' . $depList[$capitalizedFk]["table_name"];
                        $depMapperName = '<?=$namespace?>\Mapper\\Sql\\' . $depList[$capitalizedFk]["table_name"];
                        $depModelName = '<?=$namespace?>\Model\\' . $depList[$capitalizedFk]["table_name"];

                        if ( class_exists($relDbAdapName) && class_exists($depModelName) ) {

                            $relDbAdapter = new $relDbAdapName;
                            $references = $relDbAdapter->getReference('<?=$namespace?>\Mapper\\Sql\\DbTable\\<?=$this->_className?>', $capitalizedFk);

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

<?php
if ($fields->hasSoftDelete()):
    $column = $fields->getSoftDeleteField();
?>
            $model->set<?=$column->getNormalizedName('upper')?>(true);
            $result = $model->save();
<?php
else: //$fields->hasSoftDelete()
    if (is_array($primaryKey)):
?>
            $where = array();
<?php
        foreach ($primaryKey as $field) :
?>

            $pk_val = $model->get<?=$field->getNormalizedName('upper')?>();
            if (is_null($pk_val)) {

                $this->_logger->log('The value for <?=$field->getNormalizedName('upper')?> cannot be null in delete for ' . get_class($this), \Zend_Log::ERR);
                throw new \Exception('The value for <?=$field->getNormalizedName('upper')?> cannot be null', 2000);
            } else {
                $where[] = $dbAdapter->quoteInto($dbAdapter->quoteIdentifier('<?=$field->getName()?> = ?'), $pk_val);
            }
<?php
        endforeach;
    else:
?>
            $where = $dbAdapter->quoteInto($dbAdapter->quoteIdentifier('<?=$primaryKey->getName()?>') . ' = ?', $model->get<?=$primaryKey->getNormalizedName('upper')?>());
<?php
    endif; //is_array($primaryKey)
?>
            $result = $dbTable->delete($where);

            if ($this->_cache) {

                $this->_cache->remove(get_class($model) . "_" . $model->getPrimarykey());
            }

            $fileObjects = array();
            $availableObjects = $model->getFileObjects();

            foreach ($availableObjects as $fso) {

                $removeMethod = 'remove' . $fso;
                $model->$removeMethod();
            }

<?php
endif;//$fields->hasSoftDelete()
?>

            if ($useTransaction) {
                $dbAdapter->commit();
            }
        } catch (\Exception $exception) {

            $message = 'Exception encountered while attempting to delete ' . get_class($this);
            if (!empty($where)) {
                $message .= ' Where: ';
<?php
    if (is_array($primaryKey)) :
?>
                foreach ($where as $where_clause) {
                    $message .= $where_clause;
                }
<?php
    else:
?>
                $message .= $where;
<?php
    endif;
?>
            } else {
                $message .= ' with an empty where';
            }

            $message .= ' Exception: ' . $exception->getMessage();
            $this->_logger->log($message, \Zend_Log::ERR);
            $this->_logger->log($exception->getTraceAsString(), \Zend_Log::DEBUG);

            if ($useTransaction) {

                $dbAdapter->rollback();
            }

            throw $exception;
        }

<?php if ($etagsExist) { if ($this->_className !== 'EtagVersions') { ?>        $this->_etagChange();<?php } }?>

        return $result;

    }

    /**
     * Saves current row
     * @return integer primary key for autoincrement fields if the save action was successful
     */
    public function save(\<?=$namespace?>Model\Raw\<?=$this->_className?> $model, $forceInsert = false)
    {
        return $this->_save($model, false, false, null, $forceInsert);
    }

    /**
     * Saves current and all dependent rows
     *
     * @param \<?=$namespace?>Model\Raw\<?=$this->_className?> $model
     * @param boolean $useTransaction Flag to indicate if save should be done inside a database transaction
     * @return integer primary key for autoincrement fields if the save action was successful
     */
    public function saveRecursive(\<?=$namespace?>Model\Raw\<?=$this->_className?> $model, $useTransaction = true,
            $transactionTag = null, $forceInsert = false)
    {
        return $this->_save($model, true, $useTransaction, $transactionTag, $forceInsert);
    }

    protected function _save(\<?=$namespace?>Model\Raw\<?=$this->_className?> $model,
        $recursive = false, $useTransaction = true, $transactionTag = null, $forceInsert = false
    )
    {
        $this->_setCleanUrlIdentifiers($model);

        $fileObjects = array();

        $availableObjects = $model->getFileObjects();

        foreach ($availableObjects as $item) {

            $objectMethod = 'fetch' . $item;
            $fso = $model->$objectMethod(false);
            $specMethod = 'get' . $item . 'Specs';
            $fileSpects = $model->$specMethod();

            $fileSizeSetter = 'set' . $fileSpects['sizeName'];
            $baseNameSetter = 'set' . $fileSpects['baseNameName'];
            $mimeTypeSetter = 'set' . $fileSpects['mimeName'];

            if (!is_null($fso) && $fso->mustFlush()) {

                $fileObjects[$item] = $fso;

                $model->$fileSizeSetter($fso->getSize())
                      ->$baseNameSetter($fso->getBaseName())
                      ->$mimeTypeSetter($fso->getMimeType());
            }

            if (is_null($fso)) {
                $model->$fileSizeSetter(null)
                ->$baseNameSetter(null)
                ->$mimeTypeSetter(null);
            }
        }

        $data = $model->sanitize()->toArray();

<?php
if (is_array($primaryKey)):
?>
        $primaryKey = array();
<?php
    foreach ($primaryKey as $field):
?>

        $pk_val = $model->get<?=$field->getNormalizedName('upper')?>();
        if (is_null($pk_val)) {
            $this->_logger->log('The value for <?=$field->getNormalizedName('upper')?> cannot be null in save for ' . get_class($this), \Zend_Log::ERR);
            throw new \Exception("The value for <?=$field->getNormalizedName('upper')?> cannot be null", 2000);
        } else {
            $primaryKey['<?=$field->getName()?>'] = $pk_val;
        }
<?php
    endforeach;
?>

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
<?php
else : //is_array($primaryKey)
?>
        $primaryKey = $model->get<?=$primaryKey->getNormalizedName('upper')?>();
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

<?php
    if (!$primaryKey->isRelationship()):
?>
        if (!$forceInsert) {
            unset($data['<?=$primaryKey->getName()?>']);
        }

        try {
            if (is_null($primaryKey) || empty($primaryKey) || $forceInsert) {
                if (is_null($primaryKey) || empty($primaryKey)) {
<?php if ($primaryKey->getComment() === '[uuid]') { ?>
                    $data['<?=$primaryKey->getName()?>'] = new \Zend_Db_Expr("uuid()");
<?php } ?>
<?php if ($primaryKey->getComment() === '[uuid:php]') { ?>
                    $uuid = new \Iron\Utils\UUID();
                    $model->set<?=$primaryKey->getNormalizedName('upper')?>($uuid->generate());
                    $data['<?=$primaryKey->getName()?>'] = $model->get<?=$primaryKey->getNormalizedName('upper')?>();
<?php } ?>
                }
<?php
    else:
?>
        $exists = $this->find($primaryKey);

        try {
            if (is_null($exists)) {
<?php
    endif;
?>
                $primaryKey = $this->getDbTable()->insert($data);

                if ($primaryKey) {
                    $model->set<?=$primaryKey->getNormalizedName('upper')?>($primaryKey);
                } else {
                    throw new \Exception("Insert sentence did not return a valid primary key", 9000);
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
<?php
endif; //is_array($primaryKey)
?>
            } else {
                $this->getDbTable()
                     ->update(
                         $data,
                         array(<?php
if (is_array($primaryKey)):
    foreach ($primaryKey as $field) :
?>

                             '<?=$field->getName()?> = ?' => $primaryKey['<?=$field->getName()?>'],
<?php
    endforeach;
else:
?>

                             $this->getDbTable()->getAdapter()->quoteIdentifier('<?=$primaryKey->getName()?>') . ' = ?' => $primaryKey
<?php
endif;
?>
                         )
                     );
            }

            if (!empty($primaryKey) && !empty($fileObjects)) {

                foreach ($fileObjects as $key => $fso) {

                    $baseName = $fso->getBaseName();
                    if (!empty($baseName)) {
                        $fso->flush($primaryKey);
                    }
                }
            }

<?php
if (count($this->getDependentTables()) > 0):
?>

            if ($recursive) {
<?php
    foreach ($this->getDependentTables() as $key):
?>
                if ($model->get<?=$this->_getRelationName($key, 'dependent')?>(null, null, true) !== null) {
<?php
        if ($key['type'] !== 'many'):
?>
                    $model->get<?=$this->_getRelationName($key, 'dependent')?>()
<?php
            if (!is_array($primaryKey)) :
?>
                          ->set<?=$this->_getCapital($key['column_name'])?>($primaryKey)
<?php
            endif;
?>
                          ->saveRecursive(false, $transactionTag);
<?php
        else:
            $relatedModelVarName = lcfirst($this->_getClassName($key['foreign_tbl_name']));
?>
                    $<?=$relatedModelVarName?> = $model->get<?=$this->_getRelationName($key, 'dependent')?>();

                    if (!is_array($<?=$relatedModelVarName?>)) {

                        $<?=$relatedModelVarName?> = array($<?=$relatedModelVarName?>);
                    }

                    foreach ($<?=$relatedModelVarName?> as $value) {
                        $value<?php
            if ($this->_primaryKey[$this->getTablename()]['phptype'] !== 'array') :
?>
->set<?=$this->_getCapital($key['column_name'])?>($primaryKey)
<?php

            elseif (is_array($key['column_name'])):
                foreach ($key['column_name'] as $column) :
?>
->set<?=$this->_getCapital($column)?>($primaryKey['<?php echo $column ?>'])
<?php
                endforeach;
            endif;
?>
                              ->saveRecursive(false, $transactionTag);
                    }
<?php
        endif;
?>
                }

<?php
    endforeach;
?>
            }
<?php
endif;
?>

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
            $message = 'Exception encountered while attempting to save ' . get_class($this);
            if (!empty($primaryKey)) {
<?php
    if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') :
?>
                $message .= ' id:';
<?php
        foreach ($this->_primaryKey[$this->getTablename()]['fields'] as $key):
?>
                $message .= ' <?=$key['field']?> => ' . $primaryKey['<?=$key['field']?>'];
<?php
        endforeach;
?>
<?php
    else:
?>
                $message .= ' id: ' . $primaryKey;
<?php
    endif;
?>
            } else {
                $message .= ' with an empty primary key ';
            }

            $message .= ' Exception: ' . $e->getMessage();
            $this->_logger->log($message, \Zend_Log::ERR);
            $this->_logger->log($e->getTraceAsString(), \Zend_Log::DEBUG);

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

            throw $e;
        }

        if ($success && $this->_cache) {

            if ($useTransaction) {

                $this->_cache->save($model->toArray(), get_class($model) . "_" . $model->getPrimaryKey(), array($transactionTag));

            } else {

                $this->_cache->save($model->toArray(), get_class($model) . "_" . $model->getPrimaryKey());
            }
        }

<?php if ($etagsExist) { if ($this->_className !== 'EtagVersions') { ?>
        if ($model->mustUpdateEtag()) {
            $this->_etagChange();
        }
<?php } }?>

        if ($success === true) {
            return $primaryKey;
        }

        return $success;
    }

    /**
     * Loads the model specific data into the model object
     *
     * @param \Zend_Db_Table_Row_Abstract|array $data The data as returned from a \Zend_Db query
     * @param <?=$namespace?>Model\Raw\<?=$this->_className?>|null $entry The object to load the data into, or null to have one created
     * @return <?=$namespace?>Model\Raw\<?=$this->_className?> The model with the data provided
     */
    public function loadModel($data, $entry = null)
    {
        if (!$entry) {
            $entry = new \<?=$namespace?>Model\<?=$this->_className?>();
        }

        // We don't need to log changes as we will reset them later...
        $entry->stopChangeLog();

        if (is_array($data)) {
            $entry<?php
                $count = count($this->_columns[$this->getTableName()]);
                foreach ($this->_columns[$this->getTableName()] as $column):
                $count--;

                if (stristr($column['comment'], '[ml]')) {

                    continue;
                }

              ?>->set<?=$column['capital']?>($data['<?=$column['field']?>'])<?php if ($count> 0) echo "\n                  ";
              endforeach; ?>;
        } else if ($data instanceof \Zend_Db_Table_Row_Abstract || $data instanceof \stdClass) {
            $entry<?php
                $count = count($this->_columns[$this->getTableName()]);
                foreach ($this->_columns[$this->getTableName()] as $column):
                $count--;

                if (stristr($column['comment'], '[ml]')) {

                    continue;
                }

              ?>->set<?=$column['capital']?>($data->{'<?=$column['field']?>'})<?php if ($count> 0) echo "\n                  ";
              endforeach; ?>;

        } else if ($data instanceof \<?=$namespace?>Model\Raw\<?=$this->_className?>) {
            $entry<?php
                $count = count($this->_columns[$this->getTableName()]);
                foreach ($this->_columns[$this->getTableName()] as $column):
                $count--;

                if (stristr($column['comment'], '[ml]')) {

                    continue;
                }

              ?>->set<?=$column['capital']?>($data->get<?=$column['capital']?>())<?php if ($count> 0) echo "\n                  ";
              endforeach; ?>;

        }

        $entry->resetChangeLog()->initChangeLog()->setMapper($this);

        return $entry;
    }
<?php
if ($etagsExist) {
    ?>

    protected function _etagChange()
    {

        $date = new \Zend_Date();
        $date->setTimezone('UTC');
        $nowUTC = $date->toString('yyyy-MM-dd HH:mm:ss');

        $etags = new \<?=$namespace?>Mapper\Sql\EtagVersions();
        $etag = $etags->findOneByField('table', '<?=$this->_className?>');

        if (empty($etag)) {
            $etag = new \<?=$namespace?>Model\EtagVersions();
            $etag->setTable('<?=$this->_className?>');
        }

        $random = substr(
            str_shuffle(
                str_repeat(
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
                    5
                )
            ), 0, 5
        );

        $etag->setEtag(md5($nowUTC . $random));
        $etag->setLastChange($nowUTC);
        $etag->save();

    }

<?php
}// endif $etagsExist

$functions = $this->_includeMapper->getFunctions();
if (!empty($functions)) {
    echo "\n$functions\n";
}
?>
}