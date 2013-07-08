<?php
class Generator_Db_Field implements \IteratorAggregate
{
    protected $_description;

    public function __construct(array $description)
    {
        $this->_description = $description;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->_description);
    }

    public function getName()
    {
        return $this->_description['COLUMN_NAME'];
    }

    public function isPrimaryKey()
    {
        return (bool)$this->_description['PRIMARY'];
    }

    public function getTableName()
    {
        return $this->_description['TABLE_NAME'];
    }

    public function getAcceptedValues()
    {
        $acceptedValues = array();
        if ($this->isEnum()) {
            if (preg_match('/enum\((?<acceptedValues>.*)\)$/i', $this->_description['DATA_TYPE'], $matches)) {
                $acceptedValues = explode(',', $matches['acceptedValues']);
                $acceptedValues = array_map(function($value) {
                    return trim($value, '\'"');
                }, $acceptedValues);
            }
        }

        return $acceptedValues;
    }

    public function getType()
    {
        return $this->_description['DATA_TYPE'];
    }

    public function getLength()
    {
        return $this->_description['LENGTH'];
    }

    public function getLengthDefinition()
    {
        if ($this->getLength()) {
            return '(' . $this->getLength() . ')';
        }
        return '';
    }

    public function isNullable()
    {
        return $this->_description['NULLABLE'];
    }

    public function isRequired()
    {
        return !$this->isNullable();
    }

    public function hasDefaultValue()
    {
        return isset($this->_description['DEFAULT']) && !is_null($this->_description['DEFAULT']);
    }

    public function getDefaultValue()
    {
        return $this->_description['DEFAULT'];
    }

    public function hasComment()
    {
        return isset($this->_description['COMMENT']) && !empty($this->_description['COMMENT']);
    }

    public function getComment()
    {
        return $this->hasComment()? $this->_description['COMMENT'] : '';
    }

    public function isBoolean()
    {
        return $this->getType() == 'tinyint' && $this->getLength() == 1;
    }

    public function isCurrentTimeStamp()
    {
        return $this->getType() == 'timestamp' && $this->getDefaultValue() == 'CURRENT_TIMESTAMP';
    }

    public function isEnum()
    {
        return preg_match('/enum\(.*\)$/', $this->_description['DATA_TYPE']);
    }

    public function isFile()
    {
        return $this->_checkTag('file');
    }

    public function isFso()
    {
        return $this->_checkTag('fso');
    }

    public function isHtml()
    {
        return $this->_checkTag('html');
    }

    public function isMap()
    {
        return $this->_checkTag('map');
    }

    public function isMultilang()
    {
        return $this->_checkTag('ml');
    }

    /**
     * Returns true if field has "[password]" or field type is varchar and name has 'passwd' substring on it.
     * @return boolean
     */
    public function isPassword()
    {
        return
            $this->_description['DATA_TYPE'] == 'varchar' && stristr($this->getName(), 'passw')
            || $this->_checkTag('password');
    }

    public function isAnyDateType()
    {
        return (bool) preg_match('/date|time/', $this->_description['DATA_TYPE']);
    }

    public function isRelationship()
    {
        return isset($this->_description['RELATED']);
    }

    public function isUrlIdentifier()
    {
        return $this->_checkTag('urlIdentifier') || (bool)stristr($this->getComment(), '[urlIdentifier:');
    }

    public function getIdentifiedFieldName()
    {
        $identifiedFieldName = '';
        if (preg_match('/\[urlIdentifier:(?P<fieldName>.*)\]/', $this->getComment(), $matches)) {
            $identifiedFieldName = $matches['fieldName'];
        }
        return $identifiedFieldName;
    }

    public function isVideo()
    {
        return $this->_checkTag('video');
    }

    public function mustBeIgnored()
    {
        return $this->_checkTag('ignore');
    }

    protected function _checkTag($tag)
    {
        return $this->hasComment() && stristr($this->getComment(), '[' . $tag . ']');
    }

    public function getRelatedTable()
    {
        return $this->_description['RELATED']['TABLE'];
    }

    public function getRelatedField()
    {
        return $this->_description['RELATED']['FIELD'];
    }

    public function getPhpType()
    {
        $type = $this->getType();
        if (preg_match('/(tinyint\(1\)|bit)/', $type)) {
            $res = 'boolean';
        } elseif(preg_match('/(datetime|timestamp|blob|char|enum|date|time)/', $type)) {
            $res = 'string';
        } elseif (preg_match('/(decimal|numeric|float)/', $type)) {
            $res = 'float';
        } elseif (preg_match('#^(?:tiny|small|medium|long|big|var)?(\w+)(?:\(\d+\))?(?:\s\w+)*$#', $type, $matches)) {
            $res = $matches[1];
        }
        return $res;
    }

    public function getNormalizedName($type = 'lower')
    {
        return \Generator_StringUtils::toCamelCase($this->getName(), $type);
    }
}