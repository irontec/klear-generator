<?php

class Generator_Db_Table
{
    protected $_config;
    protected $_table;
    protected $_db;

    public function __construct($table, $config, $dbWriter = null) {

        $this->_table = $table;
        $this->_config = $config;
        $this->_db = Zend_Db_Table::getDefaultAdapter();

        if (is_null($dbWriter)) {
            $this->_dbWriter = $this->_db;
        } else {
            $this->_dbWriter = $dbWriter;
        }
    }

    public function generateAllFields() {

        $this->generateMultilangFields();
        $this->generateFileFields();
        $this->generateVideoFields();
        $this->generateMapFields();
    }

    protected function _describeTable() {

        try {

            return Generator_Db::describeTable($this->_table);

        } catch (Exception $exception) {

            if ($exception->getCode() == Generator_Db::ISVIEW) {

                echo  "Skipping {$this->_table} \n";
                return null;
            }

            Throw $exception;
        }
    }

    public function generateMultilangFields() {

        $fieldsDescription = $this->_describeTable();
        if (is_null($fieldsDescription)) {
            return null;
        }
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

                        $comment = str_ireplace('[ml]', '', $field->getComment());

                        if ($field->isUrlIdentifier()){
                            $identifiedField = $field->getIdentifiedFieldName();
                            if ($identifiedField) {
                                $langIdentifiedField = $identifiedField . '_' . $language;
                                $comment = str_ireplace('[urlIdentifier:' . $identifiedField . ']', '[urlIdentifier:' . $langIdentifiedField . ']', $comment);
                            }
                        }

                        $query .= ' COMMENT ' . $this->_db->quote($comment);

                        $query .= ' AFTER ' . $this->_db->quoteIdentifier($field->getName());

                        $this->_dbWriter->query($query);

                        echo  "$newFieldName added to {$this->_table} \n";
                    }
                }
            }
        }
    }

    public function generateFileFields() {

        $fsoFields = array(
            'BaseName' => array(
                'type' => 'VARCHAR(255)',
                'comment' => ''
            ),
            'MimeType' => array(
                'type' => 'VARCHAR(80)',
                'comment' => ''
            ),
//             'Md5Sum' => array(
//                 'type' => 'VARCHAR(80)',
//                 'comment' => ''
//             ),
            'FileSize' => array(
                'type' => 'INT(11) UNSIGNED',
                'comment' => '[FSO]'
            ),
        );

        $fieldsDescription = $this->_describeTable();
        if (is_null($fieldsDescription)) {
            return null;
        }

        foreach ($fieldsDescription as $field) {

            if ($field->isFile()) {

                foreach ($fsoFields as $fsoFieldName => $fsoFieldData) {
                    $newFieldName = $field->getName() . $fsoFieldName;

                    if (!isset($fieldsDescription[$newFieldName])) {

                        $this->_addField($field,$newFieldName, $fsoFieldData, '[file]');
                    }
                }

                $query = 'ALTER TABLE ' . $this->_db->quoteIdentifier($field->getTableName())
                     . ' DROP ' .  $field->getName();
                $this->_dbWriter->query($query);
                echo  $field->getName() . " deleted in {$this->_table} \n";
            }
        }
    }

    public function generateVideoFields() {

        $videoFields = array(
            'Thumbnail' => array(
                'type' => "varchar(120) NOT NULL DEFAULT ''",
                'comment' => ''
            ),
            'Source' => array(
                'type' => "enum('youtube','vimeo')",
                'comment' => ''
            ),
            'Title' => array(
                'type' => 'varchar(90) NOT NULL DEFAULT ""',
                'comment' => ''
            )
        );

        $fieldsDescription = $this->_describeTable();
        if (is_null($fieldsDescription)) {
            return null;
        }

        foreach ($fieldsDescription as $field) {

            if ($field->isVideo()) {

                $fieldName = $field->getName();

                foreach ($videoFields as $videoFieldName => $videoFieldData) {

                    $newFieldName = $fieldName . $videoFieldName;

                    if (!isset($fieldsDescription[$newFieldName])) {

                        $this->_addField($field,$newFieldName, $videoFieldData, '[video]');
                    }
                }
            }
        }
    }

    public function generateMapFields() {

        $mapFields = array(
            'Lng' => array(
                'type' => "decimal(10,7)",
                'comment' => ''
            ),
            'Lat' => array(
                'type' => "decimal(10,7)",
                'comment' => ''
            ),
        );

        $fieldsDescription = $this->_describeTable();
        if (is_null($fieldsDescription)) {
            return null;
        }

        foreach ($fieldsDescription as $field) {

            if ($field->isMap()) {

                $fieldName = $field->getName();

                foreach ($mapFields as $mapFieldName => $mapFieldData) {

                    $newFieldName = $fieldName . $mapFieldName;

                    if (!isset($fieldsDescription[$newFieldName])) {

                        $this->_addField($field,$newFieldName, $mapFieldData, '[map]');
                    }
                }
            }
        }
    }

    protected function _addField($field, $newFieldName, $newFieldData, $commentTag) {

        $query = 'ALTER TABLE ' . $this->_db->quoteIdentifier($field->getTableName())
        . ' ADD ' . $this->_db->quoteIdentifier($newFieldName)
        . ' ' . $newFieldData['type'];

        if (!$field->isNullable()) {
            $query .= ' NOT NULL ';
        }

        $query .= ' COMMENT ' . $this->_db->quote(str_ireplace($commentTag, '', $field->getComment()) . $newFieldData['comment']);

        $query .= ' AFTER ' . $this->_db->quoteIdentifier($field->getName());

        $this->_dbWriter->query($query);

        echo  "$newFieldName added to {$this->_table} \n";
    }
}