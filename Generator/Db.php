<?php
class Generator_Db
{
    /**
     * Añade foreign keys y comments a la descripción de la tabla
     * @param unknown_type $tablename
     * @return Ambigous <multitype:unknown , unknown, multitype:>
     */
    public static function describeTable($tablename)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $description = $db->describeTable($tablename);
        $data = self::_getCreateTableData($tablename);

        foreach ($data as $dataRow) {
            // Related tables/fields
            if (preg_match('/FOREIGN KEY \(.(?P<fieldName>.*).\)\s+REFERENCES\s+.(?P<foreignTable>.*).\s+\(.(?P<foreignField>.*).\)/', $dataRow, $matches)) {
                $description[$matches['fieldName']]['RELATED'] = array(
                    'TABLE' => $matches['foreignTable'],
                    'FIELD' => $matches['foreignField']
                );
            }
            // Comments
            if (preg_match("/`(?P<fieldName>.*)`.*COMMENT\s+'(?P<comment>.*)'/", $dataRow, $matches)) {
                $description[$matches['fieldName']]['COMMENT'] = $matches['comment'];
            }
        }

        $fieldsList = array();
        foreach ($description as $name => $fieldDesc) {
            $fieldsList[$name] = new Generator_Db_Field($fieldDesc);
        }
        return $fieldsList;
    }

    public static function tableComment($tablename)
    {
        $data = self::_getCreateTableData($tablename);
        foreach ($data as $row) {
            if (preg_match("/.*ENGINE.*COMMENT='(?P<comment>.*)'/", $row, $matches)) {
                return $matches['comment'];
            }
        }
        return '';
    }

    protected static function _getCreateTableData($tablename)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = 'show create table ' . $db->quoteIdentifier($tablename);
        $createTable = $db->fetchRow($sql);
        $data = explode("\n", $createTable['Create Table']);
        return $data;
    }

    public function __construct($config)
    {

    }
}