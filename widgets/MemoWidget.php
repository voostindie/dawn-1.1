<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class MemoWidget extends Widget
{
    var $value;

    function MemoWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('columns', 60);
        $this->setProperty('rows'   , 15);
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            '<textarea name="', $this->getObjectId(), '"', 
            ' rows="', $this->getProperty('rows'),
            '" cols="', $this->getProperty('columns'), '"',
            $this->getProperty('css'), '>', 
            htmlspecialchars($this->value), '</textarea>'
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
