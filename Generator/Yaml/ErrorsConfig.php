<?php
class Generator_Yaml_ErrorsConfig extends Generator_Yaml_AbstractConfig
{
    public function __construct()
    {
        $this->_data['production'] = array(
            'klearCommon' => array(),
            'adminErrors' => array()
        );
    }
}