<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class ImageWidget extends Widget
{
    var $value;
    var $isset;

    function ImageWidget($id, &$form)
    {
        $this->Widget($id, $form);
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
            $indent, '<img src="' . $this->image . '">'
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

    function setImage($image)
    {
        $this->setValue($image);
    }
}
?>
