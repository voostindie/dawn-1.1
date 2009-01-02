<?php
require_once(DAWN_SYSTEM . 'Object.php');
require_once(DAWN_SYSTEM . 'Page.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

class Menu extends Object
{
    // DATA MEMBERS

    var $application;
    var $access;
    var $current;

    // CREATORS

    function Menu(&$application)
    {
        $this->Object('menu_items');
        $this->application =& $application;
        $this->access      =  array();
        $this->current     = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('items', OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        include_once(DAWN_SYSTEM . 'Config.php');
        include_once(DAWN_SYSTEM . 'Translator.php');
        parent::postCreate();
        $items  =  array();
        $it     =& new ArrayIterator($this->getProperty('items'));
        for ( ; $it->isValid(); $it->next())
        {
            $name   =  $it->getCurrent();
            $config =& Config::getInstance();
            $entry  =& $config->getEntry('pages.' . $name);
            $items[$name] =& new MenuItem(
                $name,
                Translator::resolveText($name, 'PAGE_CAPTION', 'PAGE'),
                Translator::resolveText($name, 'PAGE_TITLE', 'PAGE'),
                isset($entry['access']) ? $entry['access'] : ''
            );
        }
        $this->setProperty('items' , $items);
    }

    // MANIPULATORS

    function initialize()
    {
        $user =& $this->application->getUser();
        $it   =& new ArrayIterator($this->getProperty('items'));
        for ( ; $it->isValid(); $it->next())
        {
            $item =& $it->getCurrent();
            if ($user->hasAccess($item->getAccess()))
            {
                array_push($this->access, $item->getName());
            }
        }
    }
    
    function setCurrent($current)
    {
        $this->current = $current;   
    }
    
    // ACCESSORS
    
    /***
     * Get the name of the first menu item accessible by the user, or false if
     * no such item exists
     * @returns string
     ***/
    function getFirstValid()
    {
        if (count($this->access))
        {
            return $this->access[0];   
        }
        return false;
    }
    
    /***
     * Get the menu item with the specified name, or false if the item doesn't
     * exist
     * @param $name the name of the menu item to return
     * @returns MenuItem
     ***/
    function &getItem($name)
    {
        $items =& $this->getProperty('items');
        if (isset($items[$name]))
        {
            return $items[$name];
        }
        return false;
    }

    /***
     * Return the list of menu items, accessible by the current user
     * @returns array
     ***/
    function &getItems()
    {
        return $this->access;
    }

    function getCurrent()
    {
        return $this->current;
    }
}

class MenuItem
{
    // DATA MEMBERS

    var $name;
    var $caption;
    var $title;
    var $access;

    // CREATORS

    function MenuItem($name, $caption, $title, $access)
    {
        $this->name    = $name;
        $this->caption = $caption;
        $this->title   = $title;
        $this->access  = $access;
    }

    // ACCESSORS

    function getName()
    {
        return $this->name;
    }

    function getCaption()
    {
        return $this->caption;
    }

    function getTitle()
    {
        return $this->title;
    }

    function getAccess()
    {
        return $this->access;
    }
}
?>
