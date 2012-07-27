<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model
 *
 * @package <?=$namespace?>Model\Raw
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */

/**
 * <?=$this->_classDesc[$this->getTableName()]."\n"?>
 *
 * @package <?=$namespace?>Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 */
 
namespace <?=$namespace?>Model\Raw;
class <?=$this->_className?> extends <?=$this->_includeModel->getParentClass() . "\n"?>
{
<?php
 $fsoFields = array();

 foreach ($this->_columns[$this->getTableName()] as $column):

    if (stristr($column['comment'], '[fso]')) {

        $fsoFields[] = $column;
    }

 endforeach;

$objects = array();
 if (count($fsoFields)) {

     foreach ($fsoFields as $field) {

         $object = str_replace('FileSize', '', $field['field']);

         if (empty($object)) {

             $object = $this->_className;
         }

         $objects[] = $object;
     }

     $objects = array_unique($objects);

     foreach ($objects as $item) {
?>
    /*
     * @var \KlearMatrix_Model_Fso
     */
    protected $_<?php echo lcfirst($item); ?>Fso;

<?php
     }
 }

?>
<?php foreach ($this->_columns[$this->getTableName()] as $column): ?>
    /**
<?php if (!empty($column['comment'])) : ?>
     * <?=$column['comment'] . "\n"?>
<?php endif; ?>
     * Database var type <?=$column['type'] . "\n"?>
     *
     * @var <?=$column['phptype'] . "\n"?>
     */
    protected $_<?=$column['normalized']?>;

<?php endforeach;?>

<?php
$foreignKeys = $this->getForeignKeysInfo();

foreach ($foreignKeys as $key): ?>
    /**
     * Parent relation <?=$key['key_name'] . "\n"?>
     *
     * @var \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    protected $_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>;

<?php endforeach;?>

<?php
foreach ($this->getDependentTables() as $key):

    $trigger = $this->getTrigger($key['key_name']);
?>
    /**
     * Dependent relation <?=$key['key_name'] . "\n"?>
     * Type: <?=($key['type'] == 'one') ? 'One-to-One' : 'One-to-Many'?> relationship
     *
     * @var \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?><?=($key['type'] == 'one') ? '' : '[]'?><?="\n"?>
     */
    protected $_<?=$this->_getRelationName($key, 'dependent')?>;

<?php endforeach;?>
<?php $vars = $this->_includeModel->getVars();
if (!empty($vars)) {
echo "$vars\n\n";
}
?>
    /**
     * Sets up column and relationship lists
     */
    public function __construct()
    {
        parent::init();
        $this->setColumnsList(array(
<?php
    foreach ($this->_columns[$this->getTableName()] as $column):

         $mlFields = $column['field'];
?>
            '<?=$column['field']?>'=>'<?=$column['normalized']?>',
<?php
    endforeach;
?>        ));

        $this->setMultiLangColumnsList(array(
<?php
    $mlFields = array();
    foreach ($this->_columns[$this->getTableName()] as $column):
        if(!stristr($column['comment'], '[ml]')) {
            continue;
        }

        $mlFields[] = $column['field'];
?>
            '<?=$column['normalized']?>'=>'<?=$column['capital']?>',
<?php endforeach;?>
        ));

<?php
    if (isset($this->_config->klear->languages)) {
        $languages = $this->_config->klear->languages->toArray();
        $languages = "'" . implode("', '", $languages) . "'";
    } else {
        $languages = null;
    }
?>
        $this->setAvailableLangs(array(<?php echo $languages?>));

        $this->setParentList(array(
<?php foreach ($this->getForeignKeysInfo() as $key): ?>
            '<?=$this->_getCapital($key['key_name'])?>'=> array(
                    'property' => '<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>',
                    'table_name' => '<?=$this->_getClassName($key['foreign_tbl_name'])?>',
                ),
<?php endforeach;?>
        ));

        $this->setDependentList(array(
<?php

        $deleteCascade = array();
        $deleteSetNull = array();
        $updateCascade = array();

        foreach ($this->getDependentTables() as $key):

            $tmp = $this->getTrigger($key['key_name'], 'DELETECASCADE');
            if (!empty($tmp)) {

                $deleteCascade[$key['key_name']] = $tmp;
            }

            $tmp = $this->getTrigger($key['key_name'], 'DELETENULL');
            if (!empty($tmp)) {

                $deleteSetNull[$key['key_name']] = $tmp;
            }

            $tmp = $this->getTrigger($key['key_name'], 'UPDATECASCADE');
            if (!empty($tmp)) {

                $updateCascade = $tmp;
            }

            ?>
            '<?=$this->_getCapital($key['key_name'])?>' => array(
                    'property' => '<?=$this->_getRelationName($key, 'dependent')?>',
                    'table_name' => '<?=$this->_getClassName($key['foreign_tbl_name'])?>',
                ),
<?php endforeach;?>
        ));

<?php if (count($deleteCascade) > 0) { ?>
        $this->setOnDeleteCascadeRelationships(array(
            '<?php echo implode("',\n            '" , array_keys($deleteCascade)); ?>'
        ));
<?php } //endif ?>

<?php if (count($deleteSetNull) > 0) { ?>
        $this->setOnDeleteSetNullRelationships(array(
            '<?php echo implode("',\n            '" , array_keys($deleteSetNull)); ?>'
        ));
<?php } //endif ?>
<?php if (FALSE AND count($updateCascade) > 0) { ?>
        $this->setOnUpdateCascadeRelationships(array(
            '<?php echo implode("',\n            '" , array_keys($updateCascade)); ?>'
        ));
    <?php } //endif ?>

        $this->_defaultValues = array(
<?php
    foreach ($this->_columns[$this->getTableName()] as $column):
        if ($column['nullable'] == false and !is_null($column['default']) and !in_array($column['default'], array('CURRENT_TIMESTAMP'))) {
?>
            '<?php echo $column['normalized'];?>' => '<?php echo $column['default']; ?>',
<?php
        } //endif
    endforeach;
?>
        );
        
        $this->_initFileObjects();

        parent::__construct();
    }
    
