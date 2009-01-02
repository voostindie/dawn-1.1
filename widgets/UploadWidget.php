<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class UploadWidget extends Widget
{
    var $value;
    var $size;

    function UploadWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->size  = -1;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('size'    , 50);
        $this->setProperty('filesize', 2097152);
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            '<input type="hidden" name="MAX_FILE_SIZE" value="',
            ($this->size > -1 ? $this->size : $this->getProperty('filesize')),
            '">'
        );
        Html::showLine(
            $indent,
            '<input type="file" name="', $this->getObjectId(),
            '" size="', $this->getProperty('size'),
            '" value="', $this->value, '"',
            $this->getProperty('css'), '>'
        );
    }

    function setValue($value)
    {
        $this->value = $value;
    }

    function setSize($size)
    {
        $this->size = $size;
    }
}
?>
