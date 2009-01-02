<?php
require_once(DAWN_WIDGETS . 'ButtonWidget.php');

class RelationEditButtonWidget extends ButtonWidget
{
    var $key;

    function RelationEditButtonWidget($id, &$form)
    {
        $this->ButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('key_field', OBJECT_INVALID_VALUE);
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
        $result = $config->getEntry('pages.' . $page, false);
        if ($result === false)
        {
            // TODO: throw an exception
            exit('Invalid target!');
        }
        $result = $config->getEntry(
            'pages.' . $page . '.forms.' . $tracker, false
        );
        if ($result === false)
        {
            // TODO: throw an exception
            exit('Missing tracker on page!');
        }
    }

    function getOptions()
    {
        return 'onclick="this.form.' . $this->getProperty('command_field') .
            '.value = \'' . $this->getObjectId() . '\'; ' .
            'this.form.' . $this->getProperty('key_field') . '.value = \'' .
            addslashes($this->key) . '\'; this.form.submit();"';
    }

    function getTracker()
    {
        $form =& $this->getOwner();
        return $form->getProperty('tracker');
    }

    function handleClick()
    {
        include_once(ECLIPSE_ROOT . 'Url.php');
        $user    =& $this->getUser();
        $history =& $user->getHistory();
        $history->push($_POST['_url']);
        $url     =& new Url(APP_URL);
        $url->setParameter('page', $this->getProperty('target'));
        $url->setParameter(
            $this->getProperty('tracker'),
            $_POST[$this->getProperty('key_field')]
        );
        header('Location: ' . $url->getUrl());
        exit;
    }

    function setKey($key)
    {
        $this->key = $key;
    }

    function getDefaultLayout()
    {
        return 'empty';
    }

    function getErrorMessage()
    {
        return "This is impossible!";
    }

}
?>
