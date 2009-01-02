<?php
/***
 * Class LayoutManager manages the layout for a Window.
 * <p>
 *   This class resembles class ComponentManager, although much simpler. Instead
 *   of managing a set of components for a Window, it manages a single Layout.
 * </p>
 * <p>
 *   As a component in a Window, a layout can be introduced to the system in one
 *   of two ways:
 * </p>
 * <ol>
 *   <li>
 *     By <b>creation</b>: the Layout is constructed and configured from a
 *     configuration.
 *   </li>
 *   <li>
 *     By <b>loading</b>: the Layout has already been constructed before, and is
 *     now loaded from cache.
 *   </li>
 * </ol>
 * <p>
 *   Once a Layout has been properly created, it can be saved in the cache. The
 *   containing window is responsible for storing the cacheable data, but this
 *   manager supplies the means to get that data.
 * </p>
 * <p>
 *   Because a Layout is a Component, it must be built before it can be shown.
 * </p>
 ***/
class LayoutManager
{
    // DATA MEMBERS

    /***
     * The window this manager is for
     * @type Window
     ***/
    var $window;

    /***
     * The layout managed by this object
     * @type Layout
     ***/
    var $layout;

    // CREATORS

    /***
     * Construct a new LayoutManager for the specified Window.
     * @param $window the Window to create this manager for
     ***/
    function LayoutManager(&$window)
    {
        $this->window =& $window;
    }

    // MANIPULATORS

    /***
     * Create a Layout, given the configuration for it. If the configuration is
     * invalid, an exception is thrown. If the type of the layout isn't
     * specified in the configuration, the default layout as specified by the
     * owning window is returned. Thus, every Window can specify its own
     * specific default layout. This method returns an array with settings
     * necessary to recreate the layout from the cache.
     * @param $config the Layout's configuration
     * @returns array
     ***/
    function createLayout(&$config)
    {
        assert('$id = $this->window->getObjectId()');
        assert('Debug::log("LayoutManager: creating layout for \'$id\'")');
        if (!is_array($config))
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
            $exception =& new ConfigException(
                'layout',
                'window',
                $this->window->getObjectId()
            );
            $exception->halt();
        }
        if (!isset($config['type']))
        {
            $config['type'] = $this->window->getDefaultLayout();
        }
        include_once(DAWN_SYSTEM . 'LayoutFactory.php');
        $factory      =& LayoutFactory::getInstance();
        $class        =  $factory->getClass($config['type']);
        $path         =  $factory->getFullClassPath($config['type']);
        $this->layout =& new $class($this->window);
        $this->layout->create($config);
        return array(
            'class' => $class,
            'path'  => $path
        );
    }

    /***
     * Load a layout from the cache.
     * @param $layout the settings for this layout
     * @param $data the cached data for the layout
     * @returns void
     ***/
    function loadLayout(&$layout, &$data)
    {
        assert('$id = $this->window->getObjectId()');
        assert('Debug::log("LayoutManager: loading layout for \'$id\'")');
        include_once($layout['path']);
        $class = $layout['class'];
        $this->layout =& new $class($this->window);
        $this->layout->load($data);
    }

    /***
     * Show the layout
     * @param $indent the indentiation level to show the layout at
     * @returns void
     ***/
    function showLayout($indent)
    {
        assert('$id = $this->window->getObjectId()');
        assert('Debug::log("LayoutManager: showing layout for \'$id\'")');
        $this->layout->show($indent);
    }

    // ACCESSORS

    /***
     * Return the data for the layout for storage in the cache
     * @returns array
     ***/
    function saveLayout()
    {
        return $this->layout->save();
    }

    /***
     * Get a list of all components shown on the layout. This method is only
     * called in debug mode.
     * @returns array
     ***/
    function getComponents()
    {
        return $this->layout->getComponents();
    }
}
?>
