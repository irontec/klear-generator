<?php
class Generator_Yaml_ListConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_table;
    protected $_klearConfig;
    protected $_db;

    public function __construct($table)
    {
        $this->_table = $table;

        $normalizedTable = Generator_Yaml_StringUtils::toCamelCase($table);

        $listScreenName = lcfirst($normalizedTable) . 'List_screen';
        $newScreenName = lcfirst($normalizedTable) . 'New_screen';
        $editScreenName = lcfirst($normalizedTable) . 'Edit_screen';
        $delDialogName = lcfirst($normalizedTable) . 'Del_dialog';

        $listScreen = array(
            'controller' => 'list',
            '<<' => '*' . ucfirst($normalizedTable),
            'title' => array(
                'i18n' => array(
                    'es' => 'Listado de ' . ucfirst($normalizedTable)
                )
            ),
            'fields' => array(
                'options' => array(
                    'title' => array(
                        'i18n' => array(
                            'es' => 'Opciones'
                        )
                    ),
                    'screens' => array(
                        $editScreenName => 'true',
                    ),
                    'dialogs' => array(
                        $delDialogName => 'true',
                    ),
                    'default' => $listScreenName
                )
            ),
            'options' => array(
                'title' => array(
                        'i18n' => array(
                                'es' => 'Opciones'
                        )
                ),
                'screens' => array(
                    $newScreenName => 'true'
                )
            )
        );

        $editScreen = array(
            '<<' => '*' . ucfirst($normalizedTable),
            'controller' => 'edit',
            'class' =>  'ui-silk-pencil',
            'label' => 'false',
            'title' => array(
                'i18n' => array(
                    'es' => 'Editar ' . ucfirst($normalizedTable)
                )
            )
        );

        $newScreen = array(
            '<<' => '*' . ucfirst($normalizedTable),
            'controller' => 'new',
            'class' =>  'ui-silk-add',
            'label' => 'true',
            'multiInstance' => 'true',
            'title' => array(
                'i18n' => array(
                    'es' => 'Añadir ' . ucfirst($normalizedTable)
                )
            )
        );

        $delDialog = array(
            '<<' => '*' . ucfirst($normalizedTable),
            'controller' => 'delete',
            'class' => 'ui-silk-bin',
            'labelOption' => 'false',
            'title' => array(
                'i18n' => array(
                    'es' => 'Eliminar ' . ucfirst($normalizedTable)
                )
            ),
            'description' => array(
                    'i18n' => array(
                            'es' => '¿Está seguro que desea eliminar este ' . ucfirst($normalizedTable) . '?'
                    )
            ),

        );

        $data['main']['module'] = 'klearMatrix';
        $data['defaultScreen'] = $listScreenName;

        $data['screens'] = array(
            $listScreenName => $listScreen,
            $newScreenName => $newScreen,
            $editScreenName => $editScreen
        );

        $data['dialogs'] = array(
            $delDialogName => $delDialog
        );

        $this->_data['production'] = $data;
    }
}