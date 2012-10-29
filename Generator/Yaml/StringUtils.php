<?php
class Generator_Yaml_StringUtils
{
    public static function toCamelCase($name, $type = 'lower')
    {
        $camelCasedName = preg_replace('/[_|-](\w)/e', "strtoupper('\\1')", $name);
        if ($type == 'upper') {
            return ucfirst($camelCasedName);
        }
        return $camelCasedName;
    }

    public static function getMapperName($table, $namespace)
    {
        $class = array(
            $namespace,
            'Mapper',
            'Sql',
            self::toCamelCase($table, 'upper')
        );

        return '\\' . implode('\\', $class);
    }

    public static function getModelName($table, $namespace)
    {
        $class = array(
            $namespace,
            'Model',
            self::toCamelCase($table, 'upper')
        );

        return '\\' . implode('\\', $class);
    }
}