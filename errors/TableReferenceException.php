<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class TableReferenceException extends Exception
{
    var $table;
    var $field;
    var $target;
    
    function TableReferenceException($sourceTable, $sourceField, $target)
    {
        $this->Exception();
        $this->table  = $sourceTable;
        $this->field  = $sourceField;
        $this->target = $target;
    }
    
    function getName()
    {
        return 'Invalid Table Reference';
    }
    
    function getDescription()
    {
        return "The field <b>$this->field</b> of table <b>$this->table</b> " .
            "references an invalid field: <b>$this->target</b>.";
    }
}
?>
