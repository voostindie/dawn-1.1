<?php
require_once(DAWN_WIDGETS . 'ButtonWidget.php');

class FormSubmitButtonWidget extends ButtonWidget
{
    function FormSubmitButtonWidget($id, &$form)
    {
        $this->ButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('default', true);
    }

    function getOptions()
    {
        $submit = $this->getProperty('default')
            ? ' return true;'
            : 'this.form.submit();';
        return 'onclick="this.form.' . $this->getProperty('command_field') .
            '.value = \'' . $this->getObjectId() . '\';' . $submit . '"';
    }
}
?>
