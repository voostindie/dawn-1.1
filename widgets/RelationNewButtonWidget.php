<?php
require_once(DAWN_WIDGETS . 'ButtonWidget.php');

class RelationNewButtonWidget extends ButtonWidget
{
    function RelationNewButtonWidget($id, &$form)
    {
        $this->ButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('key_field', '');
        $this->setProperty('target'   , OBJECT_INVALID_VALUE);
        $this->setProperty('tracker'  , OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        parent::postCreate();
        $this->checkTarget(
            $this->getProperty('target'), $this->getProperty('tracker')
        );
    }

    function checkTarget($page, $tracker)
    {
        include_once(DAWN_SYSTEM . 'Config.php');
        $config =& Config::getInstance();
        $config->getEntry('pages.' . $page);
        $config->getEntry('pages.' . $page . '.forms.' . $tracker);
    }

    function getOptions()
    {
        $submit = $this->getProperty('default')
            ? ' return true;'
            : $submit = ' this.form.submit();';
        return 'onclick="this.form.' . $this->getProperty('command_field') .
            '.value = \'' . $this->getObjectId() . '\'; ' . $submit . '"';
    }

    function getTracker()
    {
        $form =& $this->getOwner();
        return $form->getProperty('tracker');
    }

    function handleClick()
    {
        include_once(ECLIPSE_ROOT . 'Url.php');
        $tracker =  $this->getTracker();
        $user    =& $this->getUser();
        $history =& $user->getHistory();
        $history->push($_POST['_url']);
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

    function getErrorMessage()
    {
        return '';
    }
}
?>
