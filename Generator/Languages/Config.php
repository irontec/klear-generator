<?php 

class Generator_Languages_Config
{
    
    protected $_klearConfig;
    
    protected $_availableLanguages = array(
            'es' => array(
                    'title' => 'Español',
                    'language' => 'es',
                    'locale' => 'es_ES'),
            'eu' => array(
                    'title' => 'Euskara',
                    'language' => 'eu',
                    'locale' => 'eu_ES'),
            'ca' => array(
                    'title' => 'Català',
                    'language' => 'ca',
                    'locale' => 'ca_ES'),
            'ga' => array(
                    'title' => 'Galego',
                    'language' => 'gl',
                    'locale' => 'gl_ES'),
            'en' => array(
                    'title' => 'English',
                    'language' => 'en',
                    'locale' => 'en_US'),
            'fr' => array(
                    'title' => 'Français',
                    'language' => 'fr',
                    'locale' => 'fr_FR'),
            'pt' => array(
                    'title' => 'Português',
                    'language' => 'pt',
                    'locale' => 'pt_PT')
    );
    
    protected $_enabledLanguages = array();
    
    protected $_langStoragePath;
    protected $_langProjectPath;
    
    
    public function __construct()
    {
        $this->_klearConfig = new Zend_Config_Ini(APPLICATION_PATH. '/configs/klear.ini', APPLICATION_ENV);
        $this->_loadLanguages();
        
        $this->_langStoragePath = dirname(dirname(__DIR__)) . '/languages/';
        $this->_langProjectPath = APPLICATION_PATH . '/languages/';
    }
    
    protected function _loadLanguages()
    {
        foreach ($this->_klearConfig->klear->languages as $language) {
            $result = false;
            foreach ($this->_availableLanguages as $languageIden => $languageData) {
                if ($languageData['language'] == $language) {
                    $result = true;
                    $this->_enabledLanguages[$languageIden] = $languageData;
                }
            }
            if (!$result) {
                $this->_enabledLanguages[$language] = array(
                        'title' => $language,
                        'language' => $language,
                        'locale' => $language
                );
            }
        }
    }
    
    public function getEnabledLanguages()
    {
        return $this->_enabledLanguages;
    }
    
    protected function _getLanguageFiles($lang, $langInfo)
    {
        $ret = array();
        $path = $this->_langStoragePath . $langInfo['locale'];
        $projectPath = $this->_langProjectPath . $langInfo['locale'];
        if (!is_dir($path)) {
            return null;
        }
        $ret[$path] = $projectPath;
        $poFile = $path . '/' . $langInfo['locale'] . '.po';
        $moFile = $path . '/' . $langInfo['locale'] . '.mo';
        $poProjectFile = $projectPath . '/' . $langInfo['locale'] . '.po';
        $moProjectFile = $projectPath . '/' . $langInfo['locale'] . '.mo';
        if (file_exists($poFile)) {
            $ret[$poFile] = $poProjectFile;
        }
        if (file_exists($moFile)) {
            $ret[$moFile] = $moProjectFile;
        }
        return $ret;
    }
    
    protected function _createProyectLanguageDir()
    {
        $dir = APPLICATION_PATH . '/languages';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }

    protected function _getFile($file, $languageName)
    {
        $contents = file_get_contents($file);
        
        $o = preg_replace(
                array(
                        '/(\"Project-Id-Version:\s).*(\\\n\")/',
                        '/(\"Last-Translator:\s).*(\\\n\")/',
                        '/(\"Language-Team:\s).*(\\\n\")/',
                        '/(\"X-Poedit-Basepath:\s).*(\\\n\")/',
                        '/(\"X-Poedit-Language:\s).*(\\\n\")/'
                        ),
                array(
                        '$1' . $this->_klearConfig->projectId . '$2',
                        '$1' . $this->_klearConfig->docs->email . '$2',
                        '$1' . $this->_klearConfig->docs->email . '$2',
                        '$1' . APPLICATION_PATH . '$2',
                        '$1' . $languageName . '$2'
                        ), 
                $contents);
        
        return $o;
    }
    
    protected function _createLanguageFiles()
    {
        
        $this->_createProyectLanguageDir();
        
        
        
        foreach ($this->_enabledLanguages as $key=>$info) {
            if ($files = $this->_getLanguageFiles($key, $info)) {
                foreach ($files as $generatorFile=> $projectFile) {
                    if (!file_exists($projectFile)) {
                        if (is_dir($generatorFile)) {
                            mkdir($projectFile);
                        } else {
                            $f = $this->_getFile($generatorFile, $info['title']);
                            file_put_contents($projectFile, $f);
                        }
                    }
                }
            } else {
                echo "!! No translation files for " . $info['title'] . "\n";
            }
        }
        
        
    }
    
    protected function _copyCommonStringsFile()
    {
        copy($this->_langStoragePath . 'common-strings.php' , 
                $this->_langProjectPath . 'common-strings.php');
    }
    
    public function createAllFiles()
    {
        $this->_createLanguageFiles();
        $this->_copyCommonStringsFile();
    }

}