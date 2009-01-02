<?php
require_once(DAWN_WIDGETS . 'RecordButtonWidget.php');

class RecordUpdateButtonWidget extends RecordButtonWidget
{
    function RecordUpdateButtonWidget($id, &$form)
    {
        $this->RecordButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('default', true);
    }

    function postCreate()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty(
            'error',
            Translator::getText('ERROR_RECORD_UPDATE')
        );
        parent::postCreate();
    }

    function handleClick()
    {
        if (!$this->updateRecord())
        {
            return false;
        }
        $history =& $this->getUserHistory();
        header('Location: ' . $history->pop());
        exit;
        return true;
    }

    function updateRecord()
    {
        $activeRecord  =& $this->getActiveRecord();
        if ($activeRecord === false)
        {
            // TODO: translate this message
            $this->setErrorMessage('No active record');
            return false;
        }
        $postedRecord =& $this->getPostedRecord();
        if ($postedRecord == false)
        {
            // TODO: translate this message
            $this->setErrorMessage('No posted record');
        }
        $table   =& $activeRecord->getTable();
        $command =& $table->getUpdateCommand();
        $command->setOldRecord($activeRecord);
        $command->setNewRecord($postedRecord);
        if (!$command->run())
        {
            $this->setErrorMessage($command->getErrorMessage());
            return false;
        }
        $this->updateTracker($activeRecord, $postedRecord);
        return true;
    }

    function updateTracker(&$oldRecord, &$newRecord)
    {
        $key = $newRecord->getKeySelection();
        if ($key != $oldRecord->getKeySelection())
        {
            $key   =  array();
            $table =& $oldRecord->getTable();
            $it    =& new ArrayIterator($table->getPrimaryKey());
            for ( ; $it->isValid(); $it->next())
            {
                array_push($key, $newRecord->getValue($it->getCurrent()));
            }
            $history =& $this->getUserHistory();
            $url     =& new Url($history->pop());
            $form    =& $this->getOwner();
            $tracker =  $form->getProperty('tracker');
            if ($url->hasParameter($tracker))
            {
                $url->setParameter($tracker, Table::encodeValues($key));
            }
            $history->push($url->getUrl());
        }
    }

    function showWidget($indent)
    {
        if ($this->getActiveRecord() !== false)
        {
            parent::showWidget($indent);
        }
    }

    function setErrorMessage($message)
    {
        parent::setErrorMessage(
            str_replace('%1', $message, $this->getProperty('error'))
        );
    }
}
?>
