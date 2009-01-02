<?php
require_once(DAWN_SYSTEM . 'Exception.php');

/***
 * Class <code>ConfigReaderStateException</code> implements the 
 * <code>Exception</code> interface for exceptions thrown by class
 * <code>ConfigReaderState</code>.
 ***/
class ConfigReaderStateException extends Exception
{
    // DATA MEMBERS
    
    /***
     * The <code>ConfigReaderState</code> that threw this exception
     * @type ConfigReaderState
     ***/
    var $state;
    
    /***
     * The type of this exception. This is either <b>BOF</b>, <b>EOF</b> or
     * <b>UNEXPECTED</b>.
     * @type string
     ***/
    var $type;
    
    // CREATORS
    
    /***
     * @param $state the <code>ConfigReaderState</code> that threw this 
     *        exception
     * @param $type the exception type: <b>BOF</b>, <b>EOF</b> or
     *        <b>UNEXPECTED</b>
     ***/
    function ConfigReaderStateException(&$state, $type)
    {
        $this->Exception();
        $this->state =& $state;
        $this->type  =  $type;
        $this->halt();
    }
    
    // ACCESSORS
    
    /***
     * @returns string
     ***/
    function getName()
    {
        switch ($this->type)
        {
            case 'BOF':
                $result = 'Configuration Parser: BOF';
                break;
            case 'EOF':
                $result = 'Configuration Parser: EOF';
                break;
            case 'UNEXPECTED':
                $result = 'Configuration Parser: Unexpected Character';
                break;
            default:
                $result = parent::getName();
        }
        return $result;
    }
     
    /***
     * @returns string
     ***/
    function getDescription()
    {
        switch ($this->type)
        {
            case 'BOF':
                $result = 'The internal file cursor was positioned before the' .
                    ' first character in the file <b>' . 
                    $this->state->filename . '</b>.';
                    break;
            case 'EOF':
                $result = 'The internal file cursor was positioned after the' .
                    ' last character in the file <b>' . 
                    $this->state->filename . '</b>.';
                    break;
            case 'UNEXPECTED':
                $result = 'An unexpected character was found on column ' .
                    $this->state->column . ' of line ' . $this->state->line .
                    ' in file <b>' . $this->state->filename . '</b>.';
                    break;
            default:
                $result = parent::getDescription();
        }
        return $result;
    }   
}
?>
