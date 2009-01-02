<?php
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

/***
 * Class ComponentManager manages the components contained in a Window.
 * <p>
 *   There are two ways components can be introduced into the system:
 * </p>
 * <ol>
 *   <li>
 *     By <b>creation</b>: if the window containing the components is created
 *     for the first time, the components in it are created from the
 *     configuration for that window.
 *   </li>
 *   <li>
 *     By <b>loading</b>: once a window containing components has been created,
 *     it can load the components from the cache.
 *   </li>
 * </ol>
 ***/
class ComponentManager
{
    // DATA MEMBERS

    /***
     * The window containing the components
     * @type Window
     ***/
    var $window;

    /***
     * The components being managed
     * @type array
     ***/
    var $components;

    /***
     * The static (create) dependencies between the components; only computed
     * in debug mode
     * @type array
     ***/
    var $staticDeps;

    /***
     * The names of the components that have already been created
     * @type array;
     ***/
    var $created;

    /***
     * The dynamic (build) dependencies between the components; only computed
     * in debug mode
     * @type array
     ***/
    var $dynamicDeps;

    /***
     * The names of the components that have already been built
     * @type array
     ***/
    var $built;

    /***
     * Whether or not the components are currently being loaded from cache
     * @type bool
     ****/
    var $loading;

    /***
     * The configuration for the components, only used when they are created,
     * not when they are loaded
     ***/
    var $config;

    /***
     * The settings for the components necessary to load them without using a
     * factory. Only used when the components are created.
     ***/
    var $settings;

    // CREATORS

    /***
     * Construct a new ComponentManager for a window
     * @param $window the Window to create this manager for
     ***/
    function ComponentManager(&$window)
    {
        $this->window      =& $window;
        $this->components  =  array();
        $this->loading     =  false;
        $this->built       =  array();
        $this->created     =  array();
        $this->staticDeps  =  array();
        $this->dynamicDeps =  array();
        unset($this->config);
        unset($this->settings);
    }

    // MANIPULATORS

