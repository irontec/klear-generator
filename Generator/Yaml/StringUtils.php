<?php
class Generator_Yaml_StringUtils
{
    public static function toCamelCase($name)
    {
        return preg_replace('/_(\w)/e', "strtoupper('\\1')", $name);
    }

    public static function getMapperName($table, $namespace)
    {
        $class = array(
            $namespace,
            'Mapper',
            'Sql',
            ucfirst(self::toCamelCase($table))
        );

        return '\\' . implode('\\', $class);
    }

    public static function getModelName($table, $namespace)
    {
        $class = array(
            $namespace,
            'Model',
            ucfirst(self::toCamelCase($table))
        );

        return '\\' . implode('\\', $class);
    }
}