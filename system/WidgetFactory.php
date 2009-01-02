<?php
require_once(DAWN_SYSTEM . 'Factory.php');

define('DAWN_WIDGET_FACTORY', 'DAWN_WIDGET_FACTORY');

class WidgetFactory extends Factory
{
    function &getInstance()
    {
        if (!isset($GLOBALS[DAWN_WIDGET_FACTORY]))
        {
            $GLOBALS[DAWN_WIDGET_FACTORY] =& new WidgetFactory(
                'widget', 
                DAWN_WIDGETS,
                APP_WIDGETS
            );
        }
        return $GLOBALS[DAWN_WIDGET_FACTORY];
    }
    
    function &createWidget($name, &$form, $alias)
    {
        $class = $this->getClass($alias);
        return new $class($name, $form);   
    }
}
?>