    /***
     * Given a configuration section, create all components in it. If the
     * configuration is invalid, an exception is thrown. This method returns
     * an array with settings for each component, necessary to recreate
     * the components when loaded from the cache. Note that this method
     * should only be called when the components aren't already in the cache.
     * @param $config the array with the configuration of the components.
     * @returns array
     ***/
    function &createComponents(&$config)
    {
        assert('!($this->staticDeps = array())');
        assert('$id = $this->window->getObjectId()');
        assert('Debug::log("ComponentManager: creating components for \'$id\'")');
        if (!is_array($config))
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
            $exception =& new ConfigException(
                $this->source,
                'window',
                $this->window->getObjectId()
            );
            $exception->halt();
        }
        $this->created  =  array();
        $this->config   =& $config;
        $this->settings =  array();
        $it             =& new ArrayIterator(array_keys($config));
        for ( ; $it->isValid(); $it->next())
        {
            $this->createComponent($it->getCurrent());
        }
        return $this->settings;
    }

    /***
    * Create a single component, given its name and its configuration. If the
    * configuration is invalid, an exception is thrown. This method returns
    * an array with settings required to recreate the components if it is loaded
    * from the cache: its name, its class, and the file the class is in.
    * @param $name the component's name
    * @param $config the configuration for the component
    * @returns array
    * @private
    ***/
    function &createComponent($name)
    {
        if ($this->loading)
        {
            include_once(DAWN_EXCEPTIONS . 'ComponentLoaderException.php');
            $exception =& new ComponentLoaderException(
                $this->window->getObjectId(),
                $name
            );
            $exception->halt();
        }
        if (in_array($name, $this->created))
        {
            return;
        }
        array_push($this->created, $name);
        $config =& $this->config[$name];
        if (!is_array($config))
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
            $exception =& new ConfigException(
                $this->source . '.' . $name,
                'window',
                $this->window->getObjectId()
            );
            $exception->halt();
        }
        $type      =  isset($config['type']) ? $config['type'] : $name;
        $factory   =& $this->window->getComponentFactory();
        $class     =  $factory->getClass($type);
        $path      =  $factory->getFullClassPath($type);
        $component =& new $class($name, $this->window);
        $component->create($config);
        $this->components[$name] =& $component;
        $this->settings[$name]   =  array(
            'name'  => $name,
            'class' => $class,
            'path'  => $path
        );
    }

    /***
     * Load a set of components from the cache. The order the components are
     * loaded may be different from the order they were created in!
     * @param $components the list of components and their required settings
     * @param $data the cached data for the components
     * @returns void
     ***/
    function loadComponents(&$components, &$data)
    {
        assert('$id = $this->window->getObjectId()');
        assert('Debug::log("ComponentManager: loading components for \'$id\'")');
        $this->loading =  true;
        $it            =& new ArrayIterator($components);
        for ( ; $it->isValid(); $it->next())
        {
            $settings =& $it->getCurrent();
            include_once($settings['path']);
            $class = $settings['class'];
            $this->components[$settings['name']] =& new $class(
                $settings['name'], $this->window
            );
            $this->components[$settings['name']]->load(
                $data[$settings['name']]
            );
        }
        $this->loading = false;
    }

    /***
     * Add a component to the list of managed components. If a component with
     * the specified name already exists, an exception is thrown. Note: if this
     * method is called, it must be called after createComponents or
     * loadComponents, but before buildComponents. This implies that other
     * components may request dynamic properties of the newly added component,
     * but not static ones.
     * @param $component the new component
     * @returns Component
     ***/
    function &addComponent(&$component)
    {
        if (isset($this->components[$component->getObjectId()]))
        {
            include_once(DAWN_EXCEPTIONS . 'ComponentClashException.php');
            $exception =& new ComponentClashException(
                $window->getObjectId(),
                $component->getObjectId()
            );
        }
        $this->components[$component->getObjectId()] =& $component;
        $component->setOwner($this->window);
        return $component;
    }

    /***
     * Build all components. This method traverses the list of components and
     * builds every component in it. Note that every component is only built
     * once.
     * @return void
     ***/
    function buildComponents()
    {
        assert('!($this->dynamicDeps = array())');
        assert('$id = $this->window->getObjectId()');
        assert('Debug::log("ComponentManager: building components for \'$id\'")');
        $it          =& new ArrayIterator($this->components);
        for ( ; $it->isValid(); $it->next())
        {
            $this->buildComponent($it->getKey());
        }
    }

    /***
     * Build a component. If the component has already been built, nothing
     * happens.
     * @param $name the name of the component to build
     * @returns void
     * @private
     ***/
    function buildComponent($name)
    {
        if (in_array($name, $this->built))
        {
            return;
        }
        array_push($this->built, $name);
        $this->components[$name]->build();
    }

    /***
     * Show a component. Note that all components should have been build first.
     * If the user isn't allowed to see the component, it will not be shown
     * @param $name the name of the component to show
     * @param $indent the indentation level to show the component at
     ***/
    function showComponent($indent, $name)
    {
        $user =& $this->window->getUser();
        if ($user->hasAccess($this->components[$name]->getAccess()))
        {
            $this->components[$name]->show($indent);
        }
    }

    /***
     * Add a dependency between two components: $target depends on $caller. If
     * a dependency the other way around already exists, or if the target
     * component couldn't be found, an exception is thrown. If the dependecy
     * could be added, the method returns true
     * @param $dependencies the list to add the dependency to
     * @param $caller the component that creates the dependency
     * @param $target the dependent component
     * @returns bool
     ***/
    function addDependency(&$dependencies, $caller, $target)
    {
        assert('Debug::log("ComponentManager: adding dependency from ' .
               'component \'$caller\' on \'$target\'")');
        if (!isset($this->components[$target]))
        {
            include_once(DAWN_EXCEPTIONS . 'InvalidComponentException.php');
            $exception =& new InvalidComponentException(
                $this->window->getObjectId(),
                $target
            );
            $exception->halt();
        }
        if ($this->hasDependency($dependencies, $target, $caller))
        {
            include_once(DAWN_EXCEPTIONS . 'CyclicDependencyException.php');
            $exception =& new CyclicDependencyException(
                $this->window->getObjectId(),
                $caller,
                $target
            );
            $exception->halt();
        }
        if (!isset($dependencies[$caller]))
        {
            $dependencies[$caller] = array();
        }
        if (!in_array($target, $dependencies[$caller]))
        {
            array_push($dependencies[$caller], $target);
        }
        return true;
    }

    // ACCESSORS

    /***
     * Get the data of all components to store in the cache
     * @returns array
     ***/
    function saveComponents()
    {
        $result = array();
        $it =& new ArrayIterator($this->components);
        for (; $it->isValid(); $it->next())
        {
            $component =& $it->getCurrent();
            $result[$it->getKey()] = $component->save();
        }
        return $result;
    }

    /***
     * Given a list of components, check that they all exist in the manager. If
     * a component is found that doesn't exists, an exception is thrown. This
     * method is only called in debug mode (from an assertion) to check the
     * components in a layout against those available, and always returns true.
     * @returns bool
     ***/
    function checkComponents($components)
    {
        Debug::log("ComponentManager: checking the components on the layout " .
            "of window '" . $this->window->getObjectId() . "'");
        foreach ($components as $component)
        {
            if (!isset($this->components[$component]))
            {
                include_once(DAWN_EXCEPTIONS . 'UnknownComponentException.php');
                $exception =& new UnknownComponentException(
                    $this->window->getObjectId(),
                    $component
                );
                $exception->halt();
            }
        }
        return true;
    }

    /***
     * Check if there is a dependency between two components: does $target
     * depend on $caller? This methods returns true if such a dependency exists,
     * and false otherwise.
     * @param $dependencies the list of dependencies to check
     * @param $caller the component that made the dependency
     * @param $target the dependent component
     * @returns bool
     * @private
     ***/
    function hasDependency(&$dependencies, $caller, $target)
    {
        if (!isset($dependencies[$caller]))
        {
            return false;
        }
        $it =& new ArrayIterator($dependencies[$caller]);
        for ( ; $it->isValid(); $it->next())
        {
            $component =& $it->getCurrent();
            if ($component == $target)
            {
                return true;
            }
            if ($this->hasDependency($dependencies, $component, $target))
            {
                return true;
            }
        }
        return false;
    }

    /***
     * Check if a component has a specified static property. If the property
     * doesn't exist, an exception is thrown. If the property is valid, this
     * method returns true.
     * @returns bool
     ***/
    function checkStaticProperty($component, $property)
    {
        assert('Debug::log("ComponentManager: checking static property \'$property\' of component \'$component\'")');
        $value =& $this->components[$component]->getStaticProperty($property);
        if ($value === NULL)
        {
            include_once(DAWN_EXCEPTIONS . 'InvalidPropertyException.php');
            $exception =& new InvalidPropertyException(
                'static',
                $this->window->getObjectId(),
                $component,
                $property
            );
            $exception->halt();
        }
        return true;
    }

    /***
     * Check if a component has a specified dynamic property. If the property
     * doesn't exist, an exception is thrown. If the property is valid, this
     * method returns true.
     * @returns bool
     ***/
    function checkDynamicProperty($component, $property)
    {
        assert('Debug::log("ComponentManager: checking dynamic property \'$property\' of component \'$component\'")');
        $value =& $this->components[$component]->getDynamicProperty($property);
        if ($value === NULL)
        {
            include_once(DAWN_EXCEPTIONS . 'InvalidPropertyException.php');
            $exception =& new InvalidPropertyException(
                'dynamic',
                $this->window->getObjectId(),
                $component,
                $property
            );
            $exception->halt();
        }
        return true;
    }

    /***
     * Return the component with the specified name
     * @returns Object
     ***/
    function &getComponent($name)
    {
        return $this->components[$name];
    }

    function &getStaticProperty(&$caller, $target, $property)
    {
        assert('$oid = $caller->getObjectId()');
        assert('Debug::log("ComponentManager: \'$oid\' is requesting the static property \'$property\' from \'$target\'")');
        $this->createComponent($target);
        assert('$this->addDependency($this->staticDeps, $caller->getObjectId(), $target)');
        assert('$this->checkStaticProperty($target, $property)');
        return $this->components[$target]->getStaticProperty($property);
    }

    /***
     * Return a property of a component. If the application is in debug mode, a
     * dependency is created from $target to $caller, and the existence of the
     * property is verified. Both processes can throw exceptions: if a cyclic
     * dependency arises, if the target component doesn't exist, or if the
     * target component doesn't have the specified property.
     * @param $caller the component that requests the property
     * @param $target the name of the component that has the property
     * @param $property the name of the property
     * @returns Object
     ***/
    function &getDynamicProperty(&$caller, $target, $property)
    {
        assert('$oid = $caller->getObjectId()');
        assert('Debug::log("ComponentManager: \'$oid\' is requesting the dynamic property \'$property\' from \'$target\'")');
        assert('$this->addDependency($this->dynamicDeps, $caller->getObjectId(), $target)');
        $this->buildComponent($target);
        assert('$this->checkDynamicProperty($target, $property)');
        return $this->components[$target]->getDynamicProperty($property);
    }
}
?>
