<?php

class Generator_Db_Table
{
    protected $_config;
    protected $_table;
    protected $_db;

    public function __construct($table, $config)
    {
        $this->_table = $table;
        $this->_config = $config;
        $this->_db = Zend_Db_Table::getDefaultAdapter();
    }

    public function generateMultilangFields()
    {
        $fieldsDescription = Generator_Db::describeTable($this->_table);
        foreach ($fieldsDescription as $field) {

            if ($field->isMultilang()) {

                foreach ($this->_config->klear->languages as $language) {
                    $newFieldName = $field->getName() . '_' . $language;

                    if (!isset($fieldsDescription[$newFieldName])) {
                        $query = 'ALTER TABLE ' . $this->_db->quoteIdentifier($field->getTableName())
                        . ' ADD ' . $this->_db->quoteIdentifier($newFieldName)
                        . ' ' . $field->getType() . $field->getLengthDefinition();

                        if (!$field->isNullable()) {
                            $query .= ' NOT NULL ';
                        }

                        if ($field->hasDefaultValue()) {
                            $query .= ' DEFAULT ' . $this->_db->quote($field->getDefaultValue());
                        }

                        $query .= ' COMMENT ' . $this->_db->quote(str_ireplace('[ml]', '', $field->getComment()));

                        $query .= ' AFTER ' . $this->_db->quoteIdentifier($field->getName());
var_dump($query);
                        $this->_db->query($query);

                        echo  "$newFieldName added to {$this->_table} \n";
                    }
                }
            }
        }
    }

    public function generateFileFields()
    {
        $fsoFields = array(
            'BaseName' => array(
                'type' => 'VARCHAR(255)',
                'comment' => ''
            ),
            'MimeType' => array(
                'type' => 'VARCHAR(80)',
                'comment' => ''
            ),
            'Md5Sum' => array(
                'type' => 'VARCHAR(80)',
                'comment' => ''
            ),
            'FileSize' => array(
                'type' => 'INT(11) UNSIGNED',
                'comment' => '[FSO]'
            ),
        );
        $fieldsDescription = Generator_Db::describeTable($this->_table);
        foreach ($fieldsDescription as $field) {

            if ($field->isFile()) {

                foreach ($fsoFields as $fsoFieldName => $fsoFieldData) {
                    $newFieldName = $field->getName() . $fsoFieldName;

                    if (!isset($fieldsDescription[$newFieldName])) {
                        $query = 'ALTER TABLE ' . $this->_db->quoteIdentifier($field->getTableName())
                        . ' ADD ' . $this->_db->quoteIdentifier($newFieldName)
                        . ' ' . $fsoFieldData['type'];

                        if (!$field->isNullable()) {
                            $query .= ' NOT NULL ';
                        }

                        $query .= ' COMMENT ' . $this->_db->quote(str_ireplace('[file]', '', $field->getComment()) . $fsoFieldData['comment']);

                        $query .= ' AFTER ' . $this->_db->quoteIdentifier($field->getName());

                        $this->_db->query($query);

                        echo  "$newFieldName added to {$this->_table} \n";
                    }
                }

                $query = 'ALTER TABLE ' . $this->_db->quoteIdentifier($field->getTableName())
                     . ' DROP ' .  $field->getName();
                $this->_db->query($query);
                echo  $field->getName() . " deleted to {$this->_table} \n";
            }
        }
    }
}