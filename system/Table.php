<?php
require_once(DAWN_SYSTEM . 'Object.php');

/***
 * Class Table stores information on a table and all fields in it
 ***/
class Table extends Object
{
    var $name;
    var $databaseManager;
    var $fields;

    function Table($name, &$databaseManager)
    {
        $this->Object($name . '_table');
        $this->name            =  $name;
        $this->databaseManager =& $databaseManager;
        $this->fields          =  array();
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('primary_key', '');
        $this->setProperty('order'      , '');
        $this->setProperty('lookup'     , '');
        $this->setProperty('access'     , '');
        $this->setProperty('fields'     , OBJECT_INVALID_VALUE);
        $this->setProperty('insert'     , '');
        $this->setProperty('update'     , '');
        $this->setProperty('delete'     , '');
    }

    function postCreate()
    {
        parent::postCreate();
        $this->createFields($this->getProperty('fields'));
        $this->createFieldList('primary_key');
        $this->createOrder();
        $this->createLookup();
        $this->createCommand('insert');
        $this->createCommand('update');
        $this->createCommand('delete');
    }

    function createFields(&$settings)
    {
        if (!is_array($settings))
        {
            include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
            $exception =& new ConfigException('database.' . $this->name);
            $exception->halt();
        }
        include_once(DAWN_SYSTEM . 'FieldFactory.php');
        $fields  =  array();
        $factory =& FieldFactory::getInstance();
        $it      =& new ArrayIterator($settings);
        for ( ; $it->isValid(); $it->next())
        {
            $config =& $it->getCurrent();
            if (!is_array($config))
            {
                include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
                $exception =& new ConfigException(
                    'database.' . $this->name . '.' . $it->getKey()
                );
                $exception->halt();
            }
            $type   = is_array($config) && isset($config['type'])
                ? $config['type']
                : 'string';
            $field  =& $factory->createField($it->getKey(), $this, $type);
            $this->fields[$it->getKey()] =& $field;
            $field->create($config);
            $fields[$it->getKey()] = array(
                'class' => $factory->getClass($type),
                'path'  => $factory->getFullClassPath($type)
            );
        }
        $this->setProperty('fields', $fields);
    }

    function createFieldList($section)
    {
        $list = $this->parseList($this->getProperty($section));
        for ($it =& new ArrayIterator($list); $it->isValid(); $it->next())
        {
            if (!$this->hasField($it->getCurrent()))
            {
                include_once(DAWN_EXCEPTIONS . 'InvalidFieldException.php');
                $exception =& new InvalidFieldException(
                    $section,
                    $this->getName(),
                    $it->getCurrent()
                );
                $exception->halt();
            }
        }
        if (count($list) == 0)
        {
            $list = $this->getFields();
        }
        $this->setProperty($section, $list);
    }

    function createLookup()
    {
        $lookup = $this->getProperty('lookup');
        if (!is_array($lookup))
        {
            $lookup = array();
        }
        $fields = isset($lookup['fields'])
            ? $this->parseList($lookup['fields'])
            : $this->getProperty('primary_key');
        $order = isset($lookup['order'])
            ? $this->parseList($lookup['order'])
            : array($fields[0]);
        $template = isset($lookup['template'])
            ? $lookup['template']
            : '';
        $this->setProperty(
            'lookup',
            array(
                'fields'   => $fields,
                'order'    => $order,
                'template' => $template
            )
        );
    }

