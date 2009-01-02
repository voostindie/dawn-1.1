<?php
require_once(DAWN_SYSTEM . 'Locale.php');
require_once(ECLIPSE_ROOT . 'DataFile.php');
require_once(ECLIPSE_ROOT . 'DataFileReader.php');
require_once(ECLIPSE_ROOT . 'DataFileIterator.php');

define('DAWN_TRANSLATOR_SINGLETON', 'DAWN_TRANSLATOR_SINGLETON');

/***
 * Class <code>Translator</code> looks up identifiers in a dictionary and
 * returns their translation.
 * <p>
 *   A dictionary is a simple <code>DataFile</code> storing (key, value)-pairs.
 *   Searching for identifier is a slow process, which is why objects should 
 *   cache these results. Because the system cache is language specific, the 
 *   translator (and thus the dictionaries) will not be used in a completed 
 *   application.
 * </p>
 * <p>
 *   If possible, the <code>Translator</code> uses two dictionaries: a system
 *   wide dictionary and an application specific one. The latter overrides the
 *   first, so application developers can change translations for parts of the
 *   language if they so desire. If no dictionary can be found at all for some
 *   language, the <code>Translator</code> throws an exception and the
 *   application is halted immediately.
 * </p>
 * <p>
 *   To use the dictionary, all that is needed is to call the static method
 *   <code>getText</code> with the identifier to translate. If necessary, the
 *   singleton <code>Translator</code> will be created and initialized to the
 *   language from the current <code>Locale</code> automatically.
 * </p>
 * <p>
 *   In order to make sure class <code>Translator</code> is not used in a
 *   completely finished (and cached) application, only cacheable objects are
 *   allowed to use it, and then only in one of its creation methods 
 *   (<code>preCreate</code>, <code>create</code> or <code>postCreate</code>).
 * </p>
 ***/
class Translator
{
    // DATA MEMBERS
    
    /***
     * The system dictionary
     * @type DataFile
     ***/
    var $sysDictionary;
    
    /***
     * The application dictionary
     * @type DataFile
     ***/
    var $appDictionary;

    // CREATORS
    
    /***
     * Create a new translator for the language <code>$language</code>. If the
     * language doesn't exist in both the system and the application, an
     * exception is thrown.
     * @param $language the language to create the translator for
     * @private
     ***/
    function Translator($language)
    {
        $file = $language . '.dat';
        unset($this->sysDictionary);
        if (file_exists(DAWN_TRANSLATIONS . $file))
        {
            $this->sysDictionary =& new DataFile(
                DAWN_TRANSLATIONS . $language . '.dat',
                new DataFileReader()
            );
        }
        unset($this->appDictionary);
        if (APP_TRANSLATIONS != '' && file_exists(APP_TRANSLATIONS . $file))
        {
            $this->appDictionary =& new DataFile(
                APP_TRANSLATIONS . $language . '.dat',
                new DataFileReader()
            );
        }
        if (!isset($this->sysDictionary) && !isset($this->appDictionary))
        {
            include_once(DAWN_EXCEPTIONS . 'TranslatorException.php');
            $exception =& new TranslatorException($language);
            $exception->halt();
        }
    }
    
    // MANIPULATORS
    
    /***
     * Lookup an identifier in a dictionary and translate it. If the identifier
     * wasn't found, <code>false</code> is returned.
     * @returns string
     * @private
     ***/
    function lookup(&$dictionary, $identifier)
    {
        if (!isset($dictionary))
        {
            return false;
        }
        $it =& new DataFileIterator($dictionary);
        for ( ; $it->isValid(); $it->next())
        {
            $record =& $it->getCurrent();
            if ($record['identifier'] == $identifier)
            {
                return $record['translation'];
            }
        }
        return false;
    }
    
    /***
     * Translate an identifer. First the application specific dictionary is
     * checked, and if the identifier wasn't found, the system dictionary is
     * checked. If the identifier still can't be found, the identifier itself
     * is returned.
     * @returns string
     * @private
     ***/
    function translate($identifier)
    {
        assert('Debug::log("Translator: looking up \'$identifier\'.")');
        $text = $this->lookup($this->appDictionary, $identifier);
        if ($text !== false)
        {
            return $text;
        }
        $text = $this->lookup($this->sysDictionary, $identifier);
        if ($text !== false)
        {
            return $text;
        }
        assert('Debug::log("Translator: no translation for \'$identifier\'.", ' .
               'DEBUG_LEVEL_WARNING)');
        return $identifier;
    }
    
    /***
     * Get the one and only instance of the <code>Translator</code> singleton.
     * If the translator doesn't yet exist, it is created by requesting the
     * current language from the <code>Locale</code> singleton.
     * @returns Translator
     * @static
     ***/
    function &getInstance()
    {
        if (!isset($GLOBALS[DAWN_TRANSLATOR_SINGLETON]))
        {
            $locale =& Locale::getInstance();
            $GLOBALS[DAWN_TRANSLATOR_SINGLETON] =& new Translator(
                $locale->getLanguage()
            );
        }
        return $GLOBALS[DAWN_TRANSLATOR_SINGLETON]; 
    }

    // ACCESSORS

    /***
     * @returns string
     * @static
     ***/
    function getText()
    {
        assert('Debug::checkState("Translator", DEBUG_STATE_CREATE)');
        $translator =& Translator::getInstance();
        $identifier = '';
        for ($i = 0, $j = func_num_args(); $i < $j; $i++)
        {
            $identifier  = func_get_arg($i);
            $translation = $translator->translate($identifier);
            if ($identifier != $translation)
            {
                return $translation;
            }
        }
        return $identifier;
    }

    /***
     * @returns string
     * @static
     ***/
    function resolveText($text)
    {
        assert('Debug::checkState("Translator", DEBUG_STATE_CREATE)');
        $translator =& Translator::getInstance();
        $id         =  strtoupper($text);
        for ($i = 1, $j = func_num_args(); $i < $j; $i++)
        {
            $identifier = func_get_arg($i) . '_' . $id;
            $translation = $translator->translate($identifier);
            if ($identifier != $translation)
            {
                return $translation;
            }
        }
        $translation = $translator->translate($id);
        if ($id != $translation)
        {
            return $translation;
        }
        return ucfirst($text);
    }
}
?>
