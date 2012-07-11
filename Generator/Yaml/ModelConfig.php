<?php
class Generator_Yaml_ModelConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_table;
    protected $_klearConfig;
    protected $_db;

    public function __construct($table, $namespace, $klearConfig)
    {
        $this->_table = $table;
        $this->_namespace = $namespace;
        $this->_klearConfig = $klearConfig;
        $this->_db = Zend_Db_Table::getDefaultAdapter();

        $data['class'] = $this->_getModelName();
        $data['fields'] = array();

        $fields = $this->_getFields($table);

        $firstField = true;
        foreach ($fields as $field) {
            $fieldName = $this->_getFieldName($field);

            $data['fields'][$fieldName] = $this->_getFieldConf($field);

            // First field used as default field
            if ($firstField) {

                $data['fields'][$fieldName]['default'] = 'true';
                $firstField = false;
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

            if ($field->isPrimaryKey()) {
                unset($fields[$fieldName]);
            }

            if (isset($this->_klearConfig->klear->languages)) {
                if ($field->isMultilang()) {

                    foreach ($this->_klearConfig->klear->languages as $language) {

                        unset($fields[$fieldName . '_' . $language]);
                    }
                }
            }
        }

        return $fields;
    }

    protected function _getModelName()
    {
        return Generator_Yaml_StringUtils::getModelName($this->_table, $this->_namespace);
    }

    protected function _getFieldName(Generator_Db_Field $fieldDesc)
    {
        return Generator_Yaml_StringUtils::toCamelCase($fieldDesc->getName());
    }

    protected function _getFieldConf(Generator_Db_Field $fieldDesc)
    {
        $data = array(
            'title' => array(
                'i18n' => array(
                    'es' => ucfirst($this->_getFieldName($fieldDesc))
                )
            ),
            'required' => $fieldDesc->isNullable()? 'false' : 'true',
            'type' => $this->_getFieldDataType($fieldDesc),
//             'readonly' => '${auth.readOnly}'
        );

//         if ($fieldDesc['DEFAULT']) {
//             $data['defaultValue'] = $fieldDesc['DEFAULT'];
//         }

        switch ($data['type']) {
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
                    $data['souce'] = $this->_getHtmlSource($fieldDesc);
                }
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

        switch ($fieldDesc->getType()) {
            case 'text':
            case 'mediumtext':
                return 'textarea';
            case 'mediumint':
            case 'tinyint':
                return 'number';
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

        if ($fieldDesc->isNullable()) {
            $data["'null'"] = array(
                'i18n' => array(
                    'es' => 'Sin asignar'
                )
            );
        }

        $data['config'] = array(
            'mapperName' => Generator_Yaml_StringUtils::getMapperName($fieldDesc->getRelatedTable(), $this->_namespace),
            'fieldName' => array(
                'fields' => array(
                    $fieldDesc->getRelatedField()
                ),
                'template' => "'%" . $fieldDesc->getRelatedField() . "%'",
                'order' => $fieldDesc->getRelatedField()
            )
        );

        return $data;
    }

    protected function _getBooleanSelector()
    {
        return array(
            'data' => 'inline',
            'values' => array(
                "'0'" => array(
                    'title' => array(
                        'i18n' => array(
                            'es' => '"No"'
                        )
                    )
                ),
                "'1'" => array(
                    'title' => array(
                        'i18n' => array(
                            'es' => 'Sí'
                        )
                    )
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
        $values = array();
        if (preg_match('/enum\((?P<values>.*)\)$/', $fieldDesc->getType(), $matches)) {
            if (isset($matches['values'])) {
                $untrimmedValues = explode(',', $matches[1]);
                foreach ($untrimmedValues as $value) {
                    $values[trim($value, '"\'')] = trim($value, '"\'');
                }
            }
        }
        return $values;
    }

    protected function _getTimeSource(Generator_Db_Field $fieldDesc)
    {
        return array(
            'control' => $fieldDesc->getType(),
            'setting' => array(
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
            'control' => 'tinymce'
        );
    }

}