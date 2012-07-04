<?php
class Generator_Yaml_ModelConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_table;
    protected $_db;

    public function __construct($table, $namespace)
    {
        $this->_table = $table;
        $this->_namespace = $namespace;
        $this->_db = Zend_Db_Table::getDefaultAdapter();


        $data['class'] = $this->_getClassName();
        $data['fields'] = array();

        $fields = Generator_Db::describeTable($table);
        foreach ($fields as $field) {
            $data['fields'][$this->_getFieldName($field)] = $this->_getFieldConf($field);
        }
        $this->_data['production'] = $data;
    }

    protected function _getClassName()
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
            'type' => $this->_getFieldDataType($fieldDesc),
            'required' => $fieldDesc['NULLABLE']? 'false' : 'true',
//             'readonly' => '${auth.readOnly}'
        );

        if ($fieldDesc['DEFAULT']) {
            $data['default'] = $fieldDesc['DEFAULT'];
        }

        if ($this->_isRelationship($fieldDesc)) {
            $data['source'] = $this->_getRelatedData($fieldDesc);
        }

        if ($data['type'] == 'picker') {
            $data['source'] = $this->_getTimeSource($fieldDesc);
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

    protected function _getRelatedData($fieldDesc) {
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

    protected function _getTimeSource($fieldDesc)
    {
        $data = array(
            'control' => $fieldDesc['DATA_TYPE'],
            'setting' => array(
                'disabled' => "'false'"
            )
        );
        return $data;
    }
}