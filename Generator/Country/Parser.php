<?php
class Generator_Country_Parser
{
    
    const INDEX_URL = 'https://raw.github.com/umpirsky/country-list/master/country/icu/%lang%/country.json';
    
    
    protected $_countries = array();
    
    /**
     * @var string Application Namespace
     */
    protected $_namespace;
    protected $_modelClassName;
    protected $_mapperClassName;
    
    
    protected $_countryCode;
    
    protected $_countryNameSetter;
    protected $_countryCodeSetter;
    
    protected $_languages;
    
    protected $_verbose = false;
    
    
    public function setVerbosed()
    {
        $this->_verbose = true;
    }
    
    protected function _getContent($url)
    {
        
        $client = new Zend_Http_Client($url, array(
                'maxredirects' => 0,
                'timeout'      => 30));
        
        return $client->request()->getBody();
    }
        
    
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }
    
    protected function _getSetterName($model, $field)
    {
        return 'set' . ucfirst($model->varNameToColumn($field));
    }
    
    public function setConfig(Zend_Config $config)
    {
        if (!isset($config->table)) {
            throw new Exception('No table name found in countries config (klear.ini)');
        }
        
        if (!isset($config->name)) {
            throw new Exception('No field name found in countries config (klear.ini)');
        }
        
        if (!isset($config->code)) {
            throw new Exception('No code name found in countries config (klear.ini)');
        }
        
        $this->_mapperClassName = '\\' . $this->_namespace . '\\Mapper\\Sql\\' . ucfirst($config->table);
        $this->_modelClassName = '\\' . $this->_namespace . '\\Model\\' . ucfirst($config->table);
        
        $className = $this->_modelClassName;
        $model = new $className;
        
        
        
        $this->_countryCodeSetter = $this->_getSetterName($model, $config->code);
        $this->_countryCode = $config->code;
        
        $this->_countryNameSetter = $this->_getSetterName($model, $config->name);
            
        
    }
    
    public function setLanguages(Zend_Config $languagesConfig)
    {
        foreach($languagesConfig as $lang) {
            $this->_languages[] = $lang;
            if ($this->_verbose) {
                echo "Registering $lang.\n";
            }
        }
        
    }
    
    
    public function parseAll()
    {
        
        $mapperName = $this->_mapperClassName;
        $className = $this->_modelClassName;
        
        $mapper = new $mapperName;
        $conts = array();
        
        $countryListTmp = array();
        
        foreach ($this->_languages as $lang) {
            $url = str_replace("%lang%", $lang, self::INDEX_URL);
            
            $countryList = Zend_Json::decode($this->_getContent($url));
            
            $contAux = 0; 
            foreach($countryList as $code => $countryName) {
                $contAux++;
                $countryList = $mapper->fetchList(array($this->_countryCode . "=?",array($code)));
                
                if (sizeof($countryList) == 1) {
                    $country = array_shift($countryList);
                } else {
                    $country = new $className;
                }
                
                $codeSetter = $this->_countryCodeSetter; 
                $nameSetter = $this->_countryNameSetter;
                
                if (!$countryName || empty($countryName)) {
                    $countryName = 'undefined';
                }
                
                $country->$codeSetter($code);
                $country->$nameSetter($countryName, $lang);
                
                $country->save();
                
            }
            
            $conts[] = $contAux;
            
        }
        
        // Ordeno los contadores (en bas al idioma), en modo reverso.
        arsort($conts);        
        $max = array_shift($conts);
        return $max;       
        
        
    }

    
    
}