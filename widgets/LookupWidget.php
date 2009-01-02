<?php
class LookupWidget extends Widget
{
    var $value;

    function LookupWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('values'   , OBJECT_INVALID_VALUE);
        $this->setProperty('captions' , '');
        $this->setProperty('separator', ',');
    }

    function postCreate()
    {
        $values   = $this->parseList(
            $this->getProperty('values'), $this->getProperty('separator')
        );
        $captions = $this->parseList(
            $this->getProperty('captions'), $this->getProperty('separator')
        );
        $this->setProperty('values', $values);
        $size  = count($values);
        $count = count($captions);
        for ($i = $count; $i < $size; $i++)
        {
            $captions[$i] = $values[$i];
        }
        $this->setProperty('values'  , $values);
        $this->setProperty('captions', $captions);
        $this->setProperty('size'    , $size);
        parent::postCreate();
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            '<select name="', $this->getObjectId(), '"',
            $this->getProperty('css'), '>'
        );
        $values   =& $this->getProperty('values');
        $captions =& $this->getProperty('captions');
        $size     =  $this->getProperty('size');
        for ($i = 0; $i < $size; $i++)
        {
            $selected = $values[$i] == $this->value ? ' selected' : '';
            Html::showLine(
                $indent + 1,
                '<option value="', $values[$i], '"', $selected, '>',
                $captions[$i],
                '</option>'
            );
        }
        Html::showLine(
            $indent,
            '</select>'
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