    /**************************************************************************
    ************************** File System Object (FSO)************************
    ***************************************************************************/

    protected function _initFileObjects()
    {
<?php
foreach ($objects as $fsoObject):
?>
        $this->_<?php echo lcfirst($fsoObject); ?>Fso = new \KlearMatrix_Model_Fso($this, $this->get<?php echo ucfirst($fsoObject); ?>Specs());
<?php
endforeach;
?>
        
        return $this;
    }
    
    public function getFileObjects()
    {
    
<?php
    if (count($fsoFields) > 0) :
?>
        return array('<?php echo implode("','", $objects); ?>');
        
<?php
    else:
?>

        return array();
<?php
    endif;
?>
    }
    
<?php
 if (count($fsoFields) > 0) {

?>
<?php
 foreach ($objects as $item) {
?>
    public function get<?php echo ucfirst($item); ?>Specs()
    {
        return array(
            'basePath' => '<?php echo lcfirst($item); ?>',
            'sizeName' => '<?php echo lcfirst($item); ?>FileSize',
            'mimeName' => '<?php echo lcfirst($item); ?>MimeType',
            'baseNameName' => '<?php echo lcfirst($item); ?>BaseName'
        );
    }

    public function put<?php echo ucfirst($item); ?>($filePath = '',$baseName = '')
    {
        $this->_<?php echo lcfirst($item); ?>Fso->put($filePath);

        if (!empty($baseName)) {

            $this->_<?php echo lcfirst($item); ?>Fso->setBaseName($baseName);
        }
    }

    public function fetch<?php echo ucfirst($item); ?>($autoload = true)
    {
        if ($autoload === true) {

            $this->_<?php echo lcfirst($item); ?>Fso->fetch();
        }

        return $this->_<?php echo lcfirst($item); ?>Fso;
    }

    public function remove<?php echo ucfirst($item); ?>()
    {
        $this->_<?php echo lcfirst($item); ?>Fso->remove();

        $this->_<?php echo lcfirst($item); ?>Fso = null;

        return true;
    }
    
<?php
 } //endforeach
?>
<?php
 } //endif
 echo "\n";
?>

    /**************************************************************************
    *********************************** /FSO ***********************************
    ***************************************************************************/
<?php foreach ($this->_columns[$this->getTableName()] as $column):

    $setterParams = '';
    $getterParams = '';
    $multilang = false;

    if (stristr($column['comment'], '[ml]')) {

        $multilang = true;
        $setterParams = '$data, $language = \'\'';
        $getterParams = '$language = \'\'';
    }

?>


