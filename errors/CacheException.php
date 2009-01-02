<?php
require_once(DAWN_SYSTEM . 'Exception.php');

define('DAWN_CACHE_LOAD_EXCEPTION', 1);
define('DAWN_CACHE_SAVE_EXCEPTION', 2);

class CacheException extends Exception
{
    var $filename;
    var $mode;
    
    function CacheException($filename, $mode)
    {
        $this->Exception();
        $this->filename = $filename;
        $this->mode     = $mode;
    }
    
    function getName()
    {
        switch($this->mode)
        {
            case DAWN_CACHE_LOAD_EXCEPTION:
                return 'Could Not Load From Cache';
            case DAWN_CACHE_SAVE_EXCEPTION:
                return 'Could Not Save To Cache';
        }
        return parent::getName();
    }
    
    function getDescription()
    {
        switch($this->mode)
        {
            case DAWN_CACHE_LOAD_EXCEPTION:
                $state = ' for reading';
                break;
            case DAWN_CACHE_SAVE_EXCEPTION:
                $state = ' for writing';
                break;
            default:
                $state = '';
        }
        return "The cache file <code>$this->filename</code> could not be " .
            "opened$state. Please check the file permissions.";
    }
}
?>
