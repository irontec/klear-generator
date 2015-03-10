<?php
class Generator_Yaml_ModelConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_table;
    protected $_klearConfig;
    protected $_db;

    public function __construct($table, $namespace, $klearConfig, $enabledLanguages = array())
    {

        $this->_enabledLanguages = $enabledLanguages;

        $this->_loadTranslator();

        $this->_table = $table;
        $this->_namespace = $namespace;
        $this->_klearConfig = $klearConfig;
        $this->_db = Zend_Db_Table::getDefaultAdapter();

        $data['class'] = $this->_getModelName();
        $data['fields'] = array();

        $fields = $this->_getFields($table);

        $ignoredFieldEndings = array(
            'MimeType',
            'BaseName'
        );

        $firstField = true;
        foreach ($fields as $field) {
            $fieldName = $this->_getFieldName($field);

            $ignore = false;
            foreach ($ignoredFieldEndings as $ignoreEnding) {
                if (preg_match('/' . $ignoreEnding . '$/', $fieldName)) {
                    $ignore = true;
                }
            }

            if (!$ignore) {
                $data['fields'][$fieldName] = $this->_getFieldConf($field);

                // First field used as default field
                if ($firstField) {

                    $data['fields'][$fieldName]['default'] = 'true';
                    $firstField = false;
                }
            }
        }
        $this->_data['production'] = $data;
    }

    /**
     * Returns fields to be created. Remove multilanguage and primary key fields
     * @param string $table tableName
     * @return array with field descriptions
     */
    protected function _getFields($table)
    {
        $fields = Generator_Db::describeTable($table);

        $tmpFields = $fields;
        foreach ($tmpFields as $field) {

            $fieldName = $field->getName();

            if ($field->isPrimaryKey() || $field->mustBeIgnored()) {
                $fields->remove($fieldName);
            }

            if (isset($this->_klearConfig->klear->languages)) {
                if ($field->isMultilang()) {

                    foreach ($this->_klearConfig->klear->languages as $language) {
                        $fields->remove($fieldName . '_' . $language);
                    }
                }
            }
        }

        return $fields;
    }

    protected function _getModelName()
    {
        return Generator_StringUtils::getModelName($this->_table, $this->_namespace);
    }

    protected function _getFieldName(Generator_Db_Field $fieldDesc)
    {
        $fieldName = $fieldDesc->getName(); //Generator_StringUtils::toCamelCase($fieldDesc->getName());

        if ($fieldDesc->isFso()) {
            if (preg_match('/(?P<fieldname>.*)FileSize$/', $fieldName, $matches)) {
                $fieldName = $matches['fieldname'];
            }
        }

        return $fieldName;
    }

    protected function _getFieldConf(Generator_Db_Field $fieldDesc)
    {
        $this->_translate->setLocale('es_ES');

        $title = ucfirst($this->_getFieldName($fieldDesc));

        $normalizedEntity = ucfirst(Generator_StringUtils::toCamelCase($title));

        $pluralEntity = ucfirst(Generator_StringUtils::getSentenceFromCamelCase($normalizedEntity));

        $pluralEntity = Generator_StringUtils::getPlural($pluralEntity);

        $singularEntity = Generator_StringUtils::getSingular($normalizedEntity);
        $singularEntity = ucfirst(Generator_StringUtils::getSentenceFromCamelCase($singularEntity));



        $titles = "_('" . $singularEntity . "')";

        if ($this->_translate->isTranslated($singularEntity)) {

            $trans = $this->_translate->translate($singularEntity);

            if (is_array($trans)) {

                $titles = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 1)";
            }

        }


        if ($singularEntity == $pluralEntity) {
            $pluralEntity = $pluralEntity . '(s)';
        }

        $data = array(
            'title' => $titles,
            'type' => $this->_getFieldDataType($fieldDesc),
            //'readonly' => '${auth.readOnly}'
        );

        $isRequired = $fieldDesc->isNullable()? false : true;

        if ($fieldDesc->hasDefaultValue()) {
            $data['defaultValue'] = $fieldDesc->getDefaultValue();
        }

        if ($isRequired && !$fieldDesc->hasDefaultValue()) {
            $data['required'] = 'true';
        }

        switch ($data['type']) {
            case 'text':
                if ($fieldDesc->getLength()) {
                    $data['maxLength'] = $fieldDesc->getLength();
                }
                break;
            case 'picker':
                $data['source'] = $this->_getTimeSource($fieldDesc);
                break;
            case 'number':
                $data['source'] = $this->_getNumberSource($fieldDesc);
                break;
            case 'select':
                $data['source'] = $this->_getSelectSource($fieldDesc);
                break;
            case 'textarea':
                if ($fieldDesc->isHtml()) {
                    $data['source'] = $this->_getHtmlSource($fieldDesc);
                }
                break;
            case 'file':
                $data['source'] = $this->_getFileSource($fieldDesc);
                break;
            break;
                case 'password':
                $data['adapter'] = 'Blowfish';
                break;
        }

        return $data;
    }

    protected function _getFieldDataType(Generator_Db_Field $fieldDesc)
    {
        if ($fieldDesc->isPassword()) {
            return 'password';
        }

        if ($this->_isSelectField($fieldDesc)) {
            return 'select';
        }

        if ($fieldDesc->isFso()) {
            return 'file';
        }

        switch ($fieldDesc->getType()) {
            case 'blob':
            case 'mediumblob':
            case 'longblob':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return 'textarea';
            case 'bigint':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':
                return 'number';
            case 'timestamp':
            case 'datetime':
            case 'date':
            case 'time':
                return 'picker';
            case 'varchar':
            default:
                return 'text';
        }
    }

    protected function _isSelectField(Generator_Db_Field $fieldDesc)
    {
        return $fieldDesc->isRelationship()
            || $fieldDesc->isBoolean()
            || $fieldDesc->isEnum();
    }


    protected function _getRelatedData(Generator_Db_Field $fieldDesc)
    {
        $data = array(
            'data' => 'mapper'
        );

        $relatedTableFields = Generator_Db::describeTable($fieldDesc->getRelatedTable());
        foreach ($relatedTableFields as $field) {
            if ($field->isPrimaryKey()) {
                $relatedField = $field;
            }
            if ($field->getType() == 'varchar') {
                $relatedField = $field;
                break;
            }
        }

        $relatedFieldName = $relatedField->getName();

        $orderField = $relatedFieldName;
        if ($relatedField->isMultilang()) {
            $orderField = $relatedFieldName . '_${lang}';
        }

        $data['config'] = array(
            'mapperName' => Generator_StringUtils::getMapperName($fieldDesc->getRelatedTable(), $this->_namespace),
            'fieldName' => array(
                'fields' => array(
                    $relatedFieldName,
                ),
                'template' => "'%" . $relatedFieldName. "%'",
            ),
            'order' => $orderField
        );

        if ($fieldDesc->isNullable()) {
            $data["'null'"] = '_("Unasigned")';
        }

        return $data;
    }

    protected function _getBooleanSelector()
    {
        $no = '_("No")';
        $yes = '_("Yes")';

        return array(
                'data' => 'inline',
                'values' => array(
                        "'0'" => array(
                                'title' => $no
                        ),
                        "'1'" => array(
                                'title' => $yes
                        )
                )
        );
    }

    protected function _getEnumSelector(Generator_Db_Field $fieldDesc)
    {
        return array(
            'data' => 'inline',
            'values' => $this->_getEnumValues($fieldDesc)
        );
    }

    protected function _getEnumValues(Generator_Db_Field $fieldDesc)
    {
        $acceptedValues = $fieldDesc->getAcceptedValues();
        $values = array();
        foreach ($acceptedValues as $value) {
            $values["'" . $value . "'"] = "_('" . $value . "')";
        }
        return $values;
    }

    protected function _getTimeSource(Generator_Db_Field $fieldDesc)
    {
        $control = $fieldDesc->getType();
        if ($control == 'timestamp') {
            $control = 'datetime';
        }
        return array(
            'control' => $control,
            'settings' => array(
                'disabled' => "'false'"
            )
        );
    }

    protected function _getNumberSource(Generator_Db_Field $fieldDesc)
    {
        return array(
            'control' => 'Spinner'
        );
    }

    protected function _getSelectSource(Generator_Db_Field $fieldDesc)
    {
        if ($fieldDesc->isRelationship()) {
            return $this->_getRelatedData($fieldDesc);
        }

        if ($fieldDesc->isBoolean()) {
            return  $this->_getBooleanSelector();
        }

        if ($fieldDesc->isEnum()) {
            return $this->_getEnumSelector($fieldDesc);
        }
    }

    protected function _getHtmlSource()
    {
        return array(
            'control' => 'tinymce',
            'template' => 'simple'
        );
    }

    protected function _getFileSource($fieldDesc)
    {

        $download = '_("Download file")';
        $upload = '_("Upload file")';

        $table = lcfirst($this->_table);

        return array(
            'data' => 'fso',
            'size_limit' => '20M',
//             'extensions' => array(),
            'options' => array(
                'download' => array(
                    'external' => 'true',
                    'type' => 'command',
                    'target' => $table . ucfirst($this->_getFieldName($fieldDesc)) . 'Download_command',
                    'icon' => 'ui-silk-bullet-disk',
                    'title' => $download,
                    'onNull' => 'hide'
                ),
                'upload' => array(
                    'type' => 'command',
                    'target' => $table . ucfirst($this->_getFieldName($fieldDesc)) . 'Upload_command',
                    'title' => $upload,
                    'class' => 'qq-uploader',
                    'onNull' => 'show'
                ),
                'preview' => array(
                    'target' => $table . ucfirst($this->_getFieldName($fieldDesc)) . 'Preview_command',
                    'type' => 'command',
                    'class' => 'filePreview',
                    'external' => true,
                    'props' => array(
                        'width' => 150,
                        'height' => 150,
                        'crop' => 'false'),
                    'onNull' => 'hide'
                ),
                'previewList' => array(
                    'target' => $table . ucfirst($this->_getFieldName($fieldDesc)) . 'Preview_command',
                    'type' => 'command',
                    'class' => 'filePreview',
                    'listController' => true,
                    'external' => true,
                    'props' => array(
                        'width' => 30,
                        'height' => 30,
                        'crop' => 'false'),
                    'onNull' => 'hide'
                )
            )
        );
    }
}