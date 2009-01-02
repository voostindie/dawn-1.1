<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class MenuWidget extends Widget
{
    function MenuWidget($name, &$form)
    {
        $this->Widget($name, $form);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('separator', ' | ');
    }
    
    function showWidget($indent)
    {
        $menu  =& $this->getMenu();
        $items =& $menu->getItems();
        $css   = $this->getProperty('css');
        $count = count($items);
        for ($i = 0; $i < $count; $i++)
        {
            $item =& $menu->getItem($items[$i]);
            if ($item->getName() == $menu->getCurrent())
            {
                $this->showCurrent($indent, $item, $css);
            }
            else
            {
                $this->showItem($indent, $item, $css);
            }
            if ($i < $count - 1)
            {
                Html::showLine($indent, $this->getProperty('separator'));
            }
        }
    }

    function showCurrent($indent, &$item, $css)
    {
        Html::showLine($indent, '<b>', $item->getCaption(), '</b>');
    }

    function showItem($indent, &$item, $css)
    {
        Html::showLine(
            $indent,
            '<a href="', APP_URL, '?page=', $item->getName(), '"', $css, '>',
            $item->getCaption(),
            '</a>'
        );
    }

    function &getMenu()
    {
        $application =& $this->getApplication();
        return $application->getMenu();
    }
    
    function getDefaultCss()
    {
        return 'menu';   
    }
}
?>
