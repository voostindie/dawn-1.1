<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class InvalidFieldException extends Exception
{
    var $section;
    var $table;
    var $field;
    
    function InvalidFieldException($section, $table, $field)
    {
        $this->Exception();
        $this->section = $section;
        $this->table   = $table;
        $this->field   = $field;
    }
    
    function getName()
    {
        return 'Invalid Table Field';
    }
    
    function getDescription()
    {
        return "The configuration for the table <b>$this->table</b> has a " .
            "reference to the field <b>$this->field</b> in the section " .
            "<b>$this->section</b>, but this field doesn't exist in the " .
            "table, so this reference is invalid.";
    }
}
?>
