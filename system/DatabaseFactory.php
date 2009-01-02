<?php
/***
 * Class <code>DatabaseFactory</code> is a simple class to map a 
 * DBMS-description to an <b>Eclipse</b> class.
 * <p>
 *   <b>Dawn</b> uses <b>Eclipse</b> to create connections to various DBMSs.
 * </p>
 ***/
class DatabaseFactory
{
    /***
     * Return the name of the Eclipse database-class to use, given a description
     * for the database system. If the description is unknown, an exception is
     * thrown.
     * @param $type the name of the database system to use
     * @returns string
     ***/
    function getClass($type)
    {
        assert('Debug::checkState("DatabaseFactory", DEBUG_STATE_CREATE)');
        switch (strtolower($type))
        {
            case 'microsoft sql server':
            case 'ms-sql':
            case 'mssql':
                return 'MSDatabase';
            case 'mysql':
                return 'MyDatabase';
            case 'sybase':
                return 'SyDatabase';
            case 'postgresql':
            case 'pgsql':
            case 'psql':
                return 'PgDatabase';
        }
        $valid = array(
            'microsoft sql server', 'ms-sql', 'mssql',
            'mysql',
            'sybase',
            'postgresql', 'pgsql', 'psql'
        );
        include_once(DAWN_EXCEPTIONS . 'DatabaseException.php');
        $exception =& new DatabaseException($type, $valid);
        $exception->halt();
    }
}
?>
