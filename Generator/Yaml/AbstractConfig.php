<?php
abstract class Generator_Yaml_AbstractConfig
{
    protected $_data;
    public function getConfig()
    {
        $data = $this->_data;

        if (isset($data['production'])) {
            $data['testing'] = array(
                '_extends' => 'production'
            );

            $data['development'] = array(
                '_extends' => 'production'
            );
        }

        if (is_null($data)) {
            $data = array();
        }

        return new Zend_Config($data);
    }
}