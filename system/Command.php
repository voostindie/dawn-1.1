<?php
require_once(DAWN_SYSTEM . 'Object.php');

class Command extends Object
{
    var $table;
    var $transaction;
    
    function Command($name, &$table)
    {
        $this->Object($name);
        $this->table       =& $table;
        $database          =& $table->getDatabase();
        $this->transaction =& $database->createTransaction();
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->registerArgument('type', '');
    }
    
    function postCreate()
    {
        parent::postCreate();
        $this->deleteProperty('type');
    }
    
    function run()
    {
        $this->transaction->begin();
        if ($this->preCommand() && $this->execute() && $this->postCommand())
        {
            $this->transaction->commit();
            return true;
        }
        $this->transaction->rollback();
        return false;
    }
    
    function preCommand()
    {
        return true;
    }
    
    function execute()
    {
        return true;
    }
    
    function postCommand()
    {
        return true;
    }
    
    function getErrorMessage()
    {
        return $this->transaction->getErrorMessage();
    }

    function &getTransaction()
    {
        return $this->transaction;
    }

    function &getTable()
    {
        return $this->table;
    }
    
    function &getDatabaseManager()
    {
        return $this->table->getDatabaseManager();
    }
    
    function &getDatabase()
    {
        return $this->table->getDatabase();
    }
    
    function &getApplication()
    {
        $manager =& $this->getDatabaseManager();
        return $manager->getApplication();
    }
    
    function &getUser()
    {
        $application =& $this->getApplication();
        return $application->getUser();
    }
}
?>
