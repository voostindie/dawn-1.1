<?php
require_once(DAWN_FORMS . 'RecordForm.php');

class RecordInsertForm extends RecordForm
{
    function RecordInsertForm($name, &$page)
    {
        $this->RecordForm($name, $page, false);
    }

    function getValidButtons()
    {
        return array('insert', 'cancel');
    }

    function getEmptyField($name)
    {
        $table =& $this->getActiveTable();
        $field =& $table->getField($name);
        return $field->getDefault();
    }

    function getDefaultFieldWidget()
    {
        return 'text';
    }
    
    function getDefaultLookupWidget()
    {
        return 'table_lookup';
    }
}
?>
