<?php
abstract class Generator_Yaml_AbstractConfig
{
    const DEFAULT_REGISTRY_KEY = 'Klear_Generator_Translate';
    
    protected $_data;
    
    protected $_translate;
    
    protected $_enabledLanguages;
    
    public function getConfig()
    {
        $data = $this->_data;

        if (isset($data['production'])) {
            $data['testing'] = array(
                '_extends' => 'production'
            );

            $data['development'] = array(
                '_extends' => 'production'
            );
        }

        if (is_null($data)) {
            $data = array();
        }

        return new Zend_Config($data);
    }
    
    protected function _loadTranslator()
    {
        if (!Zend_Registry::isRegistered(self::DEFAULT_REGISTRY_KEY)) {
            $this->_translate = new Zend_Translate(
                    array(
                            'disableNotices' => true,
                            'adapter' => 'Zend_Translate_Adapter_Gettext'
                    )
                );
            foreach ($this->_enabledLanguages as $languageIden => $languageData) {
                $translationPath = array(
                        __DIR__,
                        '..',
                        '..',
                        'languages',
                        $languageData['locale'],
                        $languageData['locale'] . '.mo'
                );
                $file = implode(DIRECTORY_SEPARATOR, $translationPath);
                if (file_exists($file)) {
                    $this->_translate->addTranslation(array(
                            'disableNotices' => true,
                            'adapter' => 'Zend_Translate_Adapter_Gettext',
                            'locale' => $languageData['locale'],
                            'content' => $file
                    ));
                } 
            }
            Zend_Registry::set(self::DEFAULT_REGISTRY_KEY, $this->_translate);
        } else {
            $this->_translate = Zend_Registry::get(self::DEFAULT_REGISTRY_KEY);
        }
    }
}