    /**
     * Sets column <?=$column['field']?><?php if (in_array($column['type'], array('datetime', 'timestamp', 'date'))): ?>. Stored in ISO 8601 format.<?php endif; echo "\n";?>
     *
<?php if (in_array($column['type'], array('datetime', 'timestamp', 'date'))): ?>
     * @param string|Zend_Date $date
<?php else: ?>
     * @param <?=$column['phptype']?> $data
<?php endif; ?>
     * @return \<?=$namespace?>Model\<?=$this->_className . "\n"?>
     */
    public function set<?=$column['capital']?>(<?= $multilang ? $setterParams : '$data'; ?>)
    {
<?php if (in_array($column['type'], array('datetime', 'timestamp', 'date'))):
?>
        if ($data == '0000-00-00 00:00:00') {

            $data = null;
        }

        if (!is_null($data) && !$data instanceof Zend_Date) {

            $data = new \Zend_Date($data, \Zend_Date::ISO_8601, 'es_ES');
        }

        if (
            $this->_logChanges === true
            && (
                ($data instanceof Zend_Date && !$this->_<?=$column['normalized']?> instanceof Zend_Date)
                || (!$data instanceof Zend_Date && $this->_<?=$column['normalized']?> instanceof Zend_Date)
                || (
                    $data instanceof Zend_Date && $this->_<?=$column['normalized']?> instanceof Zend_Date
                    && $this->_<?=$column['normalized']?>->setTimezone(date_default_timezone_get())->toString()  !== $data->setTimezone(date_default_timezone_get())->toString()
                )
            )
        ) {

            $this->_logChange('<?=$column['normalized']?>');
        }

<?php
endif;
if ($multilang):
?>
        
        $language = $this->_getCurrentLanguage($language);

        $methodName = "set<?=$column['capital']?>". ucfirst(str_replace('_', '', $language));
        if (!method_exists($this, $methodName)) {

            //Throw new \Exception('Unavailable language');
            $this->_<?=$column['normalized']?> = $data;
            return $this;
        }
        $this->$methodName($data);
<?php
    else:
?>
        if ($this->_logChanges === true && $this->_<?=$column['normalized']?> != $data) {

            $this->_logChange('<?=$column['normalized']?>');
        }

        $this->_<?=$column['normalized']?> = $data;
<?php
    endif;
?>
        return $this;
    }

    /**
     * Gets column <?=$column['field'] . "\n"?>
     *
<?php if (in_array($column['type'], array('datetime', 'timestamp', 'date'))): ?>
     * @param boolean $returnZendDate
     * @return Zend_Date|null|string Zend_Date representation of this datetime if enabled, or ISO 8601 string if not
<?php else: ?>
     * @return <?=$column['phptype'] . "\n"?>
<?php endif; ?>
     */
    public function get<?=$column['capital']?>(<?php

        if (in_array($column['type'], array('datetime', 'timestamp', 'date'))): ?>$returnZendDate = false<?php endif;
        if ($multilang) {
            echo $getterParams;
        }
    ?>)
    {<?php if (in_array($column['type'], array('datetime', 'timestamp', 'date'))): ?>

        if (is_null($this->_<?= $column['normalized']; ?>)) {

            return null;
        }

        if ($returnZendDate) {

            return $this->_<?= $column['normalized']; ?>->setTimezone(date_default_timezone_get());
        }

<?php if ($column['type'] =='date'): ?>
        return $this->_<?=$column['normalized']?>->setTimezone(date_default_timezone_get())->toString('yyyy-MM-dd');
<?php else: ?>
        return $this->_<?=$column['normalized']?>->setTimezone(date_default_timezone_get())->toString('yyyy-MM-dd HH:mm:ss');
<?php endif; ?>

<?php elseif ($column['phptype'] == 'boolean'): ?>

        return (int) $this->_<?=$column['normalized']?>;
<?php
elseif ($multilang):
?>

        $language = $this->_getCurrentLanguage($language);

        $methodName = "get<?=$column['capital']?>". ucfirst(str_replace('_', '', $language));
        if (!method_exists($this, $methodName)) {

            //Throw new \Exception('Unavailable language');
            return $this->_<?=$column['normalized']?>;
        }

        return $this->$methodName();
<?php
    else:
?>

        return $this->_<?=$column['normalized']?>;
<?php endif; ?>
    }
<?php endforeach; ?>
<?php foreach ($this->getForeignKeysInfo() as $key): ?>

