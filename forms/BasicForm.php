<?php
require_once(DAWN_SYSTEM . 'Form.php');

class BasicForm extends Form
{
    function BasicForm($name, &$page)
    {
        $this->Form($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('layout', '');
        $this->setProperty('width', 1);
    }

    function postCreate()
    {
        $widgets =  array();
        $it      =& new ArrayIterator($this->getProperty('widgets'));
        for ( ; $it->isValid(); $it->next())
        {
            array_push($widgets, $it->getKey());
        }
        $settings = $this->hasProperty('settings')
            ? $this->getProperty('settings')
            : array();
        $index    = 0;
        $width    = $this->getProperty('width');
        for ($it =& new ArrayIterator($widgets); $it->isValid(); $it->next())
        {
            if (!isset($settings[$it->getCurrent()]))
            {
                $settings[$it->getCurrent()] = array(
                    'row' => ($index++ % $width == 0)
                );
            }
        }
        $this->setProperty(
            'layout',
            array(
                'type'       => 'table',
                'settings'   => $settings,
                'components' => join(', ', $widgets)
            )
        );
        parent::postCreate();
    }
}
?>
