<?php
require_once(DAWN_SYSTEM . 'Cache.php');

/***
 * Class <code>Factory</code> provides basic functionality for factory classes.
 * <p>
 * All dynamically created objects in the system are instantiated in the same
 * way by some sort of factory. This is the base class for all those factories.
 * Although this class cannot create objects itself, it implements all of the
 * functionality needed to perform object creation in a uniform way, as
 * described below in detail.
 * <p>
 * <p>
 * Objects that can be created by factories must either be placed in an
 * application-specific directory, or in a system-wide Dawn-directory.
 * In those directories a file <code>aliases.dat</code> must be present that
 * defines aliases for the various classes. An example for widgets:
 * </p>
 * <pre><code>    alias | class
 *    text  | TextWidget
 *    edit  | EditWidget
 *    label | LabelWidget</pre></code>
 * <p>
 * Aliases in the application-specific directory override those in the
 * system-wide directory. This makes it possible to replace some classes with
 * others, while retaining the possibility to use those classes as superclasses.
 * For example, one might implement a <code>FancyTextWidget</code> class that
 * is a subclass of <code>TextWidget</code>. By giving this class the alias
 * <code>text</code>, it will be used in the application instead of the normal
 * <code>TextWidget</code>. This is very useful, as Dawn makes extensive use of
 * default settings in case of omissions.
 * </p>
 ***/
class Factory
{
    // DATA MEMBERS

    /***
     * The name of this factory
     * @type string
     ***/
    var $name;

    /***
     * The directory with system classes
     * @type string
     ***/
    var $sysDirectory;

    /***
     * The directory with application-specific classes
     * @type string
     ***/
    var $appDirectory;

    /***
     * The system aliases
     * @type array
     ***/
    var $sysAliases;

    /***
     * The application-specific aliases
     * @type array
     ***/
    var $appAliases;

    // CREATORS

    /***
     * Construct a new factory. Both the system aliases and the
     * application-specific aliases (if they exist) are read into memory
     * immediately.
     * @param $systemDirectory the directory with system aliases
     * @param $applicationDirectory the directory with application-specific
     * aliases; if there are none, <code>''</code> can be supplied
     ***/
    function Factory($name, $systemDirectory, $applicationDirectory)
    {
        assert('Debug::checkState("Factory", DEBUG_STATE_CREATE)');
        $this->name         = $name;
        $this->sysDirectory = $systemDirectory;
        $this->sysAliases   = $this->readAliases(
            'system',
            $this->sysDirectory. 'aliases.dat'
        );
        $this->appDirectory = $applicationDirectory;
        $this->appAliases   = array();
        if (!empty($this->appDirectory))
        {
            $this->appAliases = $this->readAliases(
                'application',
                $this->appDirectory . 'aliases.dat'
            );
        }
    }

    // MANIPULATORS

    /***
     * Read aliases into memory. If a valid cache file exists, it is read into
     * memory immediately. If the cache file doesn't exist or is invalid, the
     * aliases file is read, after which it is stored in the cache.
     * @param $cache a prefix to the cache file; either 'system' or
     *        'application'
     * @param $list a reference to the list in which aliases must be stored
     * @param $file the name of the aliases file
     * @returns void
     * @private
     ***/
    function readAliases($type, $filename)
    {
        $cache = $this->name . '_factory_' . $type . '_aliases';
        $result = Cache::loadData($cache);
        if ($result === NULL)
        {
            include_once(ECLIPSE_ROOT . 'DataFile.php');
            include_once(ECLIPSE_ROOT . 'DataFileReader.php');
            include_once(ECLIPSE_ROOT . 'DataFileIterator.php');
            $result = array();
            $file =& new DataFile($filename, new DataFileReader);
            for ($it =& new DataFileIterator($file); $it->isValid(); $it->next())
            {
                $record =& $it->getCurrent();
                $result[$record['alias']] = $record['class'];
            }
            Cache::saveData($cache, $result);
        }
        return $result;
    }

    /***
     * Given an alias, make the appropriate class available to the system and
     * return the name of the class. If the alias is invalid, this method
     * returns <code>false</code>
     * @param $alias the alias for the class to include
     * @returns string
     * @protected
     ***/
    function getClass($alias)
    {
        assert('Debug::checkState("Factory", DEBUG_STATE_CREATE)');
        $class = $this->getClassName($alias);
        $path  = $this->getClassPath($alias);
        if ($class === false || $path === false)
        {
            include_once(DAWN_EXCEPTIONS . 'ClassNotFoundException.php');
            $exception =& new ClassNotFoundException(
                $alias,
                $this->sysDirectory,
                $this->appDirectory
            );
            $exception->halt();
        }
        $filename = $path . $class . '.php';
        if (!file_exists($filename))
        {
            include_once(DAWN_EXCEPTIONS . 'FileNotFoundException.php');
            $exception =& new FileNotFoundException($filename);
            $exception->halt();
        }
        include_once($filename);
        return $class;
    }

    // ACCESSORS

    function classExists($alias)
    {
        assert('Debug::checkState("Factory", DEBUG_STATE_CREATE)');
        return isset($this->appAliases[$alias])
            || isset($this->sysAliases[$alias]);
    }

    /***
     * Get the name of a class, given the alias, or <code>false</code> if the
     * alias is invalid
     * @param $alias the alias for the class to get the name of
     * @returns string
     ***/
    function getClassName($alias)
    {
        assert('Debug::checkState("Factory", DEBUG_STATE_CREATE)');
        if (isset($this->appAliases[$alias]))
        {
            return $this->appAliases[$alias];
        }
        if (isset($this->sysAliases[$alias]))
        {
            return $this->sysAliases[$alias];
        }
        return false;
    }

    /***
     * Get the path to a class, given the alias, or <code>false</code> if the
     * alias is invalid
     * @param $alias the alias for the class to get the path of
     * @returns string
     ***/
    function getClassPath($alias)
    {
        assert('Debug::checkState("Factory", DEBUG_STATE_CREATE)');
        if (isset($this->appAliases[$alias]))
        {
            return $this->appDirectory;
        }
        if (isset($this->sysAliases[$alias]))
        {
            return $this->sysDirectory;
        }
        return false;
    }

    /***
     * Get the full path to a class given its alias. If the alias is invalid,
     * false is returned.
     * @param $alias the alias for the class to get the full path to
     ***/
    function getFullClassPath($alias)
    {
        $path  = $this->getClassPath($alias);
        $class = $this->getClassName($alias);
        if ($path && $class)
        {
            return $path . $class . '.php';
        }
        return false;
    }
}
?>
