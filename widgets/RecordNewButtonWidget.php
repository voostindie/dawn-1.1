<?php
require_once(DAWN_WIDGETS . 'RecordButtonWidget.php');

class RecordNewButtonWidget extends RecordButtonWidget
{
    function RecordNewButtonWidget($id, &$form)
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

    function handleClick()
    {
        parent::handleClick();
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
        return true;
    }
}
?>
