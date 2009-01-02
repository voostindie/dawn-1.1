<?php
require_once(DAWN_WIDGETS . 'RecordButtonWidget.php');

class RecordInsertButtonWidget extends RecordButtonWidget
{
    function RecordInsertButtonWidget($id, &$form)
    {
        $this->RecordButtonWidget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('default', true);
        $this->setProperty('target', '');
    }

    function postCreate()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty(
            'error',
            Translator::getText('ERROR_RECORD_INSERT')
        );
        parent::postCreate();
    }

    function handleClick()
    {
        if (!$this->insertRecord())
        {
            return false;
        }
        $history =& $this->getUserHistory();
        header('Location: ' . $history->pop());
        exit;
        return true;
    }

    function insertRecord()
    {
        $postedRecord =& $this->getPostedRecord();
        if ($postedRecord == false)
        {
            // TODO: throw an excepton
            exit('No posted record; nothing to insert!');
        }
        $table   =& $postedRecord->getTable();
        $command =& $table->getInsertCommand();
        $command->setNewRecord($postedRecord);
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
        $record  =& $this->findNewRecord();
        if ($record === false)
        {
            return;
        }
        $key     =  array();
        $table   =& $record->getTable();
        $it     =& new ArrayIterator($table->getPrimaryKey());
        for ( ; $it->isValid(); $it->next())
        {
            array_push($key, $record->getValue($it->getCurrent()));
        }
        $history =& $this->getUserHistory();
        $url     =& new Url($history->pop());
        if ($this->getProperty('target') != '')
        {
            $url->setParameter('page', $this->getProperty('target'));
        }
        $form    =& $this->getOwner();
        $tracker =  $form->getProperty('tracker');
        if ($url->hasParameter($tracker))
        {
            $url->setParameter($tracker, Table::encodeValues($key));
        }
        $history->push($url->getUrl());
    }

    function findNewRecord()
    {
        $fields        =  array();
        $postedRecord  =& $this->getPostedRecord();
        $it            =& new ArrayIterator($postedRecord->getFields());
        for ( ; $it->isValid(); $it->next())
        {
            $name = $it->getCurrent();
            if (!$postedRecord->hasField($name))
            {
                continue;
            }
            $field =& $postedRecord->getField($name);
            $value =  $postedRecord->getValue($name);
            if ($postedRecord->hasValue($name) &&
                $field->isOrdered() &&
                !$field->isNull($value))
            {
                array_push($fields, $name . ' = ' . $field->getSql($value));
            }
        }
        $table =& $postedRecord->getTable();
        $key   =  array();
        $it    =& new ArrayIterator($table->getPrimaryKey());
        for ( ; $it->isValid(); $it->next())
        {
            $field =& $table->getField($it->getCurrent());
            array_push(
                $key,
                $field->getSelect($it->getCurrent())
                    . ' AS ' . $it->getCurrent()
            );
        }
        $sql   =  'SELECT ' . join(', ', $key) .
                  ' FROM '  . $table->getName() .
                  ' WHERE ' . join(' AND ', $fields);
        $database =& $table->getDatabase();
        $result   =& $database->query($sql);
        if ($result->getRowCount() == 0)
        {
            return false;
        }
        return new Record(
            $postedRecord->getTable(),
            $result->getRow($result->getRowCount() - 1, ECLIPSE_DB_ASSOC)
        );
    }

    function setErrorMessage($message)
    {
        parent::setErrorMessage(
            str_replace('%1', $message, $this->getProperty('error'))
        );
    }
}
?>
