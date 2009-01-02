<?php
require_once(DAWN_WIDGETS . 'FormSubmitButtonWidget.php');

class RedirectButtonWidget extends FormSubmitButtonWidget
{
    function RedirectButtonWidget($id, &$form)
    {
        $this->FormSubmitButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('default'   , false);
        $this->setProperty('parameters', OBJECT_INVALID_VALUE);
    }


    function handleClick()
    {
        header(
            'Location: ' . APP_URL . '?' . $this->getProperty('parameters')
        );
        exit;
    }

}
?>
