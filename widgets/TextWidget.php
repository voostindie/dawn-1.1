<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class TextWidget extends Widget
{
    var $value;

    function TextWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('size', 70);
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            '<input name="', $this->getObjectId(), '" type="text"',
            ' size="', $this->getProperty('size'), '" value="',
            htmlspecialchars($this->value), '"', $this->getProperty('css'), '>'
        );
    }

    function setValue($value)
    {
        $this->value = $value;
    }
}
?>
