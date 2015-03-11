<?php
class Generator_Yaml_ListConfig extends Generator_Yaml_AbstractConfig
{
    protected $_namespace;
    protected $_dependantTables = array();
    protected $_klearConfig;
    protected $_db;
    protected $_tableDescription;
    protected $_normalizedTable;

    public function __construct($table, $dependantTables, $klearConfig, $enabledLanguages = array())
    {
        $this->_enabledLanguages = $enabledLanguages;
        $this->_dependantTables = $dependantTables;
        $this->_loadTranslator();

        $this->_klearConfig = $klearConfig;
        $this->_tableDescription = Generator_Db::describeTable($table);

        $normalizedTable = ucfirst(Generator_StringUtils::toCamelCase($table));
        $this->_normalizedTable = $normalizedTable;

        $listScreenName = lcfirst($normalizedTable) . 'List_screen';
        $newScreenName = lcfirst($normalizedTable) . 'New_screen';
        $editScreenName = lcfirst($normalizedTable) . 'Edit_screen';
        $delDialogName = lcfirst($normalizedTable) . 'Del_dialog';

        $listTitles = $editTitles = $addTitles = $deleteTitles = $askDeleteTitles = $messageDeleteTitles = array();

        $normalizedEntity = $normalizedTable;

        $pluralEntity = ucfirst(Generator_StringUtils::getSentenceFromCamelCase($normalizedEntity));

        $singularEntity = Generator_StringUtils::getSingular($normalizedEntity);
        $singularEntity = ucfirst(Generator_StringUtils::getSentenceFromCamelCase($singularEntity));

        if ($singularEntity == $pluralEntity) {
            $pluralEntity = $pluralEntity . '(s)';
        }

        $titleSingular = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 1)";
        $titlePlural = "ngettext('" . $singularEntity . "', '" . $pluralEntity . "', 0)";

        $options = array();

        $listTitles = '_("List of %s %2s", ' . $titlePlural . ', "[format| (%parent%)]")';
        $editTitles = '_("Edit %s %2s", ' . $titleSingular . ', "[format| (%item%)]")';
        $addTitles = '_("Add %s", ' . $titleSingular . ', "[format| (%parent%)]")';
        $deleteTitles = '_("Delete %s", ' . $titleSingular . ')';
        $askDeleteTitles = '_("Do you want to delete this %s?", ' . $titleSingular . ')';
        $messageDeleteTitles =  '_("%s successfully deleted.", ' . $titleSingular . ')';
        $options = '_("Options")';


