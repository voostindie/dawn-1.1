<?php
require_once(DAWN_SYSTEM . 'Field.php');

class MemoField extends Field
{
    function MemoField($name, &$table)
    {
        $this->Field($name, $table);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('default', '');
        $this->setProperty('smart_text', false);
    }

    function isOrdered()
    {
        return false;
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
