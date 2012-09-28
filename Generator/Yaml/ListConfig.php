<?php
class Generator_Yaml_ListConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_klearConfig;
    protected $_db;
    protected $_tableDescription;
    protected $_normalizedTable;

    public function __construct($table, $klearConfig)
    {
        $this->_klearConfig = $klearConfig;
        $this->_tableDescription = Generator_Db::describeTable($table);

        $normalizedTable = Generator_Yaml_StringUtils::toCamelCase($table);
        $this->_normalizedTable = $normalizedTable;

        $listScreenName = lcfirst($normalizedTable) . 'List_screen';
        $newScreenName = lcfirst($normalizedTable) . 'New_screen';
        $editScreenName = lcfirst($normalizedTable) . 'Edit_screen';
        $delDialogName = lcfirst($normalizedTable) . 'Del_dialog';

        if (isset($this->_klearConfig->klear->languages)) {
            foreach ($this->_klearConfig->klear->languages as $language) {
                $listTitles[$language] = 'List of ' . ucfirst($normalizedTable);
                $options[$language] = 'Options';
                $editTitles[$language] = 'Edit ' . ucfirst($normalizedTable);
                $addTitles[$language] = 'Add ' . ucfirst($normalizedTable);
                $deleteTitles[$language] = 'Delete ' . ucfirst($normalizedTable);
                $askDeleteTitles[$language] = 'You want to delete this ' . ucfirst($normalizedTable) . '?';
            }
        } else {
            $options = array('es' => 'Opciones');
            $listTitles = array('es' => 'Listado de ' . ucfirst($normalizedTable));
            $editTitles = array('es' => 'Editar ' . ucfirst($normalizedTable));
            $addTitles = array('es' => 'Añadir ' . ucfirst($normalizedTable));
            $deleteTitles = array('es' => 'Eliminar ' . ucfirst($normalizedTable));
            $askDeleteTitles = array('es' => '¿Está seguro que desea eliminar este ' . ucfirst($normalizedTable) . '?');
        }

        $listScreen = array(
            'controller' => 'list',
            'pagination' => array(
                    'items' => '25'
                            ),
            '<<' => '*' . ucfirst($normalizedTable),
            'title' => array(
                'i18n' => $listTitles
            ),
            'fields' => array(
                'options' => array(
                    'title' => array(
                        'i18n' => $options
                    ),
                    'screens' => array(
                        $editScreenName => 'true',
                    ),
                    'dialogs' => array(
                        $delDialogName => 'true',
                    ),
                    'default' => $editScreenName
                )
            ),
            'options' => array(
                'title' => array(
                    'i18n' => $options
                ),
                'screens' => array(
                    $newScreenName => 'true'
                )
            ),
        );

        $tableComment = Generator_Db::tableComment($table);
        if (stristr($tableComment, '[csv]')) {
            $listScreen['csv'] = array(
                'active' => 'true',
                'filename' => $table,
                'headers' => 'true',
                'enclosure' => '"',
                'separator' => ';'
            );
            $listScreen['fields']['whitelist'] = array(
                $this->_getPrimaryKey() => 'true'
            );
        }

        $editScreen = array(
            '<<' => '*' . ucfirst($normalizedTable),
            'controller' => 'edit',
            'class' =>  'ui-silk-pencil',
            'label' => 'false',
            'title' => array(
                'i18n' => $editTitles
            )
        );


        $newScreen = array(
            '<<' => '*' . ucfirst($normalizedTable),
            'controller' => 'new',
            'class' =>  'ui-silk-add',
            'label' => 'true',
            'multiInstance' => 'true',
            'title' => array(
                'i18n' => $addTitles
            )
        );

        $delDialog = array(
            '<<' => '*' . ucfirst($normalizedTable),
            'controller' => 'delete',
            'class' => 'ui-silk-bin',
            'labelOption' => 'false',
            'title' => array(
                'i18n' => $deleteTitles
            ),
            'description' => array(
                'i18n' => $askDeleteTitles
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

    protected function _getPrimaryKey()
    {
        foreach ($this->_tableDescription as $field) {
            if ($field->isPrimaryKey()) {
                return $field->getName();
            }
        }
    }
}