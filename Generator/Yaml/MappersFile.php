<?php
class Generator_Yaml_MappersFile extends Generator_Yaml_AbstractConfig
{
    public function __construct($tableList, $namespace)
    {
        foreach ($tableList as $table) {
            $camelCaseTable = Generator_Yaml_StringUtils::toCamelCase($table);
            $mapper = array(
                $namespace,
                'Mapper',
                'Sql',
                ucfirst($camelCaseTable)
            );
            $mappers['&' . $camelCaseTable] = array (
                 'mapper' => Generator_Yaml_StringUtils::getMapperName($table, $namespace),
                 'modelFile' => ucfirst($camelCaseTable)
            );
        }
        $this->_data['mappers'] = $mappers;
    }
}