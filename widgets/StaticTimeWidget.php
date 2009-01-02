<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class StaticTimeWidget extends Widget
{
    var $value;
    var $isset;

    function StaticTimeWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->isset = false;
    }

    function showWidget($indent)
    {
        if (!$this->isset)
        {
            Html::showLine($indent, '&nbsp;');
            return;
        }
        Html::showLine(
            $indent, $this->value
        );
    }

    function setValue($value)
    {
        if ($value != '' && !is_null($value))
        {
            $this->value = $value;
            $this->isset = true;
        }
    }
}
?>
