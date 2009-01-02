<?php
require_once(DAWN_LAYOUTS . 'TableLayout.php');

class BorderLayout extends TableLayout
{
    function BorderLayout(&$owner)
    {
        $this->TableLayout($owner);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('north' , '');
        $this->setProperty('west'  , '');
        $this->setProperty('center', '');
        $this->setProperty('east'  , '');
        $this->setProperty('south' , '');
    }

    function postCreate()
    {
        $north  = $this->createDirection('north');
        $west   = $this->createDirection('west');
        $center = $this->createDirection('center');
        $east   = $this->createDirection('east');
        $south  = $this->createDirection('south');
        $colspan = min(count($west)  , 1)
                 + min(count($center), 1)
                 + min(count($east)  , 1);
        $settings = array();
        $this->createSettings($settings, $north, true, $colspan, '');
        $row = !$this->createSettings(
            $settings, $west, true, 0, 'white-space: nowrap'
        );
        $row = !$this->createSettings(
            $settings, $center, $row, 0, 'width: 100%'
        );
        $this->createSettings($settings, $east, $row, 0, 'white-space: nowrap');
        $this->createSettings($settings, $south, true, $colspan, '');
        $this->setProperty('settings', $settings);
        parent::postCreate();
    }

    function createDirection($name)
    {
        $list = $this->parseList($this->getProperty($name));
        $this->registerComponents($list);
        $this->deleteProperty($name);
        return $list;
    }

    function createSettings(&$settings, &$direction, $row, $colspan, $style)
    {
        if (isset($direction[0]))
        {
            $settings[$direction[0]] = array(
                'row'     => $row,
                'colspan' => ($colspan > 1 ? $colspan : ''),
                'style'   => $style
            );
            return true;
        }
        return false;
    }
}
?>
