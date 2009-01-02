<?php
require_once(DAWN_SYSTEM . 'Field.php');

class BitField extends Field
{
    function BitField($name, &$table)
    {
        $this->Field($name, $table);
    }

    function isNull($value)
    {
        return false;
    }

    function getSql($value)
    {
        $value = trim($value);
        if ($value == '')
        {
            return 0;
        }
        elseif ($value == 'on' || (bool)$value)
        {
            return 1;
        }
        return 0;
    }
}
?>
