<?php
require_once(DAWN_SYSTEM . 'Layout.php');

class VerticalLayout extends Layout
{
    function VerticalLayout(&$owner)
    {
        $this->Layout($owner);
    }
    
    function show($indent)
    {
        $owner =& $this->getOwner();
        if (($css = $owner->getProperty('css')) != '')
        {
            Html::showLine($indent, '<div', $css, '>');
            $indent++;
        }
        parent::show($indent);
        $this->showComponents($indent, $this->getComponents());
        if ($css != '')
        {
            $indent--;
            Html::showLine($indent, '</div>');
        }
    }
}
?>
