<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
$tableName = $this->getTableName();
$fields = Generator_Db::describeTable($tableName);
$enumFields = array();
$fsoFields = array();
foreach ($fields as $field) {
    if ($field->isEnum()) {
        $enumFields[] = $field;
        continue;
    }

    if ($field->isFso()) {
        $fsoFields[] = $field;
        continue;
    }
}
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
$fsoObjects = array();

foreach ($fsoFields as $field) {
    $fsoObject = str_replace('FileSize', '', $field->getName());

    if (empty($fsoObject)) {
        $fsoObject = $this->_className;
    }

    $fsoObjects[$fsoObject] = $field;
}

// Indicamos a cada campo que "hermanos" FSO existen
foreach ($fields as $field) {
    $field->setFSOSiblings(array_keys($fsoObjects));
}

foreach ($fsoObjects as $item => $field) :
?>
    /*
     * @var \Iron_Model_Fso
     */
    protected $_<?php echo lcfirst($item); ?>Fso;
<?php
endforeach;
?>

<?php
foreach ($enumFields as $field) :
?>
    protected $_<?php echo $field->getNormalizedName()?>AcceptedValues = array(
<?php
    foreach ($field->getAcceptedValues() as $acceptedValue) :
?>
        '<?php echo $acceptedValue?>',
<?php
    endforeach;
?>
    );
<?php
endforeach;
?>

<?php foreach ($fields as $column): ?>
    /**
<?php if ($column->hasComment()): ?>
     * <?=$column->getComment() . "\n"?>
<?php endif; ?>
     * Database var type <?=$column->getType() . "\n"?>
     *
     * @var <?=$column->getPhpType() . "\n"?>
     */
    protected $_<?=$column->getNormalizedName()?>;

<?php endforeach;?>

<?php
$foreignKeys = $this->getForeignKeysInfo();

foreach ($foreignKeys as $key): ?>
    /**
     * Parent relation <?=$key['key_name'] . "\n"?>
     *
     * @var \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
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
     * @var \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?><?=($key['type'] == 'one') ? '' : '[]'?><?="\n"?>
     */
    protected $_<?=$this->_getRelationName($key, 'dependent')?>;

<?php
endforeach;
$vars = $this->_includeModel->getVars();
if (!empty($vars)) {
    echo $vars . "\n\n";
}
?>
    protected $_columnsList = array(
<?php foreach ($fields as $column): ?>
        '<?=$column->getName()?>'=>'<?=$column->getNormalizedName()?>',
<?php endforeach; ?>
    );

    /**
     * Sets up column and relationship lists
     */
    public function __construct()
    {
        $this->setColumnsMeta(array(
<?php
    foreach ($fields as $column):
        if (!$column->hasComment()) {
            continue;
        }

        $meta = str_replace("[", "", $column->getComment());
        $meta = explode("]", $meta);
        array_pop($meta);
        $meta = "'" . implode("','", $meta) . "'";
?>
            '<?=$column->getName()?>'=> array(<?= $meta ?>),
<?php
    endforeach;
?>        ));

        $this->setMultiLangColumnsList(array(
<?php
    $mlFields = array();
    foreach ($fields as $column):
        if(!$column->isMultilang()) {
            continue;
        }

        $mlFields[] = $column->getName();
?>
            '<?=$column->getNormalizedName()?>'=>'<?=$column->getNormalizedName('upper')?>',
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
                    'table_name' => '<?=$key['foreign_tbl_name']?>',
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
                    'table_name' => '<?=$key['foreign_tbl_name']?>',
                ),
<?php endforeach;?>
        ));

<?php if (count($deleteCascade) > 0) : ?>
        $this->setOnDeleteCascadeRelationships(array(
            '<?php echo implode("',\n            '" , array_keys($deleteCascade)); ?>'
        ));
<?php endif ?>

<?php if (count($deleteSetNull) > 0) : ?>
        $this->setOnDeleteSetNullRelationships(array(
            '<?php echo implode("',\n            '" , array_keys($deleteSetNull)); ?>'
        ));
<?php endif ?>

<?php
    //FIXME: Quitar o poner esto aquí, pero ese "false &&" APESTA  ...
    if (false && count($updateCascade) > 0) :
