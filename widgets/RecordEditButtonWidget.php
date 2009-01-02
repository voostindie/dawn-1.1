<?php
require_once(DAWN_WIDGETS . 'RecordButtonWidget.php');

class RecordEditButtonWidget extends RecordButtonWidget
{
    function RecordEditButtonWidget($id, &$form)
    {
        $this->RecordButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('target', OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        parent::postCreate();
        $this->checkTarget($this->getProperty('target'));
    }

    function showWidget($indent)
    {
        if ($this->getActiveRecord() !== false)
        {
            parent::showWidget($indent);
        }
    }

    function handleClick()
    {
        parent::handleClick();
        $record  =& $this->getActiveRecord();
        if ($record === false)
        {
            // TODO: translate this message
            $this->setErrorMessage('No record');
            return false;
        }
        $history =& $this->getUserHistory();
        $history->push($_POST['_url']);
        $tracker =  $this->getTracker();
        $oldUrl  =& new Url($_POST['_url']);
        $newUrl  =& new Url(APP_URL);
        $newUrl->setParameter('page', $this->getProperty('target'));
        if ($oldUrl->hasParameter($tracker))
        {
            $newUrl->setParameter($tracker, $oldUrl->getParameter($tracker));
        }
        header('Location: ' . $newUrl->getUrl());
        exit;
    }
}
?>
