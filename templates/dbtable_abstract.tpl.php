<?='<?php'?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model DbTables
 *
 * @package <?=$namespace?>Model
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */
<?php if ($this->_addRequire): ?>

/**
 * Zend DB Table Abstract class
 */
require_once 'Zend<?=DIRECTORY_SEPARATOR?>Db<?=DIRECTORY_SEPARATOR?>Table<?=DIRECTORY_SEPARATOR?>Abstract.php';
<?php endif; ?>

/**
 * Abstract class that is extended by all tables
 *
 * @package <?=$namespace?>Mapper\Sql\DbTable
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 */
namespace <?=$namespace?>Mapper\Sql\DbTable;
abstract class TableAbstract extends \Zend_Db_Table_Abstract
{
    /**
     * $_name - Name of database table
     *
     * @return string
     */
    protected $_name;

    /**
     * $_id - The primary key name(s)
     *
     * @return string|array
     */
    protected $_id;

    /**
     * Returns the primary key column name(s)
     *
     * @return string|array
     */
    public function getPrimaryKeyName()
    {
        return $this->_id;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_name;
    }

    public function addReferenceMapEntry($key, $value) {

        $this->_referenceMap[$key] = $value;
    }

    /**
     * Returns the number of rows in the table
     *
     * @param $estimated bool true if an estimated value is enough (use explain rows value)
     * @return int
     */
    public function countAllRows($estimated = false)
    {
        $query = $this->_getCountQuery();
        if ($estimated) {
            $row = $this->getAdapter()->query('EXPLAIN ' . $query)->fetch();
            return (int) $row['rows'];
        } else {
            $row = $this->getAdapter()->query($query)->fetch();
            return (int) $row['all_count'];
        }
    }

    /**
     * Returns the number of rows in the table with optional WHERE clause
     *
     * @param $where string Where clause to use with the query
     * @return int
     */
    public function countByQuery($where = null)
    {
        $count = 0;
        $query = $this->_getCountQuery($where);

        $row = $this->getAdapter()->query($query)->fetch();
        return (int) $row['all_count'];
    }

    public function _getCountQuery($where = null)
    {
        $query = $this->select()->from($this->_name, 'count(*) AS all_count');

        if (is_array($where)) {

            list($where, $bind) = $where;

            $query->where($where);
            $query->bind($bind);

        } else if (!empty($where)) {

            $query->where($where);
        }
        return $query;
    }

    /**
     * Generates a query to fetch a list with the given parameters
     *
     * @param $where string Where clause to use with the query
     * @param $order string Order clause to use with the query
     * @param $count int Maximum number of results
     * @param $offset int Offset for the limited number of results
     * @return Zend_Db_Select
     */
    public function fetchList($where = null, $order = null, $count = null,
        $offset = null
    ) {
        $select = $this->select()
                            ->order($order)
                            ->limit($count, $offset);

        if (is_array($where)) {

            list($where, $bind) = $where;

            $select->where($where);
            $select->bind($bind);

        } else if (!empty($where)) {

            $select->where($where);
        }

        return $select;
    }

    public function getReferenceMap($key)
    {
        if (isset($this->_referenceMap[$key]['columns'])) {
            return $this->_referenceMap[$key]['columns'];
        }
        return null;
    }
}
