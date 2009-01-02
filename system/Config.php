<?php
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

define('DAWN_CONFIG_SINGLETON', 'DAWN_CONFIG_SINGLETON');

/***
 * Class <code>Config</code> reads and preprocesses configuration files.
 * <p>
 *   The full configuration for a <b>Dawn</b> application can be stored in
 *   multiple files, by using <code>include</code>-statements inside
 *   configuration files. For example:
 * </p>
 * <pre>
 *   database {
 *     tables {
 *       include: 'book.cfg, author.cfg';
 *     }
 *   }
 * </pre>
 * <p>
 *   This example includes the contents of the configuration files
 *   <code>book.cfg</code> and <code>author.cfg</code> inside the
 *   <code>database.tables</code> entry of the main configuration file. This
 *   allows application developers to implement a configuration with a set
 *   of simple configuration files, instead of one big complicated file.
 * </p>
 * <p>
 *   As it is hard for the system to find out which configuration files to read
 *   (and in what order), this class is used to generate a single, combined
 *   configuration file.
 * </p>
 * <p>
 *   ...
 * </p>
 ***/
class Config
{
    // DATA MEMBERS
    
    /***
     * The configuration read from file
     * @type array
     ***/
    var $config;
    
    // CREATORS
    
    /***
     * Create a new <code>Config</code> object for a file and preprocess it
     * immediately. If the filename is <code>MAIN_CONFIG</code>, the 
     * configuration is read from and/or saved to cache.
     * @param $filename the name of the file to read and preprocess
     * @param $relative whether the filename is relative (in APP_CONFIG) or
     * absolute
     ***/
    function Config($filename, $relative = true)
    {
        assert('Debug::checkState("Config", DEBUG_STATE_CREATE)');
        if ($relative && $filename == MAIN_CONFIG)
        {
            $this->config = Cache::loadData('main_config');
            if ($this->config !== false)
            {
                return;
            }
        }
        include_once(DAWN_SYSTEM . 'ConfigReader.php');
        if ($relative)
        {
            $reader =& new ConfigReader(APP_CONFIG . $filename);
        }
        else
        {
            $reader =& new ConfigReader($filename);
        }
        $this->config = $this->processArray($reader->getConfig());
        if ($filename == MAIN_CONFIG)
        {
            Cache::saveData('main_config', $this->config);
        }
    }
    
    /***
     * Process an array recursively: all keys are set to lower case, values that
     * are not arrays themselves are processed with <code>processValue</code>,
     * except when the key is <code>include</code>, in which case the 
     * configuration file denoted by the value is read merged into array.
     * @param $array the array to process
     * @returns array
     * @private
     ***/
    function processArray($array)
    {
        $result   = array();
        $includes = array();
        for ($it =& new ArrayIterator($array); $it->isValid(); $it->next())
        {
            $key     = strtolower($it->getKey());
            $current = $it->getCurrent();
            if ($key == 'include')
            {
                $includes = array_map('trim', explode(',', $current));
                continue;
            }
            if (is_array($current))
            {
                $result[$key] = $this->processArray($current);
            }
            else
            {
                $result[$key] = $this->processValue($current);
            }
        }
        foreach ($includes as $filename)
        {
            $config =& new Config($filename);
            $result = array_merge($result, $config->getConfig());
        }
        return $result;
    }
    
    /***
     * Process a value by translating English boolean values ('true', 'yes', 
     * 'on', 'false', 'no' and 'off') to real booleans.
     * @param $value the value to process
     * @returns mixed
     * @private
     ***/
    function processValue($value)
    {
        switch (strtolower($value))
        {
            case 'true':
            case 'yes':
            case 'on':
                return true;
            case 'false':
            case 'no':
            case 'off':
                return false;
            default:
                return $value;
        }   
    }
    
    // ACCESSORS
    
    /***
     * Get a reference to an entry. If the entry doesn't exist, an exception
     * is thrown. An entry is a string with a set of keys with the character 
     * '<code>.</code>' in between every two keys, e.g. 
     * <code>screens.about.title</code>.
     * @param $entry the entry to find
     * @returns array
     ***/
    function &getEntry($entry, $default = false)
    {
        $root =& $this->config;
        $it   =& new ArrayIterator(explode('.', $entry));
        for ( ; $it->isValid(); $it->next())
        {
            if (!isset($root[$it->getCurrent()])) 
            {
                if ($default !== false)
                {
                    return $default;
                }
                include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
                $exception =& new ConfigException($entry);
                $exception->halt();
            }
            $root =& $root[$it->getCurrent()];
        }
        return $root;
    }

    /***
     * Get the one and only instance of the main configuration object. If
     * it doesn't yet exist, it is created
     * @returns Config
     ***/
    function &getInstance()
    {
        assert('Debug::checkState("Config", DEBUG_STATE_CREATE)');
        if (!isset($GLOBALS[DAWN_CONFIG_SINGLETON]))
        {
            $GLOBALS[DAWN_CONFIG_SINGLETON] =& new Config(MAIN_CONFIG);
        }
        return $GLOBALS[DAWN_CONFIG_SINGLETON];
    }
    
    /***
     * Get the configuration as an array
     * @returns array
     ***/
    function getConfig()
    {
        assert('Debug::checkState("Config", DEBUG_STATE_CREATE)');
        return $this->config;
    }
}
?>
