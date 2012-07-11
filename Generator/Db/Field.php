<?php
class Generator_Db_Field implements \IteratorAggregate
{
    protected $_fieldDesc;
    public function __construct($description)
    {
        $this->_fieldDesc = $description;
    }

    public function getName()
    {
        return $this->_fieldDesc['COLUMN_NAME'];
    }

    public function isPrimaryKey()
    {
        return (bool)$this->_fieldDesc['PRIMARY'];
    }

    public function getTableName()
    {
        return $this->_fieldDesc['TABLE_NAME'];
    }

    public function getIterator()
    {
        return $this->_fieldDesc;
    }

    public function getType()
    {
        return $this->_fieldDesc['DATA_TYPE'];
    }

    public function getLength()
    {
        return $this->_fieldDesc['LENGTH'];
    }

    public function getLengthDefinition()
    {
        if ($this->getLength()) {
            return '(' . $this->getLength() . ')';
        }
    }

    public function isNullable()
    {
        return $this->_fieldDesc['NULLABLE'];
    }

    public function hasDefaultValue()
    {
        return isset($this->_fieldDesc['DEFAULT']) && !empty($this->_fieldDesc['DEFAULT']);
    }

    public function getDefaultValue()
    {
        return $this->_fieldDesc['DEFAULT'];
    }

    public function hasComment()
    {
        return isset($this->_fieldDesc['COMMENT']) && !empty($this->_fieldDesc['COMMENT']);
    }

    public function getComment()
    {
        return $this->hasComment()? $this->_fieldDesc['COMMENT'] : '';
    }

    public function isMultilang()
    {
        return $this->hasComment() && stristr($this->getComment(), '[ml]');
    }

    public function isFile()
    {
        return $this->hasComment() && stristr($this->getComment(), '[file]');
    }

    public function isRelationship()
    {
        return isset($this->_fieldDesc['RELATED']);
    }

    public function isPassword()
    {
        return
            $this->_fieldDesc['DATA_TYPE'] == 'varchar' && stristr($this->getName(), 'passw')
            || stristr($this->getComment(), '[password]');
    }

    public function isBoolean()
    {
        return $this->_fieldDesc['DATA_TYPE'] == 'tinyint' && $this->getLength() == 1;
    }

    public function isEnum()
    {
        return preg_match('/enum\(.*\)$/', $this->_fieldDesc['DATA_TYPE']);
    }

    public function getRelatedTable()
    {
        return $this->_fieldDesc['RELATED']['TABLE'];
    }

    public function getRelatedField()
    {
        return $this->_fieldDesc['RELATED']['FIELD'];
    }

    public function isHtml()
    {
        return (bool)stristr($this->getComment(), '[html]');
    }
}