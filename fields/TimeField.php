<?php
require_once(DAWN_SYSTEM . 'Field.php');

class TimeField extends Field
{
    function TimeField($name, &$table)
    {
        $this->Field($name, $table);
    }

    function isNull($value)
    {
        return (trim($value) === '' || is_null($value));
    }

    function getSql($value)
    {
        if (trim($value) == 0)
        {
            return 0;
        }
        return "'"  . trim($value) . "'";
    }
}
?>
