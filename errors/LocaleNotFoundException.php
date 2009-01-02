<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class LocaleNotFoundException extends Exception
{
    var $locale;
    
    function LocaleNotFoundException($locale)
    {
        $this->Exception();
        $this->locale = $locale;
    }
    
    function getName()
    {
        return 'Locale Settings Not Found';
    }
    
    function getDescription()
    {
        return "The settings for the locale <code>$this->locale</code> could " .
            "not be found.";
    }
}
?>
