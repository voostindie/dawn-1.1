<?php
require_once(DAWN_SYSTEM . 'Field.php');

class IntField extends Field
{
    function IntField($name, &$table)
    {
        $this->Field($name, $table);
    }

    function getSql($value)
    {
        return (int)(trim($value));
    }
}
?>