    /**
     * Sets parent relation <?=$this->_getClassName($key['column_name']) . "\n"?>
     *
     * @param \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return \<?=$namespace?>Model\<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>(\<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?> = $data;

        $primaryKey = $data->getPrimaryKey();
<?php if (is_array($key['foreign_tbl_column_name']) && is_array($key['column_name'])) : ?>
<?php while ($column = next($key['foreign_tbl_column_name'])) :
        $foreign_column = next($key['column_name']); ?>
        $this->set<?=$this->_getCapital($column)?>($primaryKey['<?php echo $foreign_column ?>']);
<?php endwhile;
else : ?>
        if (is_array($primaryKey)) {
            $primaryKey = $primaryKey['<?=$key['foreign_tbl_column_name']?>'];
        }

        $this->set<?=$this->_getCapital($key['column_name'])?>($primaryKey);
<?php endif; ?>

        return $this;
    }

    /**
     * Gets parent <?=$this->_getClassName($key['column_name']) . "\n"?>
     * TODO: Mejorar esto para los casos en que la relación no exista. Ahora mismo siempre se pediría el padre
     * @return \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>($where = null, $orderBy = null)
    {
        if ($this->_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?> === null) {
            $related = $this->getMapper()->loadRelated('<?=$this->_getCapital($key['key_name'])?>', $this, $where, $orderBy);
            $this->_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?> = array_shift($related);
        }

        return $this->_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>;
    }
<?php endforeach; ?>
<?php foreach ($this->getDependentTables() as $key): ?>

<?php if ($key['type'] == 'one') :?>
    /**
     * Sets dependent relation <?=$key['key_name'] . "\n"?>
     *
     * @param \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return \<?=$namespace?>Model\<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'dependent')?>(\<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?> = $data;
        return $this;
    }

    /**
     * Gets dependent <?=$key['key_name'] . "\n"?>
     *
     * @param boolean $load Load the object if it is not already
     * @return \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'dependent')?>($where = null, $orderBy = null)
    {
        if ($this->_<?=$this->_getRelationName($key, 'dependent')?> === null) {
            $related = $this->getMapper()->loadRelated('<?=$this->_getCapital($key['key_name'])?>', $this, $where, $orderBy);
            $this->_<?=$this->_getRelationName($key, 'dependent')?> = $related;
        }

        return $this->_<?=$this->_getRelationName($key, 'dependent')?>;
    }
<?php else: ?>
    /**
     * Sets dependent relations <?=$key['key_name'] . "\n"?>
     *
     * @param array $data An array of \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     * @return \<?=$namespace?>Model\<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'dependent')?>(array $data, $deleteOrphans = false)
    {
        if ($deleteOrphans === true) {

            if ($this->_<?=$this->_getRelationName($key, 'dependent')?> === null) {

                $this->get<?=$this->_getRelationName($key, 'dependent')?>();
            }

            $oldRelations = $this->_<?=$this->_getRelationName($key, 'dependent')?>;

            if (is_array($oldRelations)) {

                $dataPKs = array();

                foreach ($data as $newItem) {

                    if (is_numeric($pk = $newItem->getPrimaryKey())) {

                        $dataPKs[] = $pk;
                    }
                }

                foreach ($oldRelations as $oldItem) {

                    if (!in_array($oldItem->getPrimaryKey(), $dataPKs)) {

                        $this->_orphans[] = $oldItem;
                    }
                }
            }
        }

        $this->_<?=$this->_getRelationName($key, 'dependent')?> = array();

        foreach ($data as $object) {
            $this->add<?=$this->_getRelationName($key, 'dependent')?>($object);
        }

        return $this;
    }

    /**
     * Sets dependent relations <?=$key['key_name'] . "\n"?>
     *
     * @param \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return \<?=$namespace?>Model\<?=$this->_className . "\n"?>
     */
    public function add<?=$this->_getRelationName($key, 'dependent')?>(\<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?>[] = $data;
        return $this;
    }

