<?php
require_once(DAWN_SYSTEM . 'Command.php');

class DeleteCommand extends Command
{
    var $record;

    function DeleteCommand($name, &$table)
    {
        $this->Command($name, $table);
        unset($this->record);
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
        $transaction =& $this->getTransaction();
        $sql = 'DELETE FROM ' . $this->table->getName() .
               ' WHERE '      . $this->record->getKeySelection();
        $result =& $transaction->query($sql);
        return $result->isSuccess();
    }

    function setOldRecord(&$record)
    {
        $this->record =& $record;
    }

    function &getOldRecord()
    {
        return $this->record;
    }
}
?>
