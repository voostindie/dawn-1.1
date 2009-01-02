<?php
require_once(DAWN_SYSTEM . 'User.php');

class StaticUser extends User
{
    function StaticUser()
    {
        $this->User();
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('id'         , 1);
        $this->setProperty('username'   , 'default');
        $this->setProperty('description', 'Default User');
    }
    
    function getId()
    {
        return $this->getProperty('id');
    }
    
    function getLogin()
    {
        return $this->getProperty('username');
    }
    
    function getDescription()
    {
        return $this->getProperty('description');
    }
    
    function isValid()
    {
        return true;
    }
}
?>
