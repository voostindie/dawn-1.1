<?php
require_once(DAWN_WIDGETS . 'ButtonWidget.php');

class RecordButtonWidget extends ButtonWidget
{
    var $error;

    function RecordButtonWidget($id, &$form)
    {
        $this->ButtonWidget($id, $form);
        $this->error = '';
    }
    
    function setErrorMessage($error)
    {
        $this->error = $error;
    }

    function checkTarget($page)
    {
        $owner   =& $this->getOwner();
        $tracker =  $owner->getProperty('tracker');
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

    function handleClick()
    {
        // TODO: add Debug log messages here
        return true;
    }

    function getErrorMessage()
    {
        return $this->error;
    }

    function getOptions()
    {
        $submit = $this->getProperty('default')
            ? ' return true;'
            : $submit = ' this.form.submit();';
        return 'onclick="this.form.' . $this->getProperty('command_field') .
            '.value = \'' . $this->getObjectId() . '\';' . $submit . '"';
    }

    function &getActiveRecord()
    {
        $owner =& $this->getOwner();
        return $owner->getActiveRecord();
    }

    function &getPostedRecord()
    {
        $owner =& $this->getOwner();
        return $owner->getPostedRecord();
    }

    function getTracker()
    {
        $window =& $this->getOwner();
        return $window->getProperty('tracker');
    }

    function &getUserHistory()
    {
        $window =& $this->getOwner();
        $user   =& $window->getUser();
        return $user->getHistory();
    }
    
    function needsPost()
    {
        return true;
    }
}
?>
