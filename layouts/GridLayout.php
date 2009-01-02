<?php
require_once(DAWN_LAYOUTS . 'TableLayout.php');

class GridLayout extends TableLayout
{
    function GridLayout(&$owner)
    {
        $this->TableLayout($owner);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('width' , OBJECT_INVALID_VALUE);
        $this->setProperty('height', OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        $settings   = array();
        $components = array();
        $width    = $this->getProperty('width');
        $height   = $this->getProperty('height');
        $this->deleteProperty('width');
        $this->deleteProperty('height');
        $this->deleteProperty('prefix');
        for ($i = 0; $i < $height; $i++)
        {
            for ($j = 0; $j < $width; $j++)
            {
                $name = $j . ',' . $i;
                $settings[$name] = array('row' => $j == 0);
                array_push($components, $name);
            }
        }
        $this->setProperty('settings'  , $settings);
        $this->setProperty('components', $components);
        parent::postCreate();
    }
}
?>
