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

            $fieldObj = new Generator_Db_Field($field);
            $fieldName = $fieldObj->getName();

            if ($fieldObj->isPrimaryKey()) {
                unset($fields[$fieldName]);
            }

            if (isset($this->_klearConfig->klear->languages)) {
                if ($fieldObj->isMultilang()) {

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

    protected function _getFieldName($fieldDesc)
    {
        return Generator_Yaml_StringUtils::toCamelCase($fieldDesc['COLUMN_NAME']);
    }

    protected function _getFieldConf($fieldDesc)
    {
        $data = array(
            'title' => array(
                'i18n' => array(
                    'es' => ucfirst($this->_getFieldName($fieldDesc))
                )
            ),
            'required' => $fieldDesc['NULLABLE']? 'false' : 'true',
            'type' => $this->_getFieldDataType($fieldDesc),
//             'readonly' => '${auth.readOnly}'
        );

//         if ($fieldDesc['DEFAULT']) {
//             $data['defaultValue'] = $fieldDesc['DEFAULT'];
//         }

        if ($this->_isRelationship($fieldDesc)) {
            $data['source'] = $this->_getRelatedData($fieldDesc);
        }

        if ($this->_isBoolean($fieldDesc)) {
            $data['source'] = $this->_getBooleanSelector();
        }

        if ($this->_isEnum($fieldDesc)) {
            $data['source'] = $this->_getEnumSelector($fieldDesc);
        }

        switch ($data['type']) {
            case 'picker':
                $data['source'] = $this->_getTimeSource($fieldDesc);
                break;
            case 'number':
                $data['source'] = $this->_getNumberSource($fieldDesc);
                break;
        }

        return $data;
    }

    protected function _getFieldDataType($fieldDesc)
    {
        if ($this->_isPasswordField($fieldDesc)) {
            return 'password';
        }

        if ($this->_isRelationship($fieldDesc)) {
            return 'select';
        }

        if ($this->_isBoolean($fieldDesc)) {
            return 'select';
        }

        if ($this->_isEnum($fieldDesc)) {
            return 'select';
        }

        switch ($fieldDesc['DATA_TYPE']) {
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

    protected function _isPasswordField($fieldDesc)
    {
        return $fieldDesc['DATA_TYPE'] == 'varchar' && strstr($fieldDesc['COLUMN_NAME'], 'passw');
    }

    protected function _isRelationship($fieldDesc)
    {
        return isset($fieldDesc['RELATED']);
    }

    protected function _isBoolean($fieldDesc)
    {
        return $fieldDesc['DATA_TYPE'] == 'tinyint' && $fieldDesc['LENGTH'] == 1;
    }

    protected function _isEnum($fieldDesc)
    {
        return preg_match('/enum\(.*\)$/', $fieldDesc['DATA_TYPE']);
    }

    protected function _getRelatedData($fieldDesc)
    {
        $data = array(
            'data' => 'mapper'
        );

        if ($fieldDesc['NULLABLE']) {
            $data["'null'"] = array(
                'i18n' => array(
                    'es' => 'Sin asignar'
                )
            );
        }

        $data['config'] = array(
            'mapperName' => Generator_Yaml_StringUtils::getMapperName($fieldDesc['RELATED']['TABLE'], $this->_namespace)
        );

        $data['fieldName'] = array(
            'fields' => array(
                $fieldDesc['RELATED']['FIELD']
            ),
            'template' => "'%" . $fieldDesc['RELATED']['FIELD'] . "%'",
            'order' => $fieldDesc['RELATED']['FIELD']
        );
        return $data;
    }

    protected function _getBooleanSelector()
    {
        return array(
            'data' => 'inline',
            'values' => array(
                "'0'" => 'No',
                "'1'" => 'Sí'
            )
        );
    }

    protected function _getEnumSelector($fieldDesc)
    {
        return array(
            'data' => 'inline',
            'values' => $this->_getEnumValues($fieldDesc)
        );
    }

    protected function _getEnumValues($fieldDesc)
    {
        $values = array();
        if (preg_match('/enum\((?P<values>.*)\)$/', $fieldDesc['DATA_TYPE'], $matches)) {
            if (isset($matches['values'])) {
                $untrimmedValues = explode(',', $matches[1]);
                foreach ($untrimmedValues as $value) {
                    $values[trim($value, '"\'')] = trim($value, '"\'');
                }
            }
        }
        return $values;
    }

    protected function _getTimeSource($fieldDesc)
    {
        return array(
            'control' => $fieldDesc['DATA_TYPE'],
            'setting' => array(
                'disabled' => "'false'"
            )
        );
    }

    protected function _getNumberSource($fieldDesc)
    {
        return array(
            'control' => 'Spinner'
        );
    }
}