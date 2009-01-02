<?php
require_once(DAWN_SYSTEM . 'Command.php');

class InsertCommand extends Command
{
    var $record;

    function InsertCommand($name, &$table)
    {
        $this->Command($name, $table);
    }

    function run()
    {
        if (!isset($this->record))
        {
            // TODO: throw an error
            exit('Record not set');
        }
        return parent::run();
    }

    function execute()
    {
        $fields =  array();
        $values =  array();
        $it     =& new ArrayIterator($this->record->getFields());
        for ( ; $it->isValid(); $it->next())
        {
            $name = $it->getCurrent();
            if (!$this->record->hasField($name))
            {
                continue;
            }
            $field =& $this->record->getField($name);
            $value =  $this->record->getValue($name);
            $sql   =  $field->getSql($value);
            if ($this->record->hasValue($name))
            {
                if ($field->isNull($value))
                {
                    if (!$field->isNullValid())
                    {
                        continue;
                    }
                    $sql = 'NULL';
                }
                else
                {
                    $sql = $field->getSql($value);
                }
                array_push($fields, $name);
                array_push($values, $sql);
            }
        }
        if (count($fields) == 0)
        {
            return true;
        }
        $transaction =& $this->getTransaction();
        $sql = 'INSERT INTO ' . $this->table->getName() .
               '(' . join(', ', $fields) . ') VALUES (' .
               join(', ', $values) . ')';
        $result =& $transaction->query($sql);
        return $result->isSuccess();
    }

    function setNewRecord(&$record)
    {
        $this->record =& $record;
    }

    function &getNewRecord()
    {
        return $this->record;
    }
}
?>
