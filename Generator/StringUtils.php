<?php
class Generator_StringUtils
{

    public static function toCamelCase($name, $type = 'lower')
    {

        $camelCasedName = preg_replace_callback(
            '/[_|-](\w)/',
            function ($m) {
                return strtoupper($m[1]);
            },
            $name
        );

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

/*
1) Look at the last 2 letters in word, if last letter is "S" and 2nd to last letter is NOT "S", trim off "S"
2) Look at end of word for "VES", if found, replace with "F"
3) Look at end of word for "OES", if found, replace with "O"
5) Look at end of word for "IES", if found, replace with "Y"
6) Look at end of word for "XES", if found, replace with "X"
 */

    public static function getPlural($string)
    {
        $lastLetters = substr($string, -1);

        $substitute = null;

        switch ($lastLetters) {
            case 'f':
                $substitute = 'ves';
                break;
            case 'o':
                $substitute = 'oes';
                break;
            case 'y':
                $substitute = 'ies';
                break;
        }

        if ($substitute) {
            return substr($string, 0, -1) . $substitute;
        }

        return $string . 's';


    }


    public static function getSingular($string)
    {
        $lastLetters = substr($string, -2);

        if ($lastLetters == 'ss'||$lastLetters == 'ws') {
            return $string;
        }

        $lastLetters = substr($string, -3);
        $substitute = null;

        switch ($lastLetters) {
            case 'ves':
                $substitute = 'f';
                break;
            case 'oes':
                $substitute = 'o';
                break;
            case 'ies':
                $substitute = 'y';
                break;
            case 'xes':
                $substitute = 'x';
                break;
        }

        if ($substitute) {
            return substr($string, 0, -3) . $substitute;
        }

        $lastLetters = substr($string, -1);

        if ($lastLetters == 's') {
            return substr($string, 0, -1);
        }

        return $string;
    }

    public static function getSentenceFromCamelCase($string)
    {

        $string = preg_replace_callback(
            "/([A-Z])/",
            function ($m) {
                return strtolower($m[1]);
            },
            $string
        );

        return trim($string);

    }
}