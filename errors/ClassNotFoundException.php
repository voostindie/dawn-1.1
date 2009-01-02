<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class ClassNotFoundException extends Exception
{
    var $alias;
    var $sysDirectory;
    var $appDirectory;
    
    function ClassNotFoundException($alias, $sysDirectory, $appDirectory)
    {
        $this->Exception();
        $this->alias        = $alias;
        $this->sysDirectory = $sysDirectory;
        $this->appDirectory = $appDirectory;
    }
    
    function getName()
    {
        return 'Class Not Found';
    }
    
    function getDescription()
    {
        $directories = array("<code>$this->sysDirectory</code>");
        if (!empty($this->appDirectory))
        {
            array_push($directories, "<code>$this->appDirectory</code>");
        }
        return  "The class with alias <b>$this->alias</b> could not be found " .
            "in the directory " . join(' and ', $directories) . ".";
    }
}
?>
