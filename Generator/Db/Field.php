<?php
class Generator_Db_Field
{
    protected $_fieldDesc;
    public function __construct($description)
    {
        $this->_fieldDesc = $description;
    }

    public function isMultilang()
    {
        return
            isset($this->_fieldDesc['COMMENT'])
            && strpos(strtolower($this->_fieldDesc['COMMENT']), '[ml]') !== false;
    }

    public function getName()
    {
        return $this->_fieldDesc['COLUMN_NAME'];
    }

    public function isPrimaryKey()
    {
        return (bool)$this->_fieldDesc['PRIMARY'];
    }
}