<?='<?php'?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model DbTables
 *
 * @package <?=$namespace?>Mapper
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */
<?php if ($this->_addRequire): ?>

/**
 * Zend DB Table Abstract class
 */
require_once 'Zend<?=DIRECTORY_SEPARATOR?>Db<?=DIRECTORY_SEPARATOR?>Table<?=DIRECTORY_SEPARATOR?>Rowset<?=DIRECTORY_SEPARATOR?>Abstract.php';
<?php endif; ?>

/**
 * Rowset class that uses generated Mappers and Models to load data from DB
 *
 * @package <?=$namespace?>Mapper\Sql\DbTable
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 */
namespace <?=$namespace?>Mapper\Sql\DbTable;
class Rowset extends \Zend_Db_Table_Rowset_Abstract
{
    protected $_rowMapper;
    public function init()
    {
        $table = $this->getTable();
        $rowMapper = $table->getRowMapperClass();
        $this->_rowMapper = new $rowMapper;
    }

    protected function _loadAndReturnRow($position)
    {
        if (!isset($this->_data[$position])) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception("Data for provided position does not exist");
        }

        // do we already have a row object for this position?
        if (empty($this->_rows[$position])) {
            $this->_rows[$position] = $this->_rowMapper->loadModel($this->_data[$position]);
        }

        // return the row object
        return $this->_rows[$position];
    }
}