<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class LabelWidget extends Widget
{
    var $value;
    var $isset;

    function LabelWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->isset   = false;
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
            'caption',
            Translator::getText(
                'WIDGET_' . strtoupper($this->getProperty('caption')),
                strtoupper($this->getProperty('caption')),
                $this->getProperty('caption')
            )
        );
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            ($this->isset
                ? $this->value
                : $this->getProperty('caption')
            )
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

    function setCaption($caption)
    {
        $this->setValue($caption);
    }
}
?>
