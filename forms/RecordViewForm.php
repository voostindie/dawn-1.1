<?php
require_once(DAWN_FORMS . 'RecordForm.php');

class RecordViewForm extends RecordForm
{
    function RecordViewForm($name, &$page)
    {
        $this->RecordForm($name, $page, true);
        $user    =& $this->getUser();
        $history =& $user->getHistory();
        $history->clear();
    }

    function getValidButtons()
    {
        return array('new', 'edit', 'delete');
    }
}
?>