        $listScreen = array(
            '&' => lcfirst($this->_normalizedTable) . 'List_screenLink',
            'controller' => 'list',
            'pagination' => array(
                    'items' => '25'
                            ),
            '<<' => '*' . $normalizedTable,
            'class' => 'ui-silk-text-list-bullets',
            'title' => $listTitles,
            'fields' => array(
                '&' => lcfirst($this->_normalizedTable) . '_fieldsLink',
                'options' => array(
                    'title' => $options,
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
                'title' => $options,
                'screens' => array(
                    $newScreenName => 'true'
                ),
                'dialogs' => array(
                    $delDialogName => 'true',
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
            '&' => lcfirst($this->_normalizedTable) . 'Edit_screenLink', 
            '<<' => '*' . $normalizedTable,
            'controller' => 'edit',
            'class' =>  'ui-silk-pencil',
            'label' => 'false',
            'title' => $editTitles
        );

        $newScreen = array(
            '&' => lcfirst($this->_normalizedTable) . 'New_screenLink', 
            '<<' => '*' . $normalizedTable,
            'controller' => 'new',
            'class' =>  'ui-silk-add',
            'label' => 'true',
            'multiInstance' => 'true',
            'title' => $addTitles,
            'shortcutOption' => 'N'
        );

        $delDialog = array(
            '&' => lcfirst($this->_normalizedTable) . 'Del_dialogLink',
            '<<' => '*' . $normalizedTable,
            'controller' => 'delete',
            'class' => 'ui-silk-bin',
            'labelOption' => 'false',
            'title' => $deleteTitles,
            'description' => $askDeleteTitles,
            'message' => $messageDeleteTitles,
            'multiItem' => true,
            'labelOnList' => true
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
        $data['main']['defaultScreen'] = $listScreenName;

        foreach ($this->_dependantTables as $relatedTableName => $relationFld) {
            $screenName = lcfirst($relatedTableName) . 'List_screen'; 
            $listScreen['fields']['options']['screens'][$screenName] = 'true'; 
        }

        $screens = array(
            '&' => lcfirst($this->_normalizedTable) . '_screensLink',
            $listScreenName => $listScreen,
            $newScreenName => $newScreen,
            $editScreenName => $editScreen
        );
        $data['screens'] = $screens + $this->_getDependantScreens();

        $dialogs = array(
            '&' => lcfirst($this->_normalizedTable) . '_dialogsLink',
            $delDialogName => $delDialog
        );
        $data['dialogs'] = $dialogs;
        $data +=  $this->_getDependantDialogs();

        if ($this->_hasFsoFields()) {

            $commandslink = array(
                '&' => lcfirst($this->_normalizedTable) . '_commandsLink'
            );
            
            $commands = $commandslink + $this->_getFsoFieldCommands($table);
            $data['commands'] = $commands;
        }

        $this->_data['production'] = $data;
    }

    protected function _getDependantScreens() {
        
        $screens = array();
        foreach ($this->_dependantTables as $table => $filterFld) {
            
            $lcTableName = lcfirst($table);

            $screens += array(
                '#' . $lcTableName => '',
                '<<' => '*'. $lcTableName .'_screensLink',
                //List
                $lcTableName . 'List_screen' => array(
                    '<<' => '*'. $lcTableName .'List_screenLink',
                    'filterField' => $filterFld,
                    'parentOptionCustomizer'=> array(
                       'recordCount'
                   )
                ),
                //New
                $lcTableName . 'New_screen' => array(
                    '<<' => '*'. $lcTableName .'New_screenLink',
                    'filterField' => $filterFld,
                ),
                //Edit
                $lcTableName . 'Edit_screen' => array(
                    '<<' => '*'. $lcTableName .'Edit_screenLink',
                    'filterField' => $filterFld . "\n",
                ),                
            );
        }
        
        return $screens;
    }

    protected function _getDependantDialogs() 
    {
        $dialogs = array();
        foreach ($this->_dependantTables as $table => $fld) {

            $lcTableName = lcfirst($table);

            $dialogs += array(
                '# ' . $lcTableName . ' dialogs'=> array(
                    '<<' => '*'. $lcTableName .'_dialogsLink',
                )
            );
        }

        return $dialogs;
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

    protected function _getFsoFieldCommands($table)
    {
        $fsoFields = $this->_getFsoFields();
        $data = array();
        foreach ($fsoFields as $fieldName) {
            $data[lcfirst($table) . ucfirst($fieldName) . 'Download_command'] = array(
                '&' => lcfirst($table) . ucfirst($fieldName) . 'Download_commandLink',
                '<<' => '*' . $this->_normalizedTable,
                'controller' => 'File',
                'action' => 'force-download',
                'mainColumn' =>  $fieldName
            );
            $data[lcfirst($table) . ucfirst($fieldName) . 'Upload_command'] = array(
                '&' => lcfirst($table) . ucfirst($fieldName) . 'Upload_commandLink',
                '<<' => '*' . $this->_normalizedTable,
                'controller' => 'File',
                'action' => 'upload',
                'mainColumn' =>  $fieldName
            );
            $data[lcfirst($table) . ucfirst($fieldName) . 'Preview_command'] = array(
                '&' => lcfirst($table) . ucfirst($fieldName) . 'Preview_commandLink',
                '<<' => '*' . $this->_normalizedTable,
                'controller' => 'File',
                'action' => 'preview',
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
