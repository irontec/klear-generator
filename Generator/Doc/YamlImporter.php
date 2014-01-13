<?php
class Generator_Doc_YamlImporter
{
    public function index($klearRaw = false, $pdf = false)
    {
        if (!$klearRaw) {
            $folderKlearYaml = APPLICATION_PATH. '/configs/klear';
        } else {
            $folderKlearYaml = APPLICATION_PATH. '/configs/klearRaw';
        }
        
        //Creación de los folder necesarios RETURN DIRECCIÓN FÍSICA DE LA CARPETA DOC
        $folderDocPath = self::createPath($folderKlearYaml);
        
        //Coge todas las opciones del klear.yaml
        if (!$klearRaw) {
            $klearYaml = self::getYaml('klear.yaml');
        } else {
            $klearYaml = self::getYamlRaw('klear.yaml');
        }
        
        //Coger todos los list de los yaml klear.yaml
        $lists = self::projectYaml($klearYaml,$klearRaw);
        
        //Coger todas las entidades de los list
        $entities = self::getEntitiesYaml($lists, $folderKlearYaml, $klearRaw);
        
        $fileLogo = self::cpLogo($klearYaml);
        
        $view = new Zend_View();
        $view->assign('klear', $klearYaml);
        $view->assign('logo',$fileLogo);
        $view->assign('list',$lists);
        $view->assign('entity',$entities);
        $view->assign('path',$folderKlearYaml);
        $view->setBasePath(dirname(__FILE__) . '/views');
        
        $html = fopen( $folderDocPath .'/tutorial.html',"w+") or die("Problemas en la creacion");
        fputs($html, $view->render('tutorial.phtml'));
        fclose($html);
        
        return $klearYaml['main'];
    }
    
    protected function createPath($folderKlear) {
        //Creación de la carpeta del documento DOC
        $folderDocPath = $folderKlear. '/doc';
        
        if (!file_exists($folderDocPath)) {
            mkdir($folderDocPath);
            
            //Creación de la carpeta Icons
            mkdir($folderDocPath.'/icons');
            
            //Creación de la carpeta IMG
            mkdir($folderDocPath.'/img');
            
            //Creación de la carpeta css
            mkdir($folderDocPath.'/css');
        }
        
        return $folderDocPath;
    }
    
    public function getYaml($filePath) {
        
        $yaml = new Zend_Config_Yaml(
                'klear.yaml://' . $filePath,
                APPLICATION_ENV,
                array(
                        "yamldecoder" => "yaml_parse"
                )
        );
        
        return $yaml->toArray();
    }
    
    public function getYamlRaw($filePath) {
    
        $yaml = new Zend_Config_Yaml(
                'klear.yaml:///../klearRaw/' . $filePath,
                APPLICATION_ENV,
                array(
                        "yamldecoder" => "yaml_parse"
                )
        );
    
        return $yaml->toArray();
    }
    
    protected function cpLogo($klearYaml) {
        if ($klearYaml['main']['logo'] != 'klear/images/klear.png') {
            
            $publicImg = $klearYaml['main']['logo'];
            
            $logo = APPLICATION_PATH.'/../public/'.$publicImg;
            
            if (file_exists($logo)) {
                
                return self::base64($logo);
                
            }
            
            return $nameFile;
            
        } else {
            
            $logoSource = dirname(__FILE__).'/logo/klear.png';
            
            return self::base64($logoSource);
        }
        
        return false;
    }
    
    protected function base64($path) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
        return $base64;
    }
    
    protected function full_copy( $source, $target ) {
        if ( is_dir( $source ) ) {
            @mkdir( $target );
            $d = dir( $source );
            
            while ( FALSE !== ( $entry = $d->read() ) ) {
                if ( $entry == '.' || $entry == '..' ) {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if ( is_dir( $Entry ) ) {
                    self::full_copy( $Entry, $target . '/' . $entry );
                    continue;
                }
                
                $pos = strpos($entry,'.svn-base');
                
                if (!is_int($pos)) {
                    copy( $Entry, $target . '/' . $entry );
                }
            }
            $d->close();
        } else {
            copy( $source, $target );
        }
    }
    
    protected function projectYaml($klearYaml,$klearRaw) {
        $entity = array();
        foreach ($klearYaml['menu'] as $menu) {
            foreach ($menu['submenus'] as $yamlName => $submenu) {
                if (!$klearRaw) {
                    $entity[$yamlName] = self::getYaml($yamlName.'.yaml');
                } else {
                    $entity[$yamlName] = self::getYamlRaw($yamlName.'.yaml');
                }
            }
        }
        
        return $entity;
    }
    
    protected function getEntitiesYaml($lists,$folderKlear,$klearRaw) {
        
        $model = array();
        
        foreach($lists as $key => $list) {
            $principal = @$list['main']['defaultScreen'];
            
            if (isset($principal) && ($list['screens'][$principal]["controller"] != 'dashboard')) {
                $yamlModel = $list['screens'][$principal]["modelFile"];
    
                if (!$klearRaw) {
                    $model[$key] = self::getYaml('/model/'.$yamlModel.'.yaml');
                } else {
                    $model[$key] = self::getYamlRaw('/model/'.$yamlModel.'.yaml');
                }
            }
        }
        
        return $model;
        
        
//         $directory = opendir($folderKlear); //ruta actual
//         $model = array();
        
//         while ($file = readdir($directory)) {
            
//             $pos = strpos($file,'List.yaml');
            
//             if (!is_dir($folderKlear.'/'.$file) && is_int($pos)) {
                
//                 $pieces = explode(".", $file);
                
//                 $yaml = $pieces[0];
                
//                 if (!$klearRaw) {
//                     $array = self::getYaml($file);
//                 } else {
//                     $array = self::getYamlRaw($file);
//                 }
                
//                 $principal = $array['main']['defaultScreen'];
                
//                 if ($array['screens'][$principal]['controller'] == 'list') {
//                     $yamlModel = $array['screens'][$principal]["modelFile"];
                    
//                     if (!$klearRaw) {
//                         $model[$yaml] = self::getYaml('/model/'.$yamlModel.'.yaml');
//                     } else {
//                         $model[$yaml] = self::getYamlRaw('/model/'.$yamlModel.'.yaml');
//                     }
//                 }
//             }
//         }
        
//         return $model;
    }
}