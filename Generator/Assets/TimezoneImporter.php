<?php
class Generator_Assets_TimezoneImporter
{
    
    const INDEX_URL = 'http://www.iana.org/time-zones/repository/data/zone.tab';
    
    
    protected $_countries = array();
    
    /**
     * @var string Application Namespace
     */
    protected $_namespace;
    protected $_modelClassName;
    protected $_mapperClassName;
    
    
//     klear.timezones.table = Timezones
//     klear.timezones.tz = tz
//     klear.timezones.comment = comment
//     klear.timezones.countryTable = Countries
//     klear.timezones.externalCountryCode = code
    
    protected $_tzGetter;
    protected $_tzSetter;
    protected $_tz;
    
    protected $_tzCountryIdSetter = false;
    
    protected $_commentSetter;
    
    
    protected $_countryMapperClassName;
    protected $_countryCodename;
    protected $_countryCache = array();
    protected $_countrySetter;
    
    
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


    protected function _prepareTimezones()
    {
        $rawData = $this->_getContent(self::INDEX_URL);
        
        $lines = explode("\n", $rawData);
        $tzs = array();
        foreach($lines as $line) {
            if (preg_match("/^#/", $line)) {
                continue;
            }
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $_tmp = str_getcsv($line, "\t");
            
            if (sizeof($_tmp)< 3) {
                continue;
            }
            
            $tz = array();
            $tz['countryCode'] = $_tmp[0];
            $tz['tz'] = $_tmp[2];
            if (sizeof($_tmp) == 4) {
                $tz['comment'] = $_tmp[3];
            } else {
                $tz['comment'] = NULL;
            }
            $tzs[] = $tz;
        }

        return $tzs;
        
        
    }
    
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }
    
    protected function _getSetterName($model, $field)
    {
        return 'set' . ucfirst($model->varNameToColumn($field));
    }
    
    protected function _getGetterName($model, $field)
    {
    	return 'get' . ucfirst($model->varNameToColumn($field));
    }
    
    public function setConfig(Zend_Config $config)
    {
        

        //     klear.timezones.table = Timezones
        //     klear.timezones.tz = tz
        //     klear.timezones.comment = comment
        //     klear.timezones.countryTable = Countries
        //     klear.timezones.externalCountryCode = code
        
        $tzConfig = $config->timezones;

        if (!isset($tzConfig->table)) {
            throw new Exception('No table name found in timezones config (klear.ini)');
        }
        
        if (!isset($tzConfig->tz)) {
            throw new Exception('No field tz found in timezones config (klear.ini)');
        }
        
        $this->_mapperClassName = '\\' . $this->_namespace . '\\Mapper\\Sql\\' . ucfirst($tzConfig->table);
        $this->_modelClassName = '\\' . $this->_namespace . '\\Model\\' . ucfirst($tzConfig->table);
        
        $className = $this->_modelClassName;
        $model = new $className;
        
        $this->_tzGetter = $this->_getGetterName($model, $tzConfig->tz);
        $this->_tzSetter = $this->_getSetterName($model, $tzConfig->tz);
        $this->_tz = $tzConfig->tz;
        $this->_commentSetter = $this->_getSetterName($model, $tzConfig->comment);
        

        if (isset($tzConfig->countryLink)) {
        
            $this->_tzCountryIdSetter = $this->_getSetterName($model, $tzConfig->countryLink);
            if (!isset($config->countries)) {
                throw new Exception('No countries config found and tz.countrLink is present (klear.ini)');
            }
        
            $this->_parseCountryConfig($config->countries);
            
        
        }
        
    }
    
    protected function _parseCountryConfig(Zend_Config $config)
    {
        
        if (!isset($config->table)) {
            throw new Exception('No table name found in countries config (klear.ini)');
        }
        
        
        if (!isset($config->code)) {
            throw new Exception('No code name found in countries config (klear.ini)');
        }

        $this->_countryMapperClassName = '\\' . $this->_namespace . '\\Mapper\\Sql\\' . ucfirst($config->table);
        $this->_countryCodename = $config->code; 
    }
  
    
  
    
    public function parseAll()
    {
        
        $mapperName = $this->_mapperClassName;
        $className = $this->_modelClassName;
        
        $mapper = new $mapperName;
        $cont = 0;
        
        $tzSetter = $this->_tzSetter;
        $commentSetter = $this->_commentSetter;
        
        
        if ($this->_tzCountryIdSetter !== false) {
            $countryMapperName = $this->_countryMapperClassName;
            $countryMapper = new $countryMapperName;
            $countryIdSetter = $this->_tzCountryIdSetter;
        }
        
        
        /*
         * Fetch and parse tz list
         */
        $tzList = $this->_prepareTimezones();
        
         
        foreach($tzList as $tz) {
            /*
             * tz = array('countryCode'=> *, 'tz'=>* ,'comment'=>*)
             */
            $cont++;
            
            $tzTempList = $mapper->fetchList(array($this->_tz . "= ?",array($tz['tz'])));
            
            if (sizeof($tzTempList) == 1) {
                $timezone = array_shift($tzTempList);
            } else {
                $timezone = new $className;
            }
            
            
            $timezone->$tzSetter($tz['tz']);
            $timezone->$commentSetter($tz['comment']);

            if ($this->_tzCountryIdSetter !== false) {
                
                $cCode = $tz['countryCode'];
                if (isset($this->_countryCache[$cCode])) {
                    
                    $curCountry = $this->_countryCache[$cCode];
                     
                } else {
                    
                    $tmpCountryList = $countryMapper->fetchList(array($this->_countryCodename . '= ?',array($cCode)));

                    if (sizeof($tmpCountryList) != 1) {
                        throw new Exception('0 or more than one countries found for code $cCode');
                    }
                    
                    $this->_countryCache[$cCode] = array_shift($tmpCountryList);
                    
                    $curCountry = $this->_countryCache[$cCode];
                            
                }
                
            }

            
            $timezone->$countryIdSetter($curCountry->getPrimaryKey());
            $timezone->save();
            $cont++;
            if ($this->_verbose) {
                echo ".";
            }
        }
        
        if ($this->_verbose) {
            echo "\n";
        }
        
        
    
        return $cont;       
        
        
    }

    
    
}