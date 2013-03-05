<?php
class Generator_Yaml_MainConfig extends Generator_Yaml_AbstractConfig
{
    public function __construct($entities = array(), $enabledLanguages = array())
    {
        $this->_enabledLanguages = $enabledLanguages;
        
        $this->_loadTranslator();
        
        $data = array();
        $data['log'] = array(
            'writerName' => '"Null"',
            'writerParams' => array()
        );


        $data['sitename'] = 'MyApp';
        $data['logo'] = 'images/logo.png';
        $data['year'] = date('Y');

        
        
        $data['lang'] = 'Espanol';
        
        $data['langs'] = $this->_enabledLanguages;
        
        // $data['dynamicConfigClass'] = '';

        $data['jqueryUI'] = array(
                'theme' => 'redmond',
                //     'extraThemeFile' => './configs/klear/conf.d/jqueryui_custom_themes.yaml'
        );

        /** TODO: Normalizar rutas: a veces son relativas al application, otras al public, a veces son relativas, otras absolutas... Mal. Jaleo. **/
        $data['cssExtended'] = array(
        //     'silkExtendedIcontPath' => '/css/ui-klear/icons';
        );

        $data['actionHelpers'] = array(
//                 'MyApp_Controller_Action_Helper_Hooks'
        );

        $data['auth'] = array(
                'adapter' => 'Klear_Auth_Adapter_Basic'
        );
        
        /*foreach ($this->_enabledLanguages as $languageIden => $languageData) {
            $this->_translate->setLocale($languageData['locale']);
            $data['auth']['title']['i18n'][$languageData['language']] = $this->_translate->translate('Access denied');
            $data['auth']['description']['i18n'][$languageData['language']] = $this->_translate->translate('Insert your username');
        }*/
        
        $data['auth']['title'] = '_("Access denied")';
        $data['auth']['description'] = '_("Insert your username")';
        
        $data['timezone'] = 'Europe/Madrid';

        $entitiesConfig = array();
        foreach ($entities as $entity) {
            
            $normalizedEntity = ucfirst(Generator_Yaml_StringUtils::toCamelCase($entity));
            
            $pluralEntity = ucfirst(Generator_Yaml_StringUtils::getSentenceFromCamelCase($normalizedEntity));
            
            $singularEntity = Generator_Yaml_StringUtils::getSingular($normalizedEntity);
            $singularEntity = ucfirst(Generator_Yaml_StringUtils::getSentenceFromCamelCase($singularEntity));
                        
            /*if ($singularEntity == $pluralEntity) {
                $pluralEntity = $pluralEntity . '(s)';
            }*/
            
            /*$entitiesConfig[$normalizedEntity . 'List'] = array(
                'title' => array('i18n' => array()),
                'class' => 'ui-silk-text-list-bullets',
                'description' => array('i18n' => array())
            );
            foreach ($this->_enabledLanguages as $languageIden => $languageData) {
                $this->_translate->setLocale($languageData['locale']);
                $translateString = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 0)";
                $title = sprintf($this->_translate->translate('List of %s'), $translateString);
                $entitiesConfig[$normalizedEntity . 'List']['title']['i18n'][$languageData['language']] = $title;
                $entitiesConfig[$normalizedEntity . 'List']['description']['i18n'][$languageData['language']] = $title;
            }*/
            
            $translateString = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 0)";
            $entitiesConfig[$normalizedEntity . 'List'] = array(
                    'title' => $translateString,
                    'class' => 'ui-silk-text-list-bullets',
                    'description' => '_("List of %s", ' . $translateString . ')'
            );
        }

        
        $menu['General'] = array();
        /*foreach ($this->_enabledLanguages as $languageIden => $languageData) {
            $this->_translate->setLocale($languageData['locale']);
            $menu['General']['title']['i18n'][$languageData['language']] = $this->_translate->translate('Main management');
            $menu['General']['description']['i18n'][$languageData['language']] = $this->_translate->translate('Main management');
        }*/
        
        $menu['General']['title'] = '_("Main management")';
        
        $menu['General']['description'] = '_("Main management")';
        
        $menu['General']['submenus'] = $entitiesConfig;

        $this->_data = array(
            'production' => array(
                'main' => $data,
                'menu' => $menu
            )
        );

    }
}