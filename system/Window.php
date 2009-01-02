<?php
require_once(DAWN_SYSTEM . 'Component.php');
require_once(DAWN_SYSTEM . 'ComponentManager.php');
require_once(DAWN_SYSTEM . 'LayoutManager.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

/***
 * Class <code>Window</code> implements a base class for containers storing
 * components.
 * <p>
 *   A <code>Window</code> is a container storing a list of components that can
 *   be shown (printed) in HTML. There are two subclasses of this base class in
 *   <b>Dawn</b>: <code>Page</code> and <code>Form</code>. The first stores
 *   any number of forms on a single web page, while the second stores any 
 *   number of widgets on a single form.
 * </p>
 ***/
class Window extends Component
{
    // DATA MEMBERS
    
    var $source;
    var $componentManager;

    // CREATORS
    
    /***
     * @param $id the ID for this window
     * @param $owner the object that owns this window
     * @param $source the name of the configuration section containing
     * the components, e.g. 'forms', or 'widgets'
     ***/
    function Window($id, &$owner, $source)
    {
        $this->Component($id, $owner);
        $this->source           =  $source;
        $this->componentManager =& new ComponentManager($this);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty($this->source, OBJECT_INVALID_VALUE);
    }
    
    function postCreate()
    {
        parent::postCreate();
        $this->setProperty(
            'components',
            $this->componentManager->createComponents(
                $this->getProperty($this->source)
            )
        );
        $this->deleteProperty($this->source);
        assert('$this->componentManager->checkComponents(' .
            '$this->layoutManager->getComponents())');
    }
    
    // MANIPULATORS
    
    function load($data)
    {
        parent::load($data['window']);
        $this->componentManager->loadComponents(
            $this->getProperty('components'), 
            $data['components']
        );
    }
  
    /***
     * Add a component to the window. The component must have already been
     * created, but not built.
     * @param $component the component to add to the window
     * @returns void
     ***/
    function addComponent(&$component)
    {
        assert('Debug::checkState("Window", DEBUG_STATE_CREATE)');
        $this->componentManager->addComponent($component);
    }

    function build()
    {
        parent::build();
        $this->buildWindow();
        $this->componentManager->buildComponents();
    }

    function buildWindow()
    {
    }

    function showComponent($indent, $name)
    {
        parent::showComponent($indent, $name);
        $this->componentManager->showComponent($indent, $name);
    }
    
    // ACCESSORS
    
    /***
     * Get a reference to the factory used for creating the components on this
     * Window. Subclasses must override this method.
     * @returns Factory
     ***/
    function &getComponentFactory()
    {
        exit("Method <code>getComponentFactory</code> of class Window isn't " .
            "implemented.");
    }

    function save()
    {
        return array(
            'window'     => parent::save(),
            'components' => $this->componentManager->saveComponents()
        );
    }

    function &getComponent($name)
    {
        return $this->componentManager->getComponent($name);
    }

    function &getStaticComponentProperty(&$caller, $target, $property)
    {
        assert('Debug::checkState("Window", DEBUG_STATE_CREATE)');
        return $this->componentManager->getStaticProperty(
            $caller, $target, $property
        );
    }

    /***
     * Get a property of a component on the window. This method can be called
     * by components on the window to request information from another component
     * on that same window.
     * @param $caller the component requesting the property
     * @param $target the name of the component that has the property
     * @param $property the name of the requested property
     * @returns Object
     ***/
    function &getDynamicComponentProperty(&$caller, $target, $property)
    {
        assert('Debug::checkState("Window", DEBUG_STATE_BUILD)');
        return $this->componentManager->getDynamicProperty(
            $caller, $target, $property
        );
    }
}
?>
