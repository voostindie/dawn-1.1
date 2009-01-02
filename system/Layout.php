<?php
require_once(DAWN_SYSTEM . 'Object.php');
require_once(DAWN_SYSTEM . 'Layout.php');

class Layout extends Object
{
    var $owner;
    
    function Layout(&$owner)
    {
        $this->Object('layout');
        $this->owner =& $owner;
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('type', '');
        $this->setProperty('components', array());
    }
    
    function postCreate()
    {
        $this->deleteProperty('type');
        parent::postCreate();
        $components =& $this->getProperty('components');
        if (!is_array($components))
        {
            $components = $this->parseList($components);
        }
    }
    
    /***
     * The Layout requires the argument 'components' to list all different
     * components shown on the layout. It is, however, a bit strange to let the
     * configuration define this list as well as name the components in the
     * various layout sections. Therefore it is possible to register components
     * from within 'postCreate' at a later time.
     * @param $name the name of the component to register
     * @returns void
     ***/
    function registerComponent($name)
    {
        $components =& $this->getProperty('components');
        if (!in_array($name, $components))
        {
            array_push($components, $name);
        }
    }
    
    /***
     * Register multiple components at once. This method is just like
     * registerComponent, except that it expects an array of names instead
     * of just one
     * @param $names the names of the components to register
     ***/
    function registerComponents($names)
    {
        foreach ($names as $name)
        {
            $this->registerComponent($name);
        }
    }
    
    function show($indent)
    {
        assert('Debug::checkState("Layout", DEBUG_STATE_SHOW)');
        assert('$id = $this->getObjectId()');
        assert('$class = get_class($this)');
        assert('Debug::log("Layout: showing \'$id\', class \'$class\' at level \'$indent\'")');
    }

    function showComponent($indent, $name)
    {
        $this->owner->showComponent($indent, $name);
    }
    
    function showComponents($indent, $names)
    {
        foreach ($names as $name)
        {
            $this->showComponent($indent, $name);
        }
    }
    
    function &getOwner()
    {
        return $this->owner;   
    }

    /***
     * Get the full list of components used by this layout.
     * @returns array
     ***/
    function getComponents()
    {
        return $this->getProperty('components');   
    }
}
?>
