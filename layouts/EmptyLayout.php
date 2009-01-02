<?php
require_once(DAWN_SYSTEM . 'Layout.php');

class EmptyLayout extends Layout
{
    function EmptyLayout(&$owner)
    {
        $this->Layout($owner);
    }

    function show($indent)
    {
        $owner =& $this->getOwner();
        parent::show($indent);
        $this->showComponents($indent, $this->getComponents());
    }
}
?>
