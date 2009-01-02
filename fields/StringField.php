<?php
require_once(DAWN_SYSTEM . 'Field.php');

class StringField extends Field
{
    function StringField($name, &$table)
    {
        $this->Field($name, $table);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('max_length', -1);
        $this->setProperty('default', '');
    }
    
    function isNull($value)
    {
        return (trim($value) === '' || is_null($value));
    }

    function getSql($value)
    {
        return "'" . str_replace("'", "''", trim($value)) . "'";
    }
}
?>
