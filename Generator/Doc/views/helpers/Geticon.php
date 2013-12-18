<?php
class Zend_View_Helper_Geticon extends Zend_View_Helper_Abstract
{
    public function geticon($class)
    {
        return self::cpIcon($class);
    }
    protected function cpIcon($class) {
        
        $iconName = str_replace('ui-silk-', "", $class);
        
        $iconName = str_replace('-', "_", $iconName);
        
        $iconSource = dirname(__FILE__).'/../../icons/'.$iconName.'.png';
        $iconDestiny = APPLICATION_PATH.'/configs/klear/doc/icons/'.$class.'.png';
        
        if(file_exists($iconDestiny)) {
             return 'icons/'.$class.'.png';
        }
        
        if(file_exists($iconSource)) {
            $copyIcon = copy($iconSource,$iconDestiny);
            
            if ($copyIcon) {
                return 'icons/'.$class.'.png';
            }
            
        } else {
            return false;
        }
    }
}