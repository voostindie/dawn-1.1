<?php
require_once(DAWN_SYSTEM . 'Form.php');

class DatabaseForm extends Form
{
    function DatabaseForm($name, &$page)
    {
        $this->Form($name, $page);
    }
    
    function &getDatabaseManager()
    {
        $application =& $this->getApplication();
        return $application->getDatabaseManager();   
    }
    
    function &getDatabase()
    {
        $manager =& $this->getDatabaseManager();
        return $manager->getDatabase();
    }
    
    function &getTable($name)
    {
        $manager =& $this->getDatabaseManager();
        return $manager->getTable($name);
    }
    
    function getFormMethod()
    {
        return 'post';
    }
}
?>
