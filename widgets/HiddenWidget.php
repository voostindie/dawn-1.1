<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class HiddenWidget extends Widget
{
    var $values;

    function HiddenWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->values = array();
    }

    function showWidget($indent)
    {
        $it =& new ArrayIterator($this->values);
        for ( ; $it->isValid(); $it->next())
        {
            Html::showLine(
                $indent,
                '<input type="hidden" name="', $it->getKey(),
                '" value="', htmlspecialchars($it->getCurrent()), '">'
            );
        }
    }

    function setField($name, $value)
    {
        $this->values[$name] = $value;
    }

    function setValue($value)
    {
        $this->setField($this->getObjectId(), $value);
    }

    function getDefaultLayout()
    {
        return 'empty';
    }
}
?>
