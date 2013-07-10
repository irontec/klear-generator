<?php
class Generator_Db_FieldCollection implements \IteratorAggregate
{
    protected $_fields = array();
    protected $_primaryKey = array();
    protected $_softDeleteField = null;

    public function add(\Generator_Db_Field $field)
    {
        $this->_fields[$field->getName()] = $field;

        if ($field->isPrimaryKey()) {
            $this->_primaryKey[] = $field->getName();
        }

        if ($field->isSoftDelete()) {
            $this->_softDeleteField = $field->getName();
        }
    }

    public function hasField($fieldName)
    {
        return isset($this->_fields[$fieldName]);
    }

    public function remove($fieldName)
    {
        unset($this->_fields[$fieldName]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->_fields);
    }

    public function hasSoftDelete()
    {
        return !is_null($this->_softDeleteField);
    }

    public function getSoftDeleteField()
    {
        return $this->_fields[$this->_softDeleteField];
    }

    public function getPrimaryKey()
    {
        if (!$this->_primaryKey) {
            return null;
        }

        if (sizeof($this->_primaryKey === 1)) {
            return $this->_fields[$this->_primaryKey[0]];
        }

        $primaryKey = array();
        foreach ($this->_primaryKey as $fieldName) {
            $primaryKey[] = $this->_fields[$fieldName];
        }
        return $primaryKey;
    }
}
