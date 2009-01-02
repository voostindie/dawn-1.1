<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class HyperlinkWidget extends Widget
{
    var $value;
    var $isset;

    function HyperlinkWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->isset = false;
    }

    function showWidget($indent)
    {
        if ($this->isset)
        {
            $link = $this->value;
            if (strlen($link) < 7 || substr($link, 0, 7) != 'http://')
            {
                $link = 'http://' . $link;
            }
            Html::showLine(
                $indent,
                '<a href="' , $link, '" target="_blank"',
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
