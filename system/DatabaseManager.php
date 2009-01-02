<?php
require_once(DAWN_SYSTEM . 'Table.php');
require_once(DAWN_SYSTEM . 'Cache.php');

/***
 * Class DatabaseManager manages the database connection as well as the tables
 * in the database.
 * <p>
 *   This class serves as a proxy for the tables. A table is only restored from
 *   cache if it is explicitly requested. This makes sense, as a large database
 *   contains many tables, whereas a typical page on a site uses only a few.
 * </p>
 ***/
class DatabaseManager extends Object
{
    var $database;
    var $tables;

    function DatabaseManager()
    {
        $this->Object('database');
        $this->tables      = array();
        unset($this->database);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('type'       , OBJECT_INVALID_VALUE);
        $this->setProperty('name'       , OBJECT_INVALID_VALUE);
        $this->setProperty('host'       , OBJECT_INVALID_VALUE);
        $this->setProperty('user'       , OBJECT_INVALID_VALUE);
        $this->setProperty('password'   , OBJECT_INVALID_VALUE);
        $this->setProperty('persistent' , false);
        $this->setProperty('connect_sql', '');
        $this->setProperty('tables'     , '');
    }

    function postCreate()
    {
        parent::postCreate();
        include_once(DAWN_SYSTEM . 'DatabaseFactory.php');
        $class = DatabaseFactory::getClass($this->getProperty('type'));
        $this->deleteProperty('type');
        $this->setProperty('class', $class);
        $this->setProperty('path' , ECLIPSE_ROOT . $class . '.php');
        $tables =& $this->getProperty('tables');
        if (is_array($tables))
        {
            $this->setProperty('tables', array_keys($tables));
        }
    }

    function connect()
    {
        assert('Debug::checkState("DatabaseManager", DEBUG_STATE_BUILD)');
        assert('Debug::log(\'DatabaseManager: connecting with database\')');
        include_once($this->getProperty('path'));
        $class          =  $this->getProperty('class');
        $this->database =& new $class(
            $this->getProperty('name'),
            $this->getProperty('host')
        );
        $this->database->connect(
            $this->getProperty('user'),
            $this->getProperty('password'),
            $this->getProperty('persistent')
        );
        if (!$this->database->isConnected())
        {
            include_once(DAWN_EXCEPTIONS . 'ConnectionException.php');
            $exception =& new ConnectionException(
                $this->getProperty('name'),
                $this->getProperty('host')
            );
            $exception->halt();
        }
        if (($sql = $this->getProperty('connect_sql')) != '')
        {
            $it =& new ArrayIterator($this->parseList($sql, ';'));
            for (; $it->isValid(); $it->next())
            {
                $this->database->query($it->getCurrent());
            }
        }
    }

	function disconnect() {
        assert('Debug::checkState("DatabaseManager", DEBUG_STATE_BUILD)');
        assert('Debug::log(\'DatabaseManager: disconnecting from database\')');
		$this->database->disconnect();
	}

    function &getDatabase()
    {
        return $this->database;
    }

    function &getGraph()
    {
        assert('Debug::checkState("DatabaseManager", DEBUG_STATE_BUILD)');
        assert('Debug::log(\'DatabaseManager: loading database graph\')');
        include_once(DAWN_SYSTEM . 'DatabaseGraph.php');
        $graph =& new DatabaseGraph($this);
        if (!Cache::loadObject($graph))
        {
            $graph->create();
            Cache::saveObject($graph);
        }
        return $graph;
    }

    function getTables()
    {
        return $this->getProperty('tables');
    }

    function &getTable($name)
    {
        if (isset($this->tables[$name]))
        {
            return $this->tables[$name];
        }
        $this->tables[$name] = new Table($name, $this);
        Cache::restoreObject(
            $this->tables[$name],
            "database.tables.$name",
	    	true
        );
        return $this->tables[$name];
    }
}
?>