    function createOrder()
    {
        $order =  array();
        $it    =& new ArrayIterator(
            $this->parseList($this->getProperty('order'))
        );
        for ( ; $it->isValid(); $it->next())
        {
            $pair = explode(' ', $it->getCurrent());
            if (count($pair) == 1)
            {
                $pair[1] = 'asc';
            }
            $direction = strtoupper($pair[1]);
            if ($direction != 'ASC' && $direction != 'DESC')
            {
                $direction = 'ASC';
            }
            if (!$this->hasField($pair[0]))
            {
                include_once(DAWN_EXCEPTIONS . 'InvalidFieldException.php');
                $exception =& new InvalidFieldException(
                    'order',
                    $this->getName(),
                    $pair[0]
                );
                $exception->halt();
            }
            $field =& $this->getField($pair[0]);
            if (!$field->isOrdered())
            {
                include_once(DAWN_EXCEPTIONS . 'InvalidOrderException.php');
                $exception =& new InvalidOrderException(
                    $this->getName(),
                    $field->getName()
                );
                $exception->halt();
            }
            array_push($order, array('field' => $pair[0], 'order' => $direction));
        }
        $this->setProperty('order', $order);
    }

    function createCommand($name)
    {
        include_once(DAWN_SYSTEM . 'CommandFactory.php');
        if (($config = $this->getProperty($name)) == '')
        {
            $config = array();
        }
        if (!isset($config['type']))
        {
            $config['type'] = $name;
        }
        $factory =& CommandFactory::getInstance();
        $command =& $factory->createCommand($name, $this, $config['type']);
        $this->setProperty(
            $name,
            array(
                'class' => $factory->getClass($config['type']),
                'path'  => $factory->getFullClassPath($config['type']),
                'data'  => $command->save()
            )
        );
    }

    function load($data)
    {
        parent::load($data['table']);
        $fields =  $this->getProperty('fields');
        $it     =& new ArrayIterator($fields);
        for ( ; $it->isValid(); $it->next())
        {
            $name = $it->getKey();
            include_once($fields[$name]['path']);
            $class =  $fields[$name]['class'];
            $field =& new $class($name, $this);
            $field->load($data['fields'][$name]);
            $this->fields[$name] =& $field;
        }
    }

    function save()
    {
        $fields =  array();
        $it     =& new ArrayIterator($this->fields);
        for ( ; $it->isValid(); $it->next())
        {
            $field                 =& $it->getCurrent();
            $fields[$it->getKey()] =  $field->save();
        }
        return array(
            'table'  => parent::save(),
            'fields' => $fields
        );
    }

    function &getDatabaseManager()
    {
        return $this->databaseManager;
    }

    function &getDatabase()
    {
        return $this->databaseManager->getDatabase();
    }

    function getName()
    {
        return $this->name;
    }

    function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    function getFields()
    {
        return array_keys($this->fields);
    }

    function &getField($name)
    {
        return $this->fields[$name];
    }

    function getPrimaryKey()
    {
        return $this->getProperty('primary_key');
    
    }
    
    function getReferences()
    {
        $result =  array();
        $it     =& new ArrayIterator($this->fields);
        for ( ; $it->isValid(); $it->next())
        {
            $field =& $it->getCurrent();
            if ($field->isReference())
            {
                array_push($result, $field->getName());
            }
        }
        return $result;
    }

    /***
     * @private
     ***/
    function &getCommand($name)
    {
        $config  =& $this->getProperty($name);
        include_once($config['path']);
        $class   = $config['class'];
        $command =& new $class($name, $this);
        $command->load($config['data']);
        return $command;
    }

    function &getInsertCommand()
    {
        return $this->getCommand('insert');
    }

    function &getUpdateCommand()
    {
        return $this->getCommand('update');
    }

    function &getDeleteCommand()
    {
        return $this->getCommand('delete');
    }
    
    function getLookupFields()
    {
        $lookup =& $this->getProperty('lookup');
        return $lookup['fields'];
    }

    function getLookupOrder()
    {
        $lookup =& $this->getProperty('lookup');
        return $lookup['order'];
    }

    function getLookupTemplate()
    {
        $lookup =& $this->getProperty('lookup');
        return $lookup['template'];
    }

    /***
     * @static
     ***/
    function encodeValues($values)
    {
        return join('|', array_map('rawurlencode', $values));
    }

    /***
     * @static
     ***/
    function decodeValues($values)
    {
        return array_map('rawurldecode', explode('|', $values));
    }
}
?>
