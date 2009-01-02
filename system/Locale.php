<?php
require_once(DAWN_SYSTEM . 'Cache.php');
require_once(DAWN_SYSTEM . 'Object.php');

define('DAWN_LOCALE_SINGLETON', 'DAWN_LOCALE_SINGLETON');

/***
 * Class <code>Locale</code> stores localization information for a single run of
 * the application.
 * <p>
 *   The most important part of the locale is the language. Dawn looks up all
 *   messages, captions and so in a dictionary. 
 * </p>
 * <p>
 * Class <code>Locale</code> is implemented as a Singleton. Before an instance
 * can be requested with <code>getInstance()</code>, the singleton must first
 * be initialized by calling <code>createInstance($name)</code>, where 
 * <code>$name</code> is the name of the locale.
 * </p>
 ***/

class Locale extends Object
{
    // CREATORS
    
    /***
     * Create a new locale, named by $name
     * @private
     ***/
    function Locale($name)
    {
        $this->Object('locale_' . $name);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('language', OBJECT_INVALID_VALUE);
        $this->setProperty('months'  , OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        $this->setProperty(
            'months', 
            $this->parseList($this->getProperty('months'))
        );
        parent::postCreate();
    }

    // MANIPULATORS

    /***
     * Create the singleton instance of class Locale. If possible, the settings
     * are restored from the cache. If the locale doesn't exist an exception is
     * thrown.
     * @returns Locale
     * @static
     ***/
    function &createInstance($name)
    {
        assert('Debug::log("Locale: creating instance for \'$name\'")');
        $GLOBALS[DAWN_LOCALE_SINGLETON] =& new Locale($name);
        $locale =& $GLOBALS[DAWN_LOCALE_SINGLETON];
        if (!Cache::loadObject($locale, false))
        {
            $file = $name . '.cfg';
            include_once(DAWN_SYSTEM . 'Config.php');
            if (APP_LOCALES != '' && file_exists(APP_LOCALES . $file))
            {
                $file = APP_LOCALES . $file;
            }
            else if (file_exists(DAWN_LOCALES . $file))
            {
                $file = DAWN_LOCALES . $file;
            }
            else
            {
                include_once(DAWN_EXCEPTIONS . 'LocaleNotFoundException.php');
                $exception =& new LocaleNotFoundException($name);
                $exception->halt();
            }
            $config =& new Config($file, false);
            $locale->create($config->getConfig());
            Cache::saveObject($locale, false);
        }
        return $locale;
    }

    /***
     * Get the one and only instance of the <code>Locale</code> singleton.
     * Before this method is called for the first time, the method
     * <code>createInstance</code> must have been called.
     * @static
     ***/
    function &getInstance()
    {
        return $GLOBALS[DAWN_LOCALE_SINGLETON];
    }

    // ACCESSORS

    /***
     * Return the language for this locale
     * @returns string
     ***/
    function getLanguage()
    {
        return $this->getProperty('language');
    }
    
    function getMonth($number)
    {
        $months =& $this->getProperty('months');
        return $months[$number - 1];
    }
}
?>
