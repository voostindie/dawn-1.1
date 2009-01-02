<?php
require_once(DAWN_SYSTEM . 'Layout.php');

class FlowLayout extends Layout
{
    function FlowLayout(&$owner)
    {
        $this->Layout($owner);
    }

    function show($indent)
    {
        $owner =& $this->getOwner();
        if (($css = $owner->getProperty('css')) != '')
        {
            Html::showLine($indent, '<span', $css, '>');
            $indent++;
        }
        parent::show($indent);
        $this->showComponents($indent, $this->getComponents());
        if ($css != '')
        {
            $indent--;
            Html::showLine($indent, '</span>');
        }
    }
}
?>
