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
        $klearYaml = self::getYaml($folderKlearYaml.'/klear.yaml');
        
        //Copia el logo del proyecto al klear y RETURN Nombre del logo
        $fileLogo = self::cpLogo($klearYaml,$folderDocPath);
        
        //Copia iconos
        //$fileIcon = self::cpIcon($klearYaml,$folderDocPath);
        
        //Coger todos los list de los yaml klear.yaml
        $list = self::projectYaml($klearYaml,$folderKlearYaml);
        
        //Coger todas las entidades de los list
        $entities = self::getEntitiesYaml($list, $folderKlearYaml);
        
        $view = new Zend_View();
        $view->assign('klear', $klearYaml);
        $view->assign('logo',$fileLogo);
        $view->assign('list',$list);
        $view->assign('entity',$entities);
        $view->setBasePath(dirname(__FILE__) . '/views');
        
        $html = fopen( $folderDocPath .'/tutorial.html',"w+") or die("Problemas en la creacion");
        fputs($html, $view->render('tutorial.phtml'));
        fclose($html);
        
        var_dump($klearYaml['main']['sitename']);
        
        exit;
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
        }
        
        return $folderDocPath;
    }
    
    protected function getYaml($filePath) {
        $yaml = new Zend_Config_Yaml(
                $filePath,
                APPLICATION_ENV,
                array(
                        "yamldecoder" => "yaml_parse"
                )
        );
        
        return $yaml->toArray();
    }
    
    protected function cpLogo($klearYaml,$folderDocPath) {
        $publicImg = $klearYaml['main']['logo'];
        
        $pieces = explode("/", $publicImg);
        
        if (count($pieces) > 1) {
            $nameFile = end($pieces);
        } else {
            $nameFile = $publicImg;
        }
        if (file_exists(APPLICATION_PATH.'/../public/'.$publicImg)) {
            
            $copy = copy(APPLICATION_PATH.'/../public/'.$publicImg,$folderDocPath.'/img/'.$nameFile);
        
            if (!$copy) {
                echo 'Falta permisos en la carpeta doc/img';
                exit;
            }
        }
        
        return $nameFile;
    }
    
    protected function cpIcon($klearYaml,$folderDocPath) {
        foreach ($klearYaml['menu'] as $menu) {
            foreach ($menu['submenus'] as $submenu) {
                if ($submenu['class']) {
                    $iconName = str_replace('ui-silk-', "", $submenu['class']);
                    
                    $iconName = str_replace('-', "_", $iconName);
                    
                    $iconSource = dirname(__FILE__).'/icons/'.$iconName.'.png';
                    $iconDestiny = $folderDocPath.'/icons/'.$submenu['class'].'.png';
                    
                    if(file_exists($iconSource)) {
                        $copyIcon = copy($iconSource,$iconDestiny);
                        
                        if (!$copyIcon) {
                            echo 'No se puede copiar iconos en la carpeta';
                            exit;
                        }
                    } else {
                        var_dump($iconSource);
                    }
                }
            }
        }
        
        return true;
    }
    
    protected function projectYaml($klearYaml,$folderKlear) {
        $entity = array();
        foreach ($klearYaml['menu'] as $menu) {
            foreach ($menu['submenus'] as $yamlName => $submenu) {
                $yamlFilePath = $folderKlear.'/'.$yamlName.'.yaml';
                
                if(file_exists($yamlFilePath)) {
                    $fileTempPath = self::createYamlTemp($yamlFilePath, $yamlName, $folderKlear);
                    $entity[$yamlName] = self::getYaml($fileTempPath);
                    
                    unlink($fileTempPath); //Borrado del archivo temporal
                } else {
                    var_dump($yamlFilePath);
                }
            }
        }
        
        return $entity;
    }
    
    protected function createYamlTemp($yamlFilePath,$yamlName,$folderKlear) {
        
        $newYamlFile = array();
        
        foreach (file($yamlFilePath) as $line) {
            $line = str_replace('<<: *', '# <<: *', $line);
            $newYamlFile[] = $line;
        }
        
        $destinyFile = sys_get_temp_dir().'/'.$yamlName.'.yaml';
        
        $fh = fopen($destinyFile, 'w');
        fwrite($fh, implode($newYamlFile));
        fclose($fh);
        
        return ($destinyFile);
    }
    
    protected function getInclude($array,$folderKlear) {
        //Include
        $a = array();
        $b = array();
        $c = array();
        
        foreach ($array as $line) {
            $line = str_replace("\n", "", $line);
            $mystring = $line;
            $findme   = '#include';
            $pos = strpos($mystring, $findme);
            
            if (is_int($pos)) {
                $pieces = explode(" ", $line);
                
                $a[] = $line;
                
                $c = array();
                
                if ($pieces[1] == 'conf.d/mapperList.yaml#include') {
                    echo $line;
                    exit;
                }
                
                foreach (file($folderKlear.'/'.$pieces[1]) as $noInclude) {
                    $c[] = str_replace("<<: *", "#<<: *", $noInclude);
                }
                
                $b[] = implode($c);
                
            }
        }
        return array($a,$b);
    }
    
    protected function getEntitiesYaml($list,$folderKlear) {
        $model = array();
        
        foreach ($list as $yaml=>$array) {
            $yamlModel = str_replace("List", "", $yaml);
            
            $yamlSource = $folderKlear.'/model/'.$yamlModel.'.yaml';
            
            if (file_exists($yamlSource)) {
                $fileTempPath = self::createYamlTemp($yamlSource, $yamlModel, $folderKlear);
                $model[$yaml] = self::getYaml($fileTempPath);
                
                unlink($fileTempPath); //Borrado del archivo temporal
            }
            
        }
        
        return $model;
    }
}