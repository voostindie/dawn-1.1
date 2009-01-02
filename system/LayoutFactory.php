<?php
require_once(DAWN_SYSTEM . 'Factory.php');

define('DAWN_LAYOUT_FACTORY', 'DAWN_LAYOUT_FACTORY');

class LayoutFactory extends Factory
{
    function &getInstance()
    {
        if (!isset($GLOBALS[DAWN_LAYOUT_FACTORY]))
        {
            $GLOBALS[DAWN_LAYOUT_FACTORY] =& new LayoutFactory(
                'layout',
                DAWN_LAYOUTS,
                APP_LAYOUTS
            );
        }
        return $GLOBALS[DAWN_LAYOUT_FACTORY];
    }

    function &createLayout(&$window, $alias)
    {
        $class = $this->getClass($alias);
        return new $class($window);
    }
}
?>
