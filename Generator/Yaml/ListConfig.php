<?php
class Generator_Yaml_ListConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_klearConfig;
    protected $_db;
    protected $_tableDescription;
    protected $_normalizedTable;

    public function __construct($table)
    {
        $this->_tableDescription = Generator_Db::describeTable($table);

        $normalizedTable = Generator_Yaml_StringUtils::toCamelCase($table);
        $this->_normalizedTable = $normalizedTable;

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


        // Add blacklists
        $newBlackList = $this->_getNewBlackList();
        if (sizeof($newBlackList) > 0) {
            $newScreen['fields'] = array(
                'blacklist' => $newBlackList
            );
        }
        $editBlackList = $this->_getEditBlackList();
        if (sizeof($editBlackList) > 0) {
            $editScreen['fields'] = array(
                    'blacklist' => $editBlackList
            );
        }

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

        if ($this->_hasFsoFields()) {
            $data['commands'] = $this->_getFsoFieldCommands();
        }

        $this->_data['production'] = $data;
    }

    protected function _getNewBlackList()
    {
        $blacklist = array();
        foreach ($this->_tableDescription as $field) {
            if (
                $field->getType() == 'timestamp' && $field->getDefaultValue() == 'CURRENT_TIMESTAMP'
                || $field->isUrlIdentifier()
            ) {
                $blacklist[$field->getName()] = 'true';
            }
        }
        return $blacklist;
    }

    protected function _getEditBlackList()
    {
        $blacklist = array();
        foreach ($this->_tableDescription as $field) {
            if ($field->getType() == 'timestamp' && $field->getDefaultValue() == 'CURRENT_TIMESTAMP') {
                $blacklist[$field->getName()] = 'true';
            }
        }
        return $blacklist;
    }

    protected function _hasFsoFields()
    {
        foreach ($this->_tableDescription as $field) {
            if ($field->isFso()) {
                return true;
            }
        }
    }

    protected function _getFsoFieldCommands()
    {
        $fsoFields = $this->_getFsoFields();
        $data = array();
        foreach ($fsoFields as $fieldName) {
            $data[ucfirst($fieldName) . 'Download_command'] = array(
                '<<' => '*' . ucfirst($this->_normalizedTable),
                'controller' => 'File',
                'action' => 'force-download',
                'mainColumn' =>  $fieldName
            );
            $data[ucfirst($fieldName) . 'Upload_command'] = array(
                '<<' => '*' . ucfirst($this->_normalizedTable),
                'controller' => 'File',
                'action' => 'upload',
                'mainColumn' =>  $fieldName
            );
        }
        return $data;
    }

    protected function _getFsoFields()
    {
        $fields = array();
        foreach ($this->_tableDescription as $field) {
            if ($field->isFso()) {
                if (preg_match('/^(?P<fieldname>.*)FileSize$/', $field->getName(), $matches)) {
                    $fields[] = $matches['fieldname'];
                }
            }
        }
        return $fields;
    }

}