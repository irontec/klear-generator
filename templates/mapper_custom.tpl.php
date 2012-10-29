<?="<?php\n"?>
<?php
$namespace = !empty($this->_namespace) ? $this->_namespace . "\\" : "";
?>

/**
 * Application Model Mapper
 *
 * @package Mapper
 * @subpackage Sql
 * @author <?=$this->_author . "\n"?>
 * @copyright <?=$this->_copyright . "\n"?>
 * @license <?=$this->_license . "\n"?>
 */
<?php if ($this->_addRequire):?>

/**
 * Table definition for this class
 * @see <?=$namespace?>Mapper\DbTable\<?=$this->_className."\n"?>
 */
require_once dirname(__FILE__) . '/../DbTable/<?=$this->_className?>.php';
<?php endif ?>

/**
 * Data Mapper implementation for <?=$namespace?>Model\<?=$this->_className."\n"?>
 *
 * @package Mapper
 * @subpackage Sql
 * @author <?=$this->_author . "\n"?>
 */
namespace <?=$namespace?>Mapper\Sql;
class <?=$this->_className?> extends Raw\<?=$this->_className?>

{

}
