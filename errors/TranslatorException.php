<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class TranslatorException extends Exception
{
    var $language;
    
    function TranslatorException($language)
    {
        $this->Exception();
        $this->language = $language;
    }
    
    function getName()
    {
        return 'Translation Files Missing';
    }
    
    function getDescription()
    {
        return "The translation files for the language <b>" . $this->language .
            "</b> do not exist. There is no system dictionary, nor is there " .
            "an application specific dictionary.";
    }
}
?>
