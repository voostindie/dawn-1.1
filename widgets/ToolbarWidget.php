<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class ToolbarWidget extends Widget
{
    var $buttons;

    function ToolbarWidget($id, &$form)
    {
        $this->Widget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('buttons'      , OBJECT_INVALID_VALUE);
        $this->setProperty('css'          , 'toolbar');
        $this->setProperty('command_field', '');
    }

    function postCreate()
    {
        $config = $this->getProperty('buttons');
        if (!is_array($config))
        {
            // TODO: throw the right exception
            include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
            $exception =& new ConfigException(
                $this->source,
                'widget',
                $this->window->getObjectId()
            );
            $exception->halt();
        }
        $this->buttons = array();
        $buttons       = array();
        $it            =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            array_push(
                $buttons, $this->createButton($it->getKey(), $it->getCurrent())
            );
        }
        $this->setProperty('buttons', $buttons);
        $this->setProperty(
            'layout',
            array('components' => array_keys($this->buttons))
        );
        parent::postCreate();
    }

    function createButton($name, &$config)
    {
        if (!is_array($config))
        {
            // TODO: throw the right exception
            include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
            $exception =& new ConfigException(
                $this->source,
                'widget',
                $this->window->getObjectId()
            );
            $exception->halt();
        }
        $config['command_field'] = $this->getProperty('command_field');
        $config['layout']     = array(
            'type' => 'empty', 'components' => 'widget'
        );
        include_once(DAWN_SYSTEM . 'WidgetFactory.php');
        $factory =& WidgetFactory::getInstance();
        $type    =  isset($config['type']) ? $config['type'] : 'button';
        $class   =  $factory->getClass($type);
        $path    =  $factory->getFullClassPath($type);
        $button  =& new $class($name, $this->getOwner());
        $button->create($config);
        $this->buttons[$name] =& $button;
        return array(
            'name'  => $name,
            'class' => $class,
            'path'  => $path
        );
    }

    function load($data)
    {
        parent::load($data['toolbar']);
        $this->buttons =  array();
        $it            =& new ArrayIterator($this->getProperty('buttons'));
        for ( ; $it->isValid(); $it->next())
        {
            $config =& $it->getCurrent();
            include_once($config['path']);
            $name   =  $config['name'];
            $class  =  $config['class'];
            $button =& new $class($name, $this->getOwner());
            $button->load($data['buttons'][$name]);
            $this->buttons[$name] =& $button;
        }
    }

    function save()
    {
        $buttons = array();
        $it =& new ArrayIterator($this->buttons);
        for ( ; $it->isValid(); $it->next())
        {
            $buttons[$it->getKey()] =
                $this->buttons[$it->getKey()]->save();
        }
        return array(
            'toolbar' => parent::save(),
            'buttons' => $buttons
        );
    }

    function showComponent($indent, $name)
    {
        $user =& $this->getUser();
        parent::showComponent($indent, $name);
        if (isset($this->buttons[$name]) &&
            $user->hasAccess($this->buttons[$name]->getAccess()))
        {
            $this->buttons[$name]->show($indent);
        }
    }

    function getDefaultButtonConfig($name, &$config)
    {
        return $config;
    }

    function getDefaultLayout()
    {
        return 'flow';
    }

    function &getButton($name)
    {
        if (isset($this->buttons[$name]))
        {
            return $this->buttons[$name];
        }
        return false;
    }
}
?>
