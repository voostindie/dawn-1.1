<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class IllegalJoinException extends Exception
{
    var $clause;
    var $join;

    function IllegalJoinException($clause, $join)
    {
        $this->Exception();
        $this->clause = $clause;
        $this->join = $join;
    }

    function getName()
    {
        return 'Illegal Table Join';
    }

    function getDescription()
    {
        return "The clause <b>$this->clause</b> has an illegal, manually " .
            "specified join: <b>$this->join</b>.";
    }
}
?>
