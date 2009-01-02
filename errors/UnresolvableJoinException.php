<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class UnresolvableJoinException extends Exception
{
    var $table1;
    var $table2;

    function UnresolvableJoinException ($table1, $table2)
    {
        $this->Exception();
        $this->table1 = $table1;
        $this->table2 = $table2;
    }

    function getName()
    {
        return 'Unresolvable Table Join';
    }

    function getDescription()
    {
        return "The requested join between the tables <b>$this->table1</b> " .
            "and <b>$this->table2</b> could not be resolved.";
    }
}
?>
