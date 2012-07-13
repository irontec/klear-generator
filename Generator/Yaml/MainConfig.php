<?php
class Generator_Yaml_MainConfig extends Generator_Yaml_AbstractConfig
{
    public function __construct($entities = array())
    {
        $data = array();
        $data['log'] = array(
                'writerName' => 'Null',
                'writerParams' => array()
        );


        $data['sitename'] = 'MyApp';
        $data['logo'] = 'images/logo.png';
        $data['year'] = date('Y');

        $data['lang'] = 'Espanol';
        $data['langs'] = array(
                'Espanol' => array(
                        'title' => 'Español',
                        'language' => 'es',
                        'locale' => 'es_ES'
                )
        );

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
                'adapter' => 'Klear_Auth_Adapter_Basic',
                'title' => array(
                        'i18n' => array(
                                'es' => 'Acceso restringido'
                        )
                ),
                'description' => array(
                        'i18n' => array(
                                'es' => 'Introduce tu usuario'
                        )
                )
        );

        $data['timezone'] = 'Europe/Madrid';

        $entitiesConfig = array();
        foreach ($entities as $entity) {
            $normalizedEntity = ucfirst(Generator_Yaml_StringUtils::toCamelCase($entity));
            $entitiesConfig[$normalizedEntity . 'List'] = array(
                'title' => array(
                    'i18n' => array(
                        'es' => 'Listado de ' . $normalizedEntity
                    )
                ),
                'class' => 'ui-silk-user-suit',
                'description' => array(
                    'i18n' => array(
                        'es' => 'Listado de ' . $normalizedEntity
                    )
                )
            );
        }

        $menu['General'] = array(
                'title' => array(
                    'i18n' => array(
                        'es' => 'Gestión general'
                    )
                ),
                'description' => array(
                    'i18n' => array(
                        'es' => 'Gestión general'
                    )
                ),
                'submenus' => $entitiesConfig
        );

        $this->_data = array(
            'production' => array(
                'main' => $data,
                'menu' => $menu
            )
        );

    }
}