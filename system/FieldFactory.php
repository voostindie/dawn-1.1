<?php
require_once(DAWN_SYSTEM . 'Factory.php');

define('DAWN_FIELD_FACTORY', 'DAWN_FIELD_FACTORY');

class FieldFactory extends Factory
{
    function &getInstance()
    {
        if (!isset($GLOBALS[DAWN_FIELD_FACTORY]))
        {
            $GLOBALS[DAWN_FIELD_FACTORY] =& new FieldFactory(
                'field', 
                DAWN_FIELDS,
                APP_FIELDS
            );
        }
        return $GLOBALS[DAWN_FIELD_FACTORY];
    }    
    
    function createField($name, &$table, $alias)
    {
        $class = $this->getClass($alias);
        return new $class($name, $table);
    }
}
?>
