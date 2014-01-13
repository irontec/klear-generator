<?php
class Zend_View_Helper_Nogettext extends Zend_View_Helper_Abstract
{
    public function nogettext($text,$list = false)
    {
        if (!is_array($text)) {
            
            if (!isset($text)) {
                return '';
            }
            
            $string = self::gettextCheck($text);
            
            return self::detectParent($string,$list);
            
        } else {
            return self::inArray($text);
        }
    }
    
    protected function gettextCheck($string)
    {
        $validFunctions = array("ngettext","_");
    
        $string = trim($string);
    
        //Detectamos que el literal no esté contenido en una de las funciones gettext
    
        $quotedValidFunctions = array();
        foreach ($validFunctions as $funcName) {
            $quotedValidFunctions[] = '' . preg_quote($funcName . '(', "/") . '';
        }
    
        if (!preg_match("/^".implode("|", $quotedValidFunctions)."/i", $string)) {
    
            return $string;
        }
        
    
        $tokens = token_get_all("<?php ".$string." ?>");
    
        $literal = '';
        $opened = 0;
        $curFunction = false;
        $arguments = array();
    
        $totalTokens = sizeof($tokens);
        
        foreach ($tokens as $idToken => $token) {
            if (is_string($token)) {
                switch($token) {
                    case ')':
                        $opened -=1;
                        if ($opened == 0) {
                            break 2;
                        }
    
                        break;
    
                    case '(':
                        $opened +=1;
                        break;
    
                }
                continue;
            }
    
            switch(token_name($token[0])) {
                case 'T_STRING':
    
                    // Nombre de función
                    if (in_array($token[1], $validFunctions)) {
    
                        if ($curFunction === false) {
                            $curFunction = $token[1];
                            $arguments = array();
                        } else {
    
                            $newString = '';
    
                            for ($j = $idToken;$j<$totalTokens;$j++) {
                                if (is_string($tokens[$j])) {
    
                                    $newString .= $tokens[$j];
                                } else {
                                    $newString .= $tokens[$j][1];
                                }
                            }
    
                            $arguments[] = self::gettextCheck($newString);
                        }
    
                    } else {
                        throw new Exception("Invalid gettext string:" . $string . ' ' . $token);
                    }
    
                    break;
                case 'T_CONSTANT_ENCAPSED_STRING':
                case 'T_LNUMBER':
                case 'T_DNUMBER':
                    // Literal string (con comillas o espacios en blanco)
                    // Literal número
                    if (($curFunction === false) || ($opened == 0)) {
                        Throw new Exception("Invalid gettext string");
                    }
                    if ($opened == 0) {
                        $literal .= $token[1];
                    }
                    if ($opened == 1) {
                        $arguments[] = preg_replace("|^[\'\"](.*)[\'\"]$|", "$1", $token[1]);
                    }
    
                    break;
                case 'T_WHITESPACE':
                    $literal .= $token[1];
                    break;
    
            }
    
        }

//         if (class_exists('Iron_Translate_Adapter_GettextKlear')) {
//             $translator = new Iron_Translate_Adapter_GettextKlear;
            
//         } else {
//             echo 'No se encontró la clase Iron_Translate_Adapter_GettextKlear';
//         }
        $locale = self::langDefault();
        
        $translate = new Zend_Translate(
                array(
                        'adapter'=>'gettext',
                        'content' => APPLICATION_PATH.'/languages/'.$locale.'/'.$locale.'.mo',
                        'locale' => $locale,
                        'disableNotices' => true,
                        'clear' =>true,
                        'reload'=>true,
                )
        );

        switch($curFunction) {
            case "_":
                if (sizeof($arguments) > 1) {
                    return @sprintf($translate->getAdapter()->_($arguments[0]), $translate->getAdapter()->_($arguments[1]));
//                     return call_user_func_array("sprintf", $arguments);
                } else {
                    return $arguments[0];
                }
                break;
    
            case "ngettext":
                return call_user_func_array(array($translate, 'plural'), $arguments);
                break;
        }
    
        Throw Exception("Invalid function in gettext");
    }
    
    protected function inArray($arrayText) {
        
        if ($arrayText['i18n']) {
            return self::gettextCheck(current($arrayText['i18n']));
        } else {
            return 'false';
        }
        
    }
    
    protected function detectParent($string,$list) {
        $pos = strpos($string,'%parent%');
        
        if ($list && is_int($pos)) {
            $principal = $list['main']['defaultScreen'];
            
            $parent = self::gettextCheck('_("'.$list['screens'][$principal]['modelFile'].'")');
            
            $string = str_replace('%parent%', $parent, $string);
            
        }
        
        return $string;
    }
    
    protected function langDefault() {
        $klearYaml = Generator_Doc_YamlImporter::getYaml('klear.yaml');
        
        $lang = $klearYaml['main']['lang'];
        
        return $klearYaml['main']['langs'][$lang]['locale'];
    }
    
}