    /**
     * Gets dependent <?=$key['key_name'] . "\n"?>
     *
     * @param boolean $load Load the object if it is not already
     * @return array The array of \<?=$namespace?>Model\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'dependent')?>($where = null, $orderBy = null)
    {
        if ($this->_<?=$this->_getRelationName($key, 'dependent')?> === null) {
            $related = $this->getMapper()->loadRelated('<?=$this->_getCapital($key['key_name'])?>', $this, $where, $orderBy);
            $this->_<?=$this->_getRelationName($key, 'dependent')?> = $related;
        }

        return $this->_<?=$this->_getRelationName($key, 'dependent')?>;
    }
<?php endif; ?>
<?php endforeach; ?>

    /**
     * Returns the mapper class for this model
     *
     * @return <?=$namespace?>Mapper\Sql\<?=$this->_className . "\n"?>
     */
    public function getMapper()
    {
        if ($this->_mapper === null) {

            \Zend_Loader_Autoloader::getInstance()->suppressNotFoundWarnings(true);

            if (class_exists('\<?=$namespace?>Mapper\Sql\<?=$this->_className?>')) {

                $this->setMapper(new \<?=$namespace?>Mapper\Sql\<?=$this->_className?>);

            } else {

                Throw new \Exception("Not a valid mapper class found");
            }

            \Zend_Loader_Autoloader::getInstance()->suppressNotFoundWarnings(false);
        }

        return $this->_mapper;
    }

    /**
     * Returns the validator class for this model
     *
     * @return null | \<?=$namespace?>Model\Validator\<?=$this->_className . "\n"; ?>
     */
    public function getValidator()
    {
        if ($this->_validator === null) {

            if (class_exists('\<?=$namespace?>\Validator\<?=$this->_className?>')) {

                $this->setValidator(new \<?=$namespace?>Validator\<?=$this->_className?>);
            }
        }

        return $this->_validator;
    }

    /**
     * Deletes current row by deleting the row that matches the primary key
     *
     * @see \Mapper\Sql\<?=$this->_className?>::delete
     * @return int|boolean Number of rows deleted or boolean if doing soft delete
     */
    public function deleteRowByPrimaryKey()
    {
<?php if ($this->_primaryKey[$this->getTablename()]['phptype'] == 'array') { ?>
        $primaryKey = array();
<?php foreach ($this->_primaryKey[$this->getTablename()]['fields'] as $key) { ?>
        if (!$this->get<?php echo $key['capital']; ?>()) {
<?php if (!empty($this->_loggerName)):?>
            $this->_logger->log('The value for <?=$key['capital']?> cannot be empty in deleteRowByPrimaryKey for ' . get_class($this), \Zend_Log::ERR);

<?php endif; ?>
            throw new \Exception('Primary Key <?php echo $key['capital']; ?> does not contain a value');
        } else {
            $primaryKey['<?php echo $key['field']?>'] = $this->get<?php echo $key['capital']?>();
        }

<?php } ?>
        return $this->getMapper()->getDbTable()->delete('<?php
        $fields = count($this->_primaryKey[$this->getTablename()]['fields']);
                $i = 0;
                foreach ($this->_primaryKey[$this->getTablename()]['fields'] as $key) {
                    echo $key['field'] . ' = \'
                    . $this->getMapper()->getDbTable()->getAdapter()->quote($primaryKey[\'' . $key['field'] . '\'])';
                    $i++;
                    if ($i != $fields) {
                        echo "
                    . ' AND ";
                    }
                }
        ?>);
<?php } else { ?>
        if ($this->get<?=$this->_primaryKey[$this->getTablename()]['capital']?>() === null) {
<?php if (!empty($this->_loggerName)):?>
            $this->_logger->log('The value for <?=$this->_primaryKey[$this->getTablename()]['capital']?> cannot be null in deleteRowByPrimaryKey for ' . get_class($this), \Zend_Log::ERR);

<?php endif; ?>
            throw new \Exception('Primary Key does not contain a value');
        }

        return $this->getMapper()->getDbTable()->delete(
            '<?=$this->_primaryKey[$this->getTablename()]['field']?> = ' .
             $this->getMapper()->getDbTable()->getAdapter()->quote($this->get<?=$this->_primaryKey[$this->getTablename()]['capital']?>())
        );
<?php } ?>
    }
<?php $functions = $this->_includeModel->getFunctions();
if (!empty($functions)) {
echo "\n$functions\n";
} ?>
}
