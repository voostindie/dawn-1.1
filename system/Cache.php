<?php
require_once(DAWN_SYSTEM . 'Locale.php');

/***
 * Class Cache is a static class for saving and restoring objects derived from class Object to and
 * from a cache file.
 * In Dawn, the cache is a collection of files in a single directory. This directory should not be
 * used for anything but the cache or the optional log file.
 * This class can be used to read and write cache files directly with the methods loadData() and
 * saveData(). It can also be used to read and write class Object-derived instances, with methods
 * loadObject() and saveObject().
 * Most objects are language specific: depending on the application locale, different captions,
 * labels and messages are stored inside the objects. Therefore, the cache for objects is language
 * specific. The same object can thus have many cache files, each of these in a different language.
 * If objects are not language specific it is not necessary to store it in the cache multiple times.
 * By passing the value false as a second argument to both loadObject() and saveObject(), the
 * locale isn't used. This is also used at application startup to load the main configuration
 * file(s).
 ***/
class Cache {

	// MANIPULATORS

    /**
     * Delete all files from the cache directory. If the cache directory could not be opened, an
     * error occurs and the system halts. This method is called in Debug mode at the start of the
     * application run.
     * @returns void
     */
    function clear() {
        if (!APP_RESET) {
            return true;
        }
        assert('Debug::log(\'Cache: clearing all cache files\')');
        $dp = opendir(APP_CACHE);
        if (!$dp) {
            include_once(DAWN_ERRORS . 'DirectoryNotFoundError.php');
            $error =& new DirectoryNotFoundError(APP_CACHE);
            $error->halt();
        }
        while ($file = readdir($dp)) {
            if ($file[0] != '.' && APP_CACHE . $file != APP_LOG_FILE) {
                unlink(APP_CACHE . $file);
            }
        }
        closedir($dp);
        return true;
    }

    /**
     * Save data to a cache file. If the cache directory could not be opened for writing, an error
     * occurs, halting the system.
     * @param $filename the name of the cache file
     * @param $data the data to store in the file
     * @returns void
     */
    function saveData($filename, $data)
    {
        assert('Debug::checkState("Cache", DEBUG_STATE_CREATE)');
        assert('Debug::log("Cache: saving \'$filename\'")');
        $fp = fopen(APP_CACHE . $filename, 'w');
        if (!$fp) {
            include_once(DAWN_ERRORS . 'CacheError.php');
            $error =& new CacheException($filename, DAWN_CACHE_SAVE_ERROR);
            $error->halt();
        }
        fputs($fp, serialize($data));
        fclose($fp);
    }

    /**
     * Load data from a cache file. If the file exists but could not be opened for reading, an
     * error occurs. If the cache is valid but could not be read, NULL is returned.
     * @param $filename the name of the cache file
     * @returns mixed
     */
    function loadData($filename) {
        assert('Debug::checkState("Cache", DEBUG_STATE_CREATE)');
        if (!file_exists(APP_CACHE . $filename)) {
            return NULL;
        }
        assert('Debug::log("Cache: loading \'$filename\'")');
        $fp = fopen(APP_CACHE . $filename, 'r');
        if (!$fp) {
            include_once(DAWN_ERROR . 'CacheError.php');
            $error =& new CacheError($filename, DAWN_CACHE_LOAD_ERROR);
            $error->halt();
        }
        $data = fgets($fp, filesize(APP_CACHE . $filename) + 1);
        fclose($fp);
        return unserialize($data);
    }

    /**
     * Store an object in the cache. If necessary, the object can be stored in a locale specific
     * file. This allows the same object to be stored in the cache multiple times, once for every
     * language the application supports.
     * @param $object the cacheable object to save
     * @param $useLocale whether this object has locale-specific settings; defaults to true
     * @returns void
     */
    function saveObject(&$object, $useLocale = true) {
        $file = $object->getObjectId();
        if ($useLocale) {
            $locale =& Locale::getInstance();
            $file .= '_' . $locale->getLanguage();
        }
        Cache::saveData($file, $object->getProperties());
    }

    /**
     * Restore an object from the cache, optionally locale-specific. If the object was restored
     * succesfully then true is returned, and false otherwise.
     * @param $object the cacheable object to restore
     * @param $useLocale whether this object has locale-specific settings; defaults to true
     * @returns bool
     */
    function loadObject(&$object, $useLocale = true) {
        $file = $object->getObjectId();
        if ($useLocale) {
            $locale  =& Locale::getInstance();
            $file   .=  '_' . $locale->getLanguage();
        }
        $data = Cache::loadData($file);
        if ($data !== NULL) {
            $object->setProperties($data);
            return true;
        }
        return false;
    }

	/**
     * Restore an (optionally locale-specific) object from cache If the object couldn't be found
     * in the cache, the object is initialized from the specified section in the main configuration
     * file, and saved to the cache immediately afterwards.
     * @param $object the object to restore
     * @param $section the name of the section in the main configuration file
     * @param $useLocale whether this object has locale-specific settings; defaults to true
     * @returns void
     */
    function restoreObject(&$object, $section, $useLocale = false) {
        assert('Debug::log("Cache: restore configuration \'$section\'")');
        if (!Cache::loadObject($object, $useLocale)) {
            include_once(DAWN_SYSTEM . 'Config.php');
            $config =& Config::getInstance();
            $object->create($config->getEntry($section, array()));
            Cache::saveObject($object, $useLocale);
        }
    }
}
?>
