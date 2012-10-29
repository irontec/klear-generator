<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model
 *
 * @package <?=$namespace?>Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 * @copyright <?=$this->_copyright."\n"?>
 * @license <?=$this->_license."\n"?>
 */

/**
 * <?=$this->_classDesc[$this->getTableName()]."\n"?>
 *
 * @package <?=$namespace?>Model
 * @subpackage Model
 * @author <?=$this->_author."\n"?>
 */
 
namespace <?=$namespace?>Model;
class <?=$this->_className?> extends Raw\<?=$this->_className?>

{
    /**
     * This method is called just after parent's constructor
     */
    public function init()
    {
    }
}
