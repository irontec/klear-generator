<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 *
 * @package <?=$namespace?>Model
 * @subpackage Paginator
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */

/**
 * Paginator class that extends Zend_Paginator_Adapter_DbSelect to return an
 * object instead of an array.
 *
 * @package <?=$namespace?>Model
 * @subpackage Paginator
 * @author <?=$this->_author."\n"?>
 */
namespace <?=$namespace?>Model;
class Paginator extends Zend_Paginator_Adapter_DbSelect
{
    /**
     * Object mapper
     *
     * @var <?=$namespace?>Mapper\Sql\Raw\MapperAbstract
     */
    protected $_mapper = null;

    /**
     * Constructor.
     *
     * @param Zend_Db_Select $select The select query
     * @param <?=$namespace?>Mapper\Sql\Raw\MapperAbstract $mapper The mapper associated with the object type
     */
    public function __construct(Zend_Db_Select $select, <?=$namespace?>Mapper\Sql\Raw\MapperAbstract $mapper)
    {
        $this->_mapper = $mapper;
        parent::__construct($select);
    }

    /**
     * Returns an array of items as objects for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array An array of <?=$namespace?>\Raw\ModelAbstract objects
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $items = parent::getItems($offset, $itemCountPerPage);
        $objects = array();

        foreach ($items as $item) {
            $objects[] = $this->_mapper->loadModel($item, null);
        }

        return $objects;
    }
}
