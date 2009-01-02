<?php
require_once(DAWN_FORMS . 'RecordForm.php');

class RecordEditForm extends RecordForm
{
    function RecordEditForm($name, &$page)
    {
        $this->RecordForm($name, $page, true);
    }

    function getValidButtons()
    {
        return array('update', 'cancel');
    }
    
    function getEmptyField($name)
    {
        return '';
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
