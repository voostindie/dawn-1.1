<?php
require_once(DAWN_SYSTEM . 'Command.php');

class UpdateCommand extends Command
{
    var $oldRecord;
    var $newRecord;

    function UpdateCommand($name, &$table)
    {
        $this->Command($name, $table);
        unset($this->oldRecord);
        unset($this->newRecord);
    }

    function run()
    {
        if (!isset($this->oldRecord))
        {
            // TODO: throw an error
            exit('Old record not set');
        }
        if (!isset($this->newRecord))
        {
            // TODO: throw an error
            exit('New record not set');
        }
        return parent::run();
    }

    function execute()
    {
        $fields =  array();
        $it     =& new ArrayIterator($this->oldRecord->getFields());
        for ( ; $it->isValid(); $it->next())
        {
            $name  =  $it->getCurrent();
            if (!$this->oldRecord->hasField($name))
            {
                continue;
            }
            $field =& $this->oldRecord->getField($name);
            $value =  $this->newRecord->getValue($name);
            if ($this->newRecord->hasValue($name))
            {
                if ($field->isNull($value))
                {
                    $sql = 'NULL';
                }
                else
                {
                    $sql = $field->getSql($value);
                }
                if ($field->getSql($this->oldRecord->getValue($name)) !== $sql)
                {
                    array_push($fields, $name . ' = ' . $sql);
                }
            }
        }
        if (count($fields) == 0)
        {
            return true;
        }
        $transaction =& $this->getTransaction();
        $sql = 'UPDATE ' . $this->table->getName() .
               ' SET   ' . join(', ', $fields) .
               ' WHERE ' . $this->oldRecord->getKeySelection();
        $result =& $transaction->query($sql);
        return $result->isSuccess();
    }

    function setOldRecord(&$record)
    {
        $this->oldRecord =& $record;
    }

    function setNewRecord(&$record)
    {
        $this->newRecord =& $record;
    }

    function &getOldRecord()
    {
        return $this->oldRecord;
    }

    function &getNewRecord()
    {
        return $this->newRecord;
    }
}
?>
