<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class PasswordWidget extends Widget
{
    var $value;

    function PasswordWidget($id, &$form)
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
            '<input name="', $this->getObjectId(), '" type="password"',
            ' size="', $this->getProperty('size'), '" value="',
            htmlspecialchars($this->value), '"', $this->getProperty('css'), '>'
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
