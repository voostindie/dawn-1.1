<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class StaticMemoWidget extends Widget
{
    var $value;

    function StaticMemoWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function showWidget($indent)
    {
        Html::showLines(
            $indent,
            nl2br(htmlspecialchars($this->value))
        );
    }

    function setValue($value)
    {
        if ($value != '' && !is_null($value))
        {
            $this->value = $value;
        }
    }
}
?>
