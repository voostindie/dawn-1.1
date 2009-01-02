<?php
require_once(DAWN_WIDGETS . 'RecordButtonWidget.php');

class RecordDeleteButtonWidget extends RecordButtonWidget
{
    function RecordDeleteButtonWidget($id, &$form)
    {
        $this->RecordButtonWidget($id, $form);
    }

    function postCreate()
    {
        parent::postCreate();
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty(
            'error',
            Translator::getText('ERROR_RECORD_DELETE')
        );
        $question = addslashes(Translator::getText('BUTTON_DELETE_QUESTION'));
        $form =& $this->getOwner();
        $page =& $form->getPage();
        $script = <<<EOF_SCRIPT
function confirmDelete(form, command)
{
    if (confirm('$question'))
    {
        form.elements[command].value = 'delete';
        form.submit();
    }
    return false;
}

EOF_SCRIPT;
        $page->addScript('confirmDelete', $script);
    }

    function handleClick()
    {
        if (!$this->deleteRecord())
        {
            return false;
        }
        header('Location: ' . $_POST['_url']);
        exit;
        return true;
    }

    function deleteRecord()
    {
        $activeRecord  =& $this->getActiveRecord();
        if ($activeRecord === false)
        {
            // TODO: translate this message
            $this->setErrorMessage('No active record');
            return false;
        }
        $table   =& $activeRecord->getTable();
        $command =& $table->getDeleteCommand();
        $command->setOldRecord($activeRecord);
        if (!$command->run())
        {
            $this->setErrorMessage($command->getErrorMessage());
            return false;
        }
        $this->updateTracker();
        return true;
    }

    function updateTracker()
    {
        $url     =& new Url($_POST['_url']);
        $form    =& $this->getOwner();
        $tracker =  $form->getProperty('tracker');
        $url->setParameter($tracker);
        $_POST['_url'] = $url->getUrl();
    }

    function showWidget($indent)
    {
        if ($this->getActiveRecord() !== false)
        {
            parent::showWidget($indent);
        }
    }

    function getOptions()
    {
        return 'onclick="confirmDelete(this.form, \'' .
            $this->getProperty('command_field') . '\');"';
    }

    function needsPost()
    {
        return false;
    }

    function setErrorMessage($message)
    {
        parent::setErrorMessage(
            str_replace('%1', $message, $this->getProperty('error'))
        );
    }
}
?>
