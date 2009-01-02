<?php
require_once(DAWN_WIDGETS . 'ButtonWidget.php');

class RelationDeleteButtonWidget extends ButtonWidget
{
    var $key;
    var $error;

    function RelationDeleteButtonWidget($id, &$form)
    {
        $this->ButtonWidget($id, $form);
        $this->error = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('key_field', OBJECT_INVALID_VALUE);
        $this->setProperty('target', '');
    }

    function postCreate()
    {
        parent::postCreate();
        include_once(DAWN_SYSTEM . 'Translator.php');
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
        $this->deleteProperty('target');
    }

    function getOptions()
    {
        return 'onclick="this.form.' . $this->getProperty('key_field') .
            '.value = \'' .addslashes($this->key) .
            '\'; confirmDelete(this.form, \'' .
            $this->getProperty('command_field') . '\');"';
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
        include_once(DAWN_SYSTEM . 'Record.php');
        $form   =& $this->getOwner();
        $table  =& $form->getTable($form->getProperty('table'));
        $fields =  $table->getPrimaryKey();
        $values =  Table::decodeValues($_POST['_key']);
        $size   =  count($fields);
        $key    =  array();
        for ($i = 0; $i < $size; $i++)
        {
            $key[$fields[$i]] = $values[$i];
        }
        $record  =& new Record($table, $key);
        $command =& $table->getDeleteCommand();
        $command->setOldRecord($record);
        if (!$command->run())
        {
            $this->error = $command->getErrorMessage();
            return false;
        }
        return true;
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
        return $this->error;
    }
}
?>
