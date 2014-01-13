<?php
class Zend_View_Helper_Geticon extends Zend_View_Helper_Abstract
{
    public function geticon($class,$path)
    {
        $iconName = str_replace('ui-silk-', "", $class);
        
        $iconName = str_replace('-', "_", $iconName);
        
        $iconSource = dirname(__FILE__).'/../../icons/'.$iconName.'.png';
        $iconDestiny = $path.'/doc/icons/'.$class.'.png';
        
        if(file_exists($iconSource)) {
            $copyIcon = copy($iconSource,$iconDestiny);
        }
            
        $yamlImporter = new Generator_Doc_YamlImporter();
        
        $yaml = $yamlImporter->getYaml('klear.yaml');
        
        $dirSource =  APPLICATION_PATH.'/../public'.$yaml['main']['cssExtended']['silkExtendedIconPath'];
        
        if (is_dir($dirSource)) {
            $iconName = str_replace('ui-silk-', "", $class);
            
            
            $iconSource = $dirSource.'/'.$iconName.'.png';
            
            if (file_exists($iconSource)) {
                $copyIcon = copy($iconSource,$iconDestiny);
            }
        }
        
        if(file_exists($iconDestiny)) {
            if (filesize($iconSource) == filesize($iconDestiny)) {
                return 'icons/'.$class.'.png';
            }
        }
        
        if ($copyIcon) {
            return 'icons/'.$class.'.png';
        }
        
        return 'icons/ui-silk-accept.png';
    }
}