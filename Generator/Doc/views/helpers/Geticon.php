<?php
class Zend_View_Helper_Geticon extends Zend_View_Helper_Abstract
{
    public function geticon($class,$path)
    {
        $iconName = str_replace('ui-silk-', "", $class);
        
        $iconName = str_replace('-', "_", $iconName);
        
        $iconSource = dirname(__FILE__).'/../../icons/'.$iconName.'.png';
        
        $yamlImporter = new Generator_Doc_YamlImporter();
        
        $yaml = $yamlImporter->getYaml('klear.yaml');
        
        $dirSource =  APPLICATION_PATH.'/../public'.$yaml['main']['cssExtended']['silkExtendedIconPath'];
        
        if (is_dir($dirSource)) {
            $iconName = str_replace('ui-silk-', "", $class);
            
            $iconSourcePublic = $dirSource.'/'.$iconName.'.png';
            
            if (file_exists($iconSourcePublic)) {
                $iconSource = $iconSourcePublic;
            }
        }
        
        if (file_exists($iconSource)) {
            return self::base64($iconSource);
        }
        
        return self::base64(dirname(__FILE__).'/../../icons/accept.png');
    }
    
    protected function base64($path) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        
        
        return $base64;
    }
}