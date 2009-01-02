<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class StaticWidget extends Widget
{
    var $value;

    function StaticWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            htmlspecialchars($this->value)
        );
    }

    function setValue($value)
    {
        $this->value = $value;
    }
}
?>
