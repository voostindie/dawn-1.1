<?php
require_once(DAWN_SYSTEM . 'Factory.php');

define('DAWN_COMMAND_FACTORY', 'DAWN_COMMAND_FACTORY');

class CommandFactory extends Factory
{
    function &getInstance()
    {
        if (!isset($GLOBALS[DAWN_COMMAND_FACTORY]))
        {
            $GLOBALS[DAWN_COMMAND_FACTORY] =& new CommandFactory(
                'command', 
                DAWN_COMMANDS,
                APP_COMMANDS
            );
        }
        return $GLOBALS[DAWN_COMMAND_FACTORY];
    }
    
    function &createCommand($name, &$table, $alias)
    {
        $class = $this->getClass($alias);
        return new $class($name, $table);
    }
}
?>
