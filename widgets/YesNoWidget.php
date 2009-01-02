<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class YesNoWidget extends Widget
{
    var $value;

    function YesNoWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('caption', $this->getObjectId());
    }

    function postCreate()
    {
        parent::postCreate();
        $this->setProperty(
            'yes',
            Translator::getText('YES')
        );
        $this->setProperty(
            'no',
            Translator::getText('NO')
        );
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            (bool)$this->value
                ? $this->getProperty('yes')
                : $this->getProperty('no')
        );
    }

    function setValue($value)
    {
        $this->value = $value;
    }
}
?>
