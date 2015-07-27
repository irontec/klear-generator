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
        $data['logo'] = 'klear/images/klear.png';
        $data['year'] = date('Y');



        $data['lang'] = key($this->_enabledLanguages);

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


        $data['auth']['title'] = '_("Access denied")';
        $data['auth']['description'] = '_("Insert your username")';

        $data['timezone'] = 'Europe/Madrid';

        $entitiesConfig = array(
            "Dashboard" => array(
                "default" => "true",
                "title" => array(
                    "i18n" => array(
                        "es" => "Panel de control"
                    )    
                )
            )
        );
        foreach ($entities as $entity) {

            $normalizedEntity = ucfirst(Generator_StringUtils::toCamelCase($entity));

            $pluralEntity = ucfirst(Generator_StringUtils::getSentenceFromCamelCase($normalizedEntity));

            $singularEntity = Generator_StringUtils::getSingular($normalizedEntity);
            $singularEntity = ucfirst(Generator_StringUtils::getSentenceFromCamelCase($singularEntity));

            $translateString = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 0)";
            $entitiesConfig[$normalizedEntity . 'List'] = array(
                    'title' => $translateString,
                    'class' => 'ui-silk-text-list-bullets',
                    'description' => '_("List of %s", ' . $translateString . ')'
            );
        }


        $menu['General'] = array();

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