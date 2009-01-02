<?php
require_once(DAWN_SYSTEM . 'Layout.php');

class CenteredLayout extends Layout
{
    function CenteredLayout(&$owner)
    {
        $this->Layout($owner);
    }

    function show($indent)
    {
        $owner =& $this->getOwner();
        parent::show($indent);
        Html::showLine($indent    , '<center>');
        Html::showLine($indent + 1, '<table height="90%">');
        Html::showLine($indent + 2, '<tr>');
        Html::showLine($indent + 3, '<td valign="middle">');
        $this->showComponents($indent + 4, $this->getComponents());
        Html::showLine($indent + 3, '</td>');
        Html::showLine($indent + 2, '</tr>');
        Html::showLine($indent + 1, '</table>');
        Html::showLine($indent    , '</center>');
    }
}
?>