?>
        $this->setOnUpdateCascadeRelationships(array(
            '<?php echo implode("',\n            '" , array_keys($updateCascade)); ?>'
        ));

<?php
    endif
?>

        $this->_defaultValues = array(
<?php
    foreach ($fields as $column):
        if ($column->isRequired() && !is_null($column->getDefaultValue())) :
?>
            '<?php echo $column->getNormalizedName()?>' => '<?php echo $column->getDefaultValue(); ?>',
<?php
        endif;
    endforeach;
?>
        );

        $this->_initFileObjects();
        parent::__construct();
    }

    /**
     * This method is called just after parent's constructor
     */
    public function init()
    {
    }
    /**************************************************************************
    ************************** File System Object (FSO)************************
    ***************************************************************************/

    protected function _initFileObjects()
    {
<?php
foreach ($fsoObjects as $fsoObject => $field):
?>
        $this->_<?php echo lcfirst($fsoObject); ?>Fso = new \Iron_Model_Fso($this, $this->get<?php echo ucfirst($fsoObject); ?>Specs());
<?php
    $modifiers = $field->getModifiers();
    if (!empty($modifiers)):
        foreach ($modifiers as $adapter => $values):
        $getter = "get" . ucfirst($adapter) . "Resolver";
?>
        $this->_<?php echo lcfirst($fsoObject); ?>Fso-><?php echo $getter; ?>()->setModifiers(array('<?php echo implode("' => true,'", $values); ?>' => true));
<?php
        endforeach;
    endif;
?>
<?php
endforeach;
?>

        return $this;
    }

    public function getFileObjects()
    {

<?php
    if ($fsoFields) :
?>
        return array('<?php echo implode("','", array_keys($fsoObjects)); ?>');
<?php
    else:
?>
        return array();
<?php
    endif;
?>
    }

<?php
 foreach ($fsoObjects as $item => $field) :

    $md5Column = false;
    foreach ($fields as $column) {
        if ($column->getNormalizedName() == $item .'Md5Sum') {

            $md5Column = true;
            break;
        }
    }
