<?php
class Generator_Yaml_MappersFile extends Generator_Yaml_AbstractConfig
{
    public function __construct($tableList, $namespace)
    {
        $mappers = array();
        foreach ($tableList as $table) {
            $camelCaseTable = ucfirst(Generator_Yaml_StringUtils::toCamelCase($table));
            $mapper = array(
                $namespace,
                'Mapper',
                'Sql',
                $camelCaseTable
            );

            $declaration = array(
                 'mapper: ' . Generator_Yaml_StringUtils::getMapperName($table, $namespace),
                 'modelFile: ' . $camelCaseTable
            );

            $mappers[] = '&' . $camelCaseTable . ' {' . implode(', ', $declaration) . '}';
        }
        $this->_data['mappers'] = $mappers;
    }
}