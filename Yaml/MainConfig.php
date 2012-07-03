<?php
class Yaml_MainConfig extends Yaml_AbstractConfig
{
    public function __construct()
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
//                 'adapter' => 'App_Auth_Adapter',
//                 'title' => array(
//                         'i18n' => array(
//                                 'es' => 'Acceso restringido'
//                         )
//                 ),
//                 'description' => array(
//                         'i18n' => array(
//                                 'es' => 'Introduce tu usuario de administrador de empresa'
//                         )
//                 )
        );

        $this->_data = array(
            'production' => array(
                'main' => $data
            )
        );
    }
}