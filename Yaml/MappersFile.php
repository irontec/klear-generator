<?php
class Yaml_MappersFile extends Yaml_AbstractConfig
{
    public function __construct($tableList, $namespace)
    {
        foreach ($tableList as $table) {
            $camelCaseTable = Yaml_StringUtils::toCamelCase($table);
            $mapper = array(
                $namespace,
                'Mapper',
                'Sql',
                ucfirst($camelCaseTable)
            );
            $mappers['&' . $camelCaseTable] = array (
                 'mapper' => Yaml_StringUtils::getMapperName($table, $namespace),
                 'modelFile' => ucfirst($camelCaseTable)
            );
        }
        $this->_data['mappers'] = $mappers;
    }
}