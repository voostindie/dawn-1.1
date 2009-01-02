<?php
class CheckboxWidget extends Widget
{
    var $value;

    function CheckboxWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function postCreate()
    {
        $script = <<<EOF_SCRIPT
function updateCheckbox(control, field)
{
    var value = control.checked ? 1 : 0;
    control.form.elements[field].value = value;
}
EOF_SCRIPT;
        $form =& $this->getOwner();
        $page =& $form->getPage();
        $page->addScript('updateCheckbox', $script);
        parent::postCreate();
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            '<input type="hidden" name="', $this->getObjectId(),
            '" value="', (int)$this->value, '">',
            '<input type="checkbox"',
            'onclick="updateCheckbox(this, \'', $this->getObjectId(),
            '\')"', ($this->value ? ' checked' : ''), '>'
        );
    }

    function setValue($value)
    {
        if ($value != '' && !is_null($value))
        {
            $this->value = (bool)$value;
        }
    }
}
?>
