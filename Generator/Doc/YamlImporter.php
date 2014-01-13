<?php
class Generator_Doc_YamlImporter
{
    public function index($klearRaw = false)
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
        
//         var_dump($klearYaml);
//         exit;
        
        //Copia iconos, img y css
        $fileIcon = self::full_copy(dirname(__FILE__).'/copy', $folderKlearYaml . '/doc');
        
        //Copia el logo del proyecto al klear y RETURN Nombre del logo
        $fileLogo = self::cpLogo($klearYaml,$folderDocPath);
        
        //Coger todos los list de los yaml klear.yaml
        $list = self::projectYaml($klearYaml,$klearRaw);
        
        //Coger todas las entidades de los list
        $entities = self::getEntitiesYaml($list, $folderKlearYaml, $klearRaw);
        
        $view = new Zend_View();
        $view->assign('klear', $klearYaml);
        $view->assign('logo',$fileLogo);
        $view->assign('list',$list);
        $view->assign('entity',$entities);
        $view->assign('path',$folderKlearYaml);
        $view->setBasePath(dirname(__FILE__) . '/views');
        
        $html = fopen( $folderDocPath .'/tutorial.html',"w+") or die("Problemas en la creacion");
        fputs($html, $view->render('tutorial.phtml'));
        fclose($html);
        
//         var_dump($klearYaml['main']['sitename']);
        
//         exit;
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
    
    protected function cpLogo($klearYaml,$folderDocPath) {
        if ($klearYaml['main']['logo'] != 'klear/images/klear.png') {
            
            $publicImg = $klearYaml['main']['logo'];
            
            $pieces = explode("/", $publicImg);
            
            if (count($pieces) > 1) {
                $nameFile = end($pieces);
            } else {
                $nameFile = $publicImg;
            }
            if (file_exists(APPLICATION_PATH.'/../public/'.$publicImg)) {
                
                if (!file_exists($folderDocPath.'/img/'.$nameFile)) {
                    $copy = copy(APPLICATION_PATH.'/../public/'.$publicImg,$folderDocPath.'/img/'.$nameFile);
                
                    if (!$copy) {
                        echo 'Falta permisos en la carpeta doc/img';
                        exit;
                    }
                }
            }
            
            return $nameFile;
            
        } else {
            
            $logoSource = dirname(__FILE__).'/logo/klear.png';
            $logoDestiny = $folderDocPath.'/img/klear.png';
            
            if (!file_exists($logoDestiny) && file_exists($logoSource)) {
                $copyLogo = copy($logoSource,$logoDestiny);
                
                if (!$copyLogo) {
                    echo 'No se pudo copiar el logo';
                    exit;
                }
            }
            
            return 'klear.png';
        }
        
        return false;
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
    
    protected function getEntitiesYaml($list,$folderKlear,$klearRaw) {
        
        $directory = opendir($folderKlear); //ruta actual
        $model = array();
        
        while ($file = readdir($directory)) {
            
            $pos = strpos($file,'List.yaml');
            
            if (!is_dir($folderKlear.'/'.$file) && is_int($pos)) {
                
                $pieces = explode(".", $file);
                
                $yaml = $pieces[0];
                
                if (!$klearRaw) {
                    $array = self::getYaml($file);
                } else {
                    $array = self::getYamlRaw($file);
                }
                
                $principal = $array['main']['defaultScreen'];
                
                if ($array['screens'][$principal]['controller'] == 'list') {
                    $yamlModel = $array['screens'][$principal]["modelFile"];
                    
                    if (!$klearRaw) {
                        $model[$yaml] = self::getYaml('/model/'.$yamlModel.'.yaml');
                    } else {
                        $model[$yaml] = self::getYamlRaw('/model/'.$yamlModel.'.yaml');
                    }
                }
            }
        }
        
        return $model;
    }
}