?>
    public function get<?php echo ucfirst($item); ?>Specs()
    {
        return array(
            'basePath' => '<?php echo lcfirst($item); ?>',
            'sizeName' => '<?php echo lcfirst($item); ?>FileSize',
            'mimeName' => '<?php echo lcfirst($item); ?>MimeType',
            'baseNameName' => '<?php echo lcfirst($item); ?>BaseName',
<?php
    if($md5Column === true) :
?>
            'md5SumName' => '<?php echo lcfirst($item); ?>Md5Sum',
<?php
    endif;
?>
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
        if ($autoload === true && $this->get<?php echo $item; ?>FileSize() > 0) {

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

    public function get<?=ucfirst($item)?>Url($profile)
    {

        $fsoConfig = \Zend_Registry::get('fsoConfig');
        $profileConf = $fsoConfig->$profile;

        if (is_null($profileConf)) {
            throw new \Exception('Profile invalid. not exist in fso.ini');
        }

        $route = array(
            'profile' => $profile,
            'routeMap' => $this->getId() . '-' . $this->get<?=ucfirst($item)?>BaseName()
        );
        $view = new \Zend_View();
        $fsoUrl = $view->serverUrl($view->url($route, 'fso'));

        return $fsoUrl;

    }

<?php
endforeach; //endforeach
echo "\n";
?>
    /**************************************************************************
    *********************************** /FSO ***********************************
    ***************************************************************************/
<?php
foreach ($fields as $column):

    $setterParams = '';
    $getterParams = '';
    $multilang = false;
    $trimMethod = '';
    $trimOpen = '';
    $trimClose = '';

    if ($column->isMultilang()) {
        $multilang = true;
        $setterParams = '$data, $language = \'\'';
        $getterParams = '$language = \'\'';
    }

    $trimMethods = array (
            "[trim]" => "trim",
            "[rtrim]" => "rtrim",
            "[ltrim]" => "ltrim"
    );
    if ($column->hasComment() && isset($trimMethods[$column->getComment()])) {
        $trimMethod = $trimMethods[$column->getComment()];
        $trimOpen = "(";
        $trimClose = ")";
    }
?>

    /**
     * Sets column <?=$column->getName() . $column->isAnyDateType()? "Stored in ISO 8601 format." : '' . "\n";?>
     *
<?php if ($column->isAnyDateType()): ?>
     * @param string|Zend_Date $date
<?php else: ?>
     * @param <?=$column->getPhpType()?> $data
<?php endif; ?>
     * @return \<?=$namespace?>Model\Raw\<?=$this->_className . "\n"?>
     */
    public function set<?=$column->getNormalizedName('upper')?>(<?=$multilang ? $setterParams : '$data'; ?>)
    {
<?php
    $applyCasting = true;

    if ($column->isAnyDateType()):
        $applyCasting = false;
?>

        if ($data == '0000-00-00 00:00:00') {
            $data = null;
        }

        if ($data === 'CURRENT_TIMESTAMP'<?php
        // Si un timestamp es obligatorio -no nullable- y se le settea NULL
        // MySQL setea now(); mejor lo forzamos nosotros a now con TZ.
        if ($column->isRequired()):
            echo  ' || is_null($data)';
        endif;
        ?>) {
            $data = \Zend_Date::now()->setTimezone('<?=$this->_defaultTimeZone;?>');
        }

        if ($data instanceof \Zend_Date) {

            $data = new \DateTime($data->toString('yyyy-MM-dd HH:mm:ss'), new \DateTimeZone($data->getTimezone()));

        } elseif (!is_null($data) && !$data instanceof \DateTime) {

            $data = new \DateTime($data, new \DateTimeZone('<?=$this->_defaultTimeZone;?>'));
        }

        if ($data instanceof \DateTime && $data->getTimezone()->getName() != '<?=$this->_defaultTimeZone;?>') {

            $data->setTimezone(new \DateTimeZone('<?=$this->_defaultTimeZone;?>'));
        }

<?php
    endif;
?>

<?php
    // es necesario indicarle a la columna que fsoObjects están disponibles en el modelo.
    // Si se trata de un campo auxiliar de fso, no se lanzará exception on NULL
    if ($column->throwExceptionOnNull($fsoObjects)) :
?>
        if (is_null($data)) {
            throw new \InvalidArgumentException(_('Required values cannot be null'));
        }

<?php
    endif;

    if ($multilang):
?>
        $language = $this->_getCurrentLanguage($language);

        $methodName = "set<?=$column->getNormalizedName('upper')?>". ucfirst(str_replace('_', '', $language));
        if (!method_exists($this, $methodName)) {

            // new \Exception('Unavailable language');
            $this->_<?=$column->getNormalizedName()?> = <?=$trimMethod.$trimOpen?>$data<?=$trimClose?>;
            return $this;
        }
        $this->$methodName($data);
<?php
    else:

        $casting = '';
        if ($applyCasting) {
            switch($column->getPhpType()) {

                case 'text':
                    $casting = '(string)';
                    break;

                case 'string':
                case 'int':
                case 'float':
                case 'boolean':

                    $casting = '(' . $column->getPhpType() . ')';
            }
        }
?>
        if ($this->_<?=$column->getNormalizedName()?> != $data) {
            $this->_logChange('<?=$column->getNormalizedName()?>');
        }

<?php
    if (!empty($casting)) :
?>
        if ($data instanceof \Zend_Db_Expr) {
            $this->_<?=$column->getNormalizedName()?> = $data;
        } else if (!is_null($data)) {
<?php
            if ($column->isEnum()) :
?>
            if (!in_array($data, $this->_<?=$column->getNormalizedName()?>AcceptedValues) && !empty($data)) {
                throw new \InvalidArgumentException(_('Invalid value for <?=$column->getNormalizedName()?>'));
            }
<?php
            endif;
?>
            $this->_<?=$column->getNormalizedName()?> = <?php echo $casting; ?> <?=$trimMethod.$trimOpen?>$data<?=$trimClose?>;
        } else {
            $this->_<?=$column->getNormalizedName()?> = $data;
        }
<?php
    else :
?>

        $this->_<?=$column->getNormalizedName()?> = <?=$trimMethod.$trimOpen?>$data<?=$trimClose?>;
<?php
    endif; // !empty($casting)
    endif;
?>
        return $this;
    }

    /**
     * Gets column <?=$column->getName() . "\n"?>
     *
<?php if ($column->isAnyDateType()): ?>
     * @param boolean $returnZendDate
     * @return Zend_Date|null|string Zend_Date representation of this datetime if enabled, or ISO 8601 string if not
<?php else: ?>
     * @return <?=$column->getPhpType() . "\n"?>
<?php endif; ?>
     */
    public function get<?=$column->getNormalizedName('upper')?>(<?php

        if ($column->isAnyDateType()) {
            echo '$returnZendDate = false';
        }
        if ($multilang) {
            echo $getterParams;
        }
    ?>)
    {
    <?php
        if ($column->isAnyDateType()):
    ?>

        if (is_null($this->_<?=$column->getNormalizedName()?>)) {

            return null;
        }

        if ($returnZendDate) {
            $zendDate = new \Zend_Date($this->_<?=$column->getNormalizedName()?>->getTimestamp(), \Zend_Date::TIMESTAMP);
            $zendDate->setTimezone('<?=$this->_defaultTimeZone;?>');
            return $zendDate;
        }


<?php if ($column->getType() =='date'): ?>
        return $this->_<?=$column->getNormalizedName()?>->format('Y-m-d');
<?php elseif ($column->getType() =='time') : ?>
        return $this->_<?=$column->getNormalizedName()?>->format('H:i:s');
<?php else: ?>
        return $this->_<?=$column->getNormalizedName()?>->format('Y-m-d H:i:s');
<?php endif; ?>

<?php elseif ($column->getPhpType() == 'boolean'): ?>

        return (int) $this->_<?=$column->getNormalizedName()?>;
<?php
    elseif ($multilang):
?>

        $language = $this->_getCurrentLanguage($language);

        $methodName = "get<?=$column->getNormalizedName('upper')?>". ucfirst(str_replace('_', '', $language));
        if (!method_exists($this, $methodName)) {

            // new \Exception('Unavailable language');
            return $this->_<?=$column->getNormalizedName()?>;
        }

        return $this->$methodName();

<?php
    else:

        if ($column->hasComment('[html]')):
?>

        $pathFixer = new \Iron_Filter_PathFixer;
        return $pathFixer->fix($this->_<?=$column->getNormalizedName()?>);
<?php
        else:
?>
        return $this->_<?=$column->getNormalizedName()?>;
<?php
        endif;
?>
<?php endif; ?>
    }
<?php endforeach; ?>

<?php foreach ($this->getForeignKeysInfo() as $key): ?>

    /**
     * Sets parent relation <?=$this->_getClassName($key['column_name']) . "\n"?>
     *
     * @param \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return \<?=$namespace?>Model\Raw\<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>(\<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
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

        if (!is_null($primaryKey)) {
            $this->set<?=$this->_getCapital($key['column_name'])?>($primaryKey);
        }
<?php endif; ?>

        $this->_setLoaded('<?=$this->_getCapital($key['key_name'])?>');
        return $this;
    }

    /**
     * Gets parent <?=$this->_getClassName($key['column_name']) . "\n"?>
     * TODO: Mejorar esto para los casos en que la relación no exista. Ahora mismo siempre se pediría el padre
     * @return \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>($where = null, $orderBy = null, $avoidLoading = false)
    {
        $fkName = '<?=$this->_getCapital($key['key_name'])?>';

        $usingDefaultArguments = is_null($where) && is_null($orderBy);
        if (!$usingDefaultArguments) {
            $this->setNotLoaded($fkName);
        }

        $dontSkipLoading = !($avoidLoading);
        $notLoadedYet = !($this->_isLoaded($fkName));

        if ($dontSkipLoading && $notLoadedYet) {
            $related = $this->getMapper()->loadRelated('parent', $fkName, $this, $where, $orderBy);
            $this->_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?> = array_shift($related);
            if ($usingDefaultArguments) {
                $this->_setLoaded($fkName);
            }
        }

        return $this->_<?=$this->_getRelationName($key, 'parent', $foreignKeys)?>;
    }
<?php endforeach; ?>
<?php foreach ($this->getDependentTables() as $key): ?>

<?php if ($key['type'] == 'one') :?>
    /**
     * Sets dependent relation <?=$key['key_name'] . "\n"?>
     *
     * @param \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return \<?=$namespace?>Model\Raw\<?=$this->_className . "\n"?>
     */
    public function set<?=$this->_getRelationName($key, 'dependent')?>(\<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?> = $data;
        $this->_setLoaded('<?=$this->_getCapital($key['key_name'])?>');
        return $this;
    }

    /**
     * Gets dependent <?=$key['key_name'] . "\n"?>
     *
     * @param string or array $where
     * @param string or array $orderBy
     * @param boolean $avoidLoading skip data loading if it is not already
     * @return \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'dependent')?>($where = null, $orderBy = null, $avoidLoading = false)
    {
        $fkName = '<?=$this->_getCapital($key['key_name'])?>';

        $usingDefaultArguments = is_null($where) && is_null($orderBy);
        if (!$usingDefaultArguments) {
            $this->setNotLoaded($fkName);
        }

        $dontSkipLoading = !($avoidLoading);
        $notLoadedYet = !($this->_isLoaded($fkName));

        if ($dontSkipLoading && $notLoadedYet) {
            $related = $this->getMapper()->loadRelated('dependent', $fkName, $this, $where, $orderBy);
            $this->_<?=$this->_getRelationName($key, 'dependent')?> = $related;
            $this->_setLoaded($fkName);
        }

        return $this->_<?=$this->_getRelationName($key, 'dependent')?>;
    }
<?php else: ?>
    /**
     * Sets dependent relations <?=$key['key_name'] . "\n"?>
     *
     * @param array $data An array of \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     * @return \<?=$namespace?>Model\Raw\<?=$this->_className . "\n"?>
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

                    $pk = $newItem->getPrimaryKey();
                    if (!empty($pk)) {
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
     * @param \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data
     * @return \<?=$namespace?>Model\Raw\<?=$this->_className . "\n"?>
     */
    public function add<?=$this->_getRelationName($key, 'dependent')?>(\<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name'])?> $data)
    {
        $this->_<?=$this->_getRelationName($key, 'dependent')?>[] = $data;
        $this->_setLoaded('<?=$this->_getCapital($key['key_name'])?>');
        return $this;
    }

    /**
     * Gets dependent <?=$key['key_name'] . "\n"?>
     *
     * @param string or array $where
     * @param string or array $orderBy
     * @param boolean $avoidLoading skip data loading if it is not already
     * @return array The array of \<?=$namespace?>Model\Raw\<?=$this->_getClassName($key['foreign_tbl_name']) . "\n"?>
     */
    public function get<?=$this->_getRelationName($key, 'dependent')?>($where = null, $orderBy = null, $avoidLoading = false)
    {
        $fkName = '<?=$this->_getCapital($key['key_name'])?>';

        $usingDefaultArguments = is_null($where) && is_null($orderBy);
        if (!$usingDefaultArguments) {
            $this->setNotLoaded($fkName);
        }

        $dontSkipLoading = !($avoidLoading);
        $notLoadedYet = !($this->_isLoaded($fkName));

        if ($dontSkipLoading && $notLoadedYet) {
            $related = $this->getMapper()->loadRelated('dependent', $fkName, $this, $where, $orderBy);
            $this->_<?=$this->_getRelationName($key, 'dependent')?> = $related;
            $this->_setLoaded($fkName);
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

                 new \Exception("Not a valid mapper class found");
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

    public function setFromArray($data)
    {
        return $this->getMapper()->loadModel($data, $this);
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
            $this->_logger->log('The value for <?=$key['capital']?> cannot be empty in deleteRowByPrimaryKey for ' . get_class($this), \Zend_Log::ERR);
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
            $this->_logger->log('The value for <?=$this->_primaryKey[$this->getTablename()]['capital']?> cannot be null in deleteRowByPrimaryKey for ' . get_class($this), \Zend_Log::ERR);
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
