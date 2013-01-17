<?php
class Generator_Yaml_ListConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_klearConfig;
    protected $_db;
    protected $_tableDescription;
    protected $_normalizedTable;

    public function __construct($table, $klearConfig, $enabledLanguages = array())
    {
        $this->_enabledLanguages = $enabledLanguages;

        $this->_loadTranslator();

        $this->_klearConfig = $klearConfig;
        $this->_tableDescription = Generator_Db::describeTable($table);

        $normalizedTable = ucfirst(Generator_Yaml_StringUtils::toCamelCase($table));
        $this->_normalizedTable = $normalizedTable;

        $listScreenName = lcfirst($normalizedTable) . 'List_screen';
        $newScreenName = lcfirst($normalizedTable) . 'New_screen';
        $editScreenName = lcfirst($normalizedTable) . 'Edit_screen';
        $delDialogName = lcfirst($normalizedTable) . 'Del_dialog';

        $listTitles = $editTitles = $addTitles = $deleteTitles = $askDeleteTitles = array();

        $normalizedEntity = $normalizedTable;

        $pluralEntity = ucfirst(Generator_Yaml_StringUtils::getSentenceFromCamelCase($normalizedEntity));

        $singularEntity = Generator_Yaml_StringUtils::getSingular($normalizedEntity);
        $singularEntity = ucfirst(Generator_Yaml_StringUtils::getSentenceFromCamelCase($singularEntity));

        if ($singularEntity == $pluralEntity) {
            $pluralEntity = $pluralEntity . '(s)';
        }

        $titleSingular = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 1)";
        $titlePlural = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 0)";

        $options = array();

        foreach ($this->_enabledLanguages as $languageIden => $languageData) {
            $this->_translate->setLocale($languageData['locale']);
            $listTitles[$languageData['language']] = sprintf($this->_translate->translate('List of %s'), $titlePlural);
            $editTitles[$languageData['language']] = sprintf($this->_translate->translate('Edit %s'), $titleSingular);
            $addTitles[$languageData['language']] = sprintf($this->_translate->translate('Add %s'), $titleSingular);
            $deleteTitles[$languageData['language']] = sprintf($this->_translate->translate('Delete %s'), $titleSingular);
            $askDeleteTitles[$languageData['language']] = sprintf($this->_translate->translate('You want to delete this %s?'), $titleSingular);
            $options[$languageData['language']] = $this->_translate->translate('Options');
        }

        $listScreen = array(
            'controller' => 'list',
            'pagination' => array(
                    'items' => '25'
                            ),
            '<<' => '*' . $normalizedTable,
            'title' => array(
                'i18n' => $listTitles
            ),
            'fields' => array(
                'options' => array(
                    'title' => array('i18n' => $options),
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
                'title' => array('i18n' => $options),
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
                'enclosure' => "'\"'",
                'separator' => "','"
            );
            $listScreen['fields']['whitelist'] = array(
                $this->_getPrimaryKey() => 'true'
            );
        }

        $editScreen = array(
            '<<' => '*' . $normalizedTable,
            'controller' => 'edit',
            'class' =>  'ui-silk-pencil',
            'label' => 'false',
            'title' => array(
                'i18n' => $editTitles
            )
        );


        $newScreen = array(
            '<<' => '*' . $normalizedTable,
            'controller' => 'new',
            'class' =>  'ui-silk-add',
            'label' => 'true',
            'multiInstance' => 'true',
            'title' => array(
                'i18n' => $addTitles
            )
        );

        $delDialog = array(
            '<<' => '*' . $normalizedTable,
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
            if ($field->isCurrentTimeStamp() || $field->isUrlIdentifier() || $field->mustBeIgnored()) {
                $blacklist[$field->getName()] = 'true';
            }
        }
        return $blacklist;
    }

    protected function _getEditBlackList()
    {
        $blacklist = array();
        foreach ($this->_tableDescription as $field) {
            if ($field->isCurrentTimeStamp()) {
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
                '<<' => '*' . $this->_normalizedTable,
                'controller' => 'File',
                'action' => 'force-download',
                'mainColumn' =>  $fieldName
            );
            $data[ucfirst($fieldName) . 'Upload_command'] = array(
                '<<' => '*' . $this->_normalizedTable,
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