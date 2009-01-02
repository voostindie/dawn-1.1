<?php
define('OBJECT_INVALID_VALUE', '__INVALID__');

/**
 * Class Object is the base class for all cacheable, dynamically created components.
 * The object creation process is as follows:
 * - The constructor is called. Subclasses may override this method to initialize non-cacheable
 *   members (e.g. references to objects).
 * - If the object exists in the cache, it is restored from there (by calling setProperties()), and
 *   no further initialization is necessary.
 * - If the cache is invalid, the following steps are taken:
 *   - The method create() is called together with an array of (cacheable) properties. Before
 *     processing these properties, create() first calls preCreate() to give the object the
 *     chance to register a set of properties. By default the method preCreate() does nothing,
 *     but subclasses can use it to make calls to setProperty(). Before doing so, they should
 *     always first call parent::preCreate().
 *   - If a subclass wants to add a property that must be specified but has no default value,
 *     it can do this by setting the value of the property to OBJECT_INVALID_VALUE. If, after
 *     initialization, a property exists that has a value of OBJECT_INVALID_VALUE, an error
 *     occurs, as no such property is allowed.
 *   - If a subclass wishes to store additional values (or wants to do some computations on the
 *     existing arguments), it can override postCreate(), which does nothing by default, and is
 *     called at the end of create().
 *   - When initialization is completed, all cacheable data is requested from the object (by
 *     calling getProperties()) and it is stored in the cache.
 * The process described above might seem a bit complicated, but it's actually rather simple, and
 * it allows large increases in execution speed: once the cache is valid, there's no need to check
 * properties or to compute additional ones. Instead they are restored instantly.
 */
class Object {

    // DATA MEMBERS

    /**
     * The ID of this object
     */
    var $objectId;

    /**
     * The cacheable data for this object (an array)
     */
    var $properties;

    // CREATORS

    /**
     * Construct a new object
     * @param $oid the object's ID
     */
    function Object($objectId)
    {
        $this->objectId   = $objectId;
        $this->properties = array();
    }

    /**
     * Set up the properties in this object. This method typically makes various calls to the
     * setProperty() method, either with a default value or the constant OBJECT_INVALID_VALUE.
     * When this method is done, this object has registered a list of properties, some of which
     * have default values and may thus be left unspecified in the configuration. The properties
     * with value OBJECT_INVALID_VALUE are required, and must be specified in the configuration.
     * Note that before setting properties, the parent method must be called, so be sure to place
     * a call to parent::preCreate()!
     * @returns void
     * @protected
     */
    function preCreate() {
    	// Nothing to do
    }

    /**
     * Initialize this object from the specified properties. If the properties contain a property
     * that is unknown to this object (that is: it isn't defined in preCreate()), an error occurs.
     * Also, if after creation this object contains properties with a value of OBJECT_INVALID_VALUE
     * an error occurs as well.
     * @param $properties the array with properties for this object; it is optional
     * @returns void
     * @private
     */
    function create($properties = array()) {
        assert('Debug::checkState("Object", DEBUG_STATE_CREATE)');
        assert('$class = get_class($this)');
        assert('Debug::log("Object: creating \'$this->objectId\', class \'$class\'")');
        $this->preCreate();
        foreach ($properties as $key => $value) {
            if (isset($this->properties[$key])) {
                $this->properties[$key] = $value;
            } else {
                include_once(DAWN_ERRORS . 'ObjectUnknownError.php');
                $error =& new ObjectUnknownError(
                    $this->objectId,
                    get_class($this),
                    array_keys($this->properties),
                    $key
                );
                $error->halt();
            }
        }
        $errors = array();
        foreach ($this->properties as $key => $value) {
            if ($value !== true && $value === OBJECT_INVALID_VALUE) {
                array_push($errors, $key);
            }
        }
        if (count($errors)) {
            include_once(DAWN_ERRORS . 'ObjectInvalidError.php');
            $error =& new ObjectInvalidError($this->objectId, get_class($this), $errors);
            $error->halt();
        }
        $this->postCreate();
    }

    /**
     * Do some postprocessing in the creation stage of this object. Typically, this method is used
     * to add and delete properties to and from the object, or to execute computations that are
     * based on these properties to store the results in one or more properties. Note that at the
     * end of the method, a call must be made to the parent method, so be sure to include a
     * parent::postCreate()!
     * @protected
     */
    function postCreate() {
    	// Nothing to do
    }

    // MANIPULATORS

    /***
     * Set all properties of this object at once.
     * @param $properties an array of properties
     * @returns void
     ***/
    function setProperties($properties) {
        assert('Debug::checkState("Object", DEBUG_STATE_CREATE)');
        assert('$class = get_class($this)');
        assert('Debug::log("Object: restoring \'$this->objectId\', class \'$class\'")');
        $this->properties = $properties;
    }

    /***
     * Set a cacheable (name, value)-pair. Call this method from preCreate() to set default or
     * required properties. Note that if a property with the given name already exists, it will be
     * overwritten. This method always returns true, so that it can be used from within assertions.
     * @param $name the name of the argument
     * @param $value the value of the argument
     * @returns bool
     ***/
    function setProperty($name, $value) {
        assert('Debug::checkState("Object", DEBUG_STATE_CREATE)');
        $this->properties[$name] = $value;
        return true;
    }

    /**
     * Create a list from a string, where the individual elements are separated by some special
     * character. If the string is empty, an empty array is returned.
     * @param $value the string to parse into a list
     * @param $separator the separator between the individual elements
     * @returns array
     */
    function parseList($value, $separator = ',') {
        if (is_array($value)) {
            return $value;
        }
        /*
        if (trim($value) == '') {
            return array();
        }
        */
        return array_map('trim', explode($separator, $value));
    }

    /**
     * Delete a cacheable property; this only makes sense if the object isn't cached yet, thus
     * when called from postCreate(). This allows the deletion of properties that have become
     * useless and needn't be stored in the cache.
     * @param $name the name of the property to delete
     * @returns void
     */
    function deleteProperty($name) {
        assert('Debug::checkState("Object", DEBUG_STATE_CREATE)');
        include_once(ECLIPSE_ROOT . 'ArrayIterator.php');
        $result = array();
        for ($it =& new ArrayIterator($this->properties); $it->isValid(); $it->next()) {
            if ($it->getKey() != $name) {
                $result[$it->getKey()] = $it->getCurrent();
            }
        }
        $this->properties = $result;
    }

    // ACCESSORS

    /**
     * Get this object's unique ID.
     * @returns string
     */
    function getObjectId() {
        return $this->objectId;
    }

    /**
     * Get all persistent (cacheable) data from this object.
     * @returns array
     */
    function getProperties() {
        return $this->properties;
    }

    /**
     * Check if the specified property exists.
     * @returns bool
     */
    function hasProperty($name) {
        return isset($this->data[$name]);
    }

    /**
     * Get the value from a property. Note that there is no check to see if the argument actually
     * exists (for performance reasons). So if you arent' sure, call hasProperty() first.
     * @param $name the name of the property who's value should be returned
     * @returns string
     */
    function &getProperty($name) {
        return $this->data[$name];
    }
}
?>
