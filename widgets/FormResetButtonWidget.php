<?php
require_once(DAWN_WIDGETS . 'ButtonWidget.php');

class FormResetButtonWidget extends ButtonWidget
{
    function FormResetButtonWidget($id, &$form)
    {
        $this->ButtonWidget($id, $form);
    }

    function postCreate()
    {
        parent::postCreate();
        $form =& $this->getOwner();
        $page =& $form->getPage();
        $script = <<<EOF_SCRIPT
function clearForm(form)
{
    var j = form.elements.length;
    for (var i = 0; i < j; i++)
    {
        var element = form.elements[i];
        switch (element.type)
        {
            case 'text':
                element.value = '';
                break;
            case 'select-one':
                element.selectedIndex = 0;
                break;
            case 'checkbox':
                element.checked = '';
                break;
        }
    }
}
EOF_SCRIPT;
        $page->addScript('clearForm', $script);
    }

    function getOptions()
    {
        return 'onclick="clearForm(this.form)"';
    }

}
?>
