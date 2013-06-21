<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model DbTables
 *
 * @package <?=$namespace?>Mapper\Sql\DbTable
 * @subpackage DbTable
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */
<? if ($this->_addRequire): ?>

/**
 * Abstract class for <?=$namespace?>Mapper\Sql\DbTable
 * @see <?=$this->_includeTable->getParentClass() . "\n"?>
 */
require_once 'TableAbstract.php';
<? endif; ?>

/**
 * Table definition for <?=$this->getTableName()."\n"?>
 *
 * @package <?=$namespace?>Mapper\Sql\DbTable
 * @subpackage DbTable
 * @author <?=$this->_author . "\n"?>
 */

namespace <?=$namespace?>Mapper\Sql\DbTable;
class <?=$this->_className?> extends <?= str_replace("_", "\\", $this->_includeTable->getParentClass()) . "\n"?>
{
    /**
     * $_name - name of database table
     *
     * @var string
     */
    protected $_name = '<?=$this->_tbname?>';

    /**
     * $_id - this is the primary key name
     *
     * @var <?=$this->_primaryKey[$this->getTablename()]['phptype'] . "\n"?>
     */
    protected $_id = <?php
    if ($this->_primaryKey[$this->getTablename()]['phptype'] !== 'array') {
        echo '\'' . $this->_primaryKey[$this->getTablename()]['field'] . '\'';
    } else {
        echo $this->_primaryKey[$this->getTablename()]['field'];
    }
    ?>;

    protected $_rowClass = '<?=$namespace?>\Model\\<?=$this->getNormalizedTableName();?>';
    protected $_rowMapperClass = '<?=$namespace?>\Mapper\\Sql\\<?=$this->getNormalizedTableName();?>';

    protected $_sequence = <?=($this->_primaryKey[$this->getTablename()]['phptype'] !== 'array') ? 'true' : 'false'; ?>; // <?=$this->_primaryKey[$this->getTablename()]['phptype'];?>

    <?=$referenceMap?>

    <?=$dependentTables?>

    <?=$metadata?>

<?=$this->_includeTable->getVars() . "\n"?>

<?=$this->_includeTable->getFunctions() . "\n"?>

}
