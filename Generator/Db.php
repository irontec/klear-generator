<?php
class Generator_Db
{
    const ISVIEW = 3001;

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

        $description = array_change_key_case($description, CASE_LOWER);

        foreach ($data as $dataRow) {
            // Related tables/fields
            if (preg_match('/FOREIGN KEY \(.(?P<fieldName>.*).\)\s+REFERENCES\s+.(?P<foreignTable>.*).\s+\(.(?P<foreignField>.*).\)/', $dataRow, $matches)) {
                $description[strtolower($matches['fieldName'])]['RELATED'] = array(
                    'TABLE' => $matches['foreignTable'],
                    'FIELD' => $matches['foreignField']
                );
            }

            // TinyInt length for boolean detection
            if (preg_match("/`(?P<fieldName>.*)`\s+tinyint\((?P<length>\d+)\)/", $dataRow, $matches)) {
                $description[strtolower($matches['fieldName'])]['LENGTH'] = (int)$matches['length'];
            }

            // Comments
            if (preg_match("/`(?P<fieldName>.*)`.*COMMENT\s+'(?P<comment>.*)'/", $dataRow, $matches)) {
                $description[strtolower($matches['fieldName'])]['COMMENT'] = $matches['comment'];
            }
        }

        $fieldsList = new \Generator_Db_FieldCollection();
        foreach ($description as $name => $fieldDesc) {
            $fieldsList->add(new \Generator_Db_Field($fieldDesc));
        }
        return $fieldsList;
    }

    /**
     * Returns table comment (if any)
     * @param unknown_type $tablename
     * @return unknown|string
     */
    public static function tableComment($tablename)
    {
        try {
            $data = self::_getCreateTableData($tablename);
            foreach ($data as $row) {
                if (preg_match("/.*ENGINE.*COMMENT='(?P<comment>.*)'/", $row, $matches)) {
                    return $matches['comment'];
                }
            }
        } catch(Exception $e) {
            echo $e->getMessage() ."\n";
        }

        return '';

    }
    
    public static function getDependantTables($namespace, $tbname, $tableList) 
    {
        $tables = $tableList;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $dbAdapter->query("SET NAMES UTF8");

        $dependents = array();

        foreach ($tables as $table) {
            
            $normalizedTableName = ucfirst(Generator_StringUtils::toCamelCase($table));
            $qry = $dbAdapter->query("show create table `$table`");

            if (!$qry) {
                throw new Exception("`show create table $table` returned false!");
            }

            $res = $qry->fetchAll();
            if (isset($res[0]['Create View'])) {
                continue;
            }

            if (!isset($res[0]['Create Table'])) {
                throw new Exception("`show create table $table` did not provide known output");
            }

            $query = $res[0]['Create Table'];
            $lines = explode("\n",$query);
            $tblinfo = array();
            $pk = '';
            foreach ($lines as $line) {
                if (preg_match('/^\s*PRIMARY KEY \(`(.+)`\)/', $line, $matches)) {
                    $pk_string = $matches[1];
                } elseif (preg_match("/^\s*CONSTRAINT `(\w+)` FOREIGN KEY \(`(.+)`\) REFERENCES `$tbname` \(`(.+)`\)/", $line, $tblinfo)) {
                    if (strpos($tblinfo[2], ',') !== false) {
                        $column_name = explode('`,`', $tblinfo[2]);
                    } else {
                        $column_name = $tblinfo[2];
                    }

                    if (strpos($tblinfo[3], ',') !== false) {
                        $foreign_column_name = explode(',', $tblinfo[3]);
                    } else {
                        $foreign_column_name = $tblinfo[3];
                    }

                    $dependents[] = array(
                        'key_name' => $tblinfo[1],
                        'tbl_name' => $namespace . '\\Model\\DbTable\\' . $normalizedTableName,
                        'type' => ($pk_string == $tblinfo[2] ? 'one' : 'many'),
                          'column_name' => $column_name,
                        'foreign_tbl_name' => $table,
                          'foreign_tbl_column_name' => $foreign_column_name
                    );
                }

            }
        }

        return $dependents;
    }

    protected static function _getCreateTableData($tablename)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = 'show create table ' . $db->quoteIdentifier($tablename);
        $createTable = $db->fetchRow($sql);
        if (isset($createTable['Create View'])) {
            throw new Exception($db->quoteIdentifier($tablename) . " is a view. Skipping.", self::ISVIEW);
        }

        $data = explode("\n", $createTable['Create Table']);
        return $data;
    }
}