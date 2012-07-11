<?php
class Generator_Yaml_MappersFile extends Generator_Yaml_AbstractConfig
{
    public function __construct($tableList, $namespace)
    {
        $mappers = array();
        foreach ($tableList as $table) {
            $camelCaseTable = Generator_Yaml_StringUtils::toCamelCase($table);
            $mapper = array(
                $namespace,
                'Mapper',
                'Sql',
                ucfirst($camelCaseTable)
            );

            $declaration = array(
                 'mapper: ' . Generator_Yaml_StringUtils::getMapperName($table, $namespace),
                 'modelFile: ' . ucfirst($camelCaseTable)
            );

            $mappers[] = '&' . $camelCaseTable . ' {' . implode(', ', $declaration) . '}';
        }
        $this->_data['mappers'] = $mappers;
    }
}