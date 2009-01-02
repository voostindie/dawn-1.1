<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class InvalidOrderException extends Exception
{
    var $table;
    var $field;
    
    function InvalidOrderException($table, $field)
    {
        $this->Exception();
        $this->table   = $table;
        $this->field   = $field;
    }
    
    function getName()
    {
        return 'Invalid Table Ordering';
    }
    
    function getDescription()
    {
        return "The tuples in the table <b>$this->table</b> are ordered on " .
            "the field <b>$this->field</b>, but this field is not orderable.";
    }
}
?>
