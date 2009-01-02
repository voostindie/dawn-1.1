<?php
require_once(DAWN_SYSTEM . 'Factory.php');

define('DAWN_FORM_FACTORY', 'DAWN_FORM_FACTORY');

class FormFactory extends Factory
{
    function &getInstance()
    {
        if (!isset($GLOBALS[DAWN_FORM_FACTORY]))
        {
            $GLOBALS[DAWN_FORM_FACTORY] =& new FormFactory(
                'form', 
                DAWN_FORMS,
                APP_FORMS
            );
        }
        return $GLOBALS[DAWN_FORM_FACTORY];
    }
    
    function &createForm($name, &$page, $alias)
    {
        $class = $this->getClass($alias);
        return new $class($name, $page);
    }
}
?>
