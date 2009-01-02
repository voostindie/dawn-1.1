<?php
require_once(DAWN_WIDGETS . 'RecordButtonWidget.php');

class RecordCancelButtonWidget extends RecordButtonWidget
{
    function RecordCancelButtonWidget($id, &$form)
    {
        $this->RecordButtonWidget($id, $form);
    }

    function handleClick()
    {
        parent::handleClick();
        $history =& $this->getUserHistory();
        $url = $history->pop();
        if ($url === NULL)
        {
            $this->setErrorMessage('No previous!');
            return false;
        }
        header('Location: ' . $url);
        exit;
    }
}
?>
