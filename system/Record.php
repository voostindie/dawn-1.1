<?php
class Record
{
    var $table;
    var $fields;

    function Record(&$table, $array)
    {
        $this->table =& $table;
        $this->reset($array);
    }

    function reset($array)
    {
        $this->fields = array();
        $it =& new ArrayIterator($this->table->getFields());
        for ( ; $it->isValid(); $it->next())
        {
            $this->fields[$it->getCurrent()] = NULL;
        }
        $prefix = $this->table->getName() . '_';
        $size   = strlen($prefix);
        $it =& new ArrayIterator($array);
        for ( ; $it->isValid(); $it->next())
        {
            $value = $it->getCurrent();
            if (substr($it->getKey(), 0, $size) == $prefix &&
                $this->table->hasField(substr($it->getKey(), $size)))
            {
                $this->fields[substr($it->getKey(), $size)] =
                    $it->getCurrent();
            }
            elseif ($this->table->hasField($it->getKey()))
            {
                $this->fields[$it->getKey()] = $it->getCurrent();
            }
        }
    }

    function getKeySelection()
    {
        $result =  array();
        $it     =& new ArrayIterator($this->table->getPrimaryKey());
        for ( ; $it->isValid(); $it->next())
        {
            $field =& $this->table->getField($it->getCurrent());
            array_push(
                $result,
                $field->getName() . ' = ' .
                    $field->getSql($this->fields[$field->getName()])
            );
        }
        return join(' AND ', $result);
    }

    function setField($field, $value)
    {
        $this->fields[$field] = $value;
    }

    function &getTable()
    {
        return $this->table;
    }

    function hasField($name)
    {
        return $this->table->hasField($name);
    }

    function &getField($name)
    {
        return $this->table->getField($name);
    }

    function getFields()
    {
        return array_keys($this->fields);
    }

    function hasValue($field)
    {
        return $this->fields[$field] !== NULL;
    }

    function getValue($field)
    {
        return $this->fields[$field];
    }
}
?>
