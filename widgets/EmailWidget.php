<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class EmailWidget extends Widget
{
    var $value;
    var $isset;

    function EmailWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->isset = false;
    }

    function showWidget($indent)
    {
        if ($this->isset)
        {
            Html::showLine(
                $indent,
                '<a href="mailto:', $this->value, '"',
                $this->getProperty('css'), '>', $this->value, '</a>'
            );
        }
        else
        {
            Html::showLine($indent, '&nbsp;');
        }
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
