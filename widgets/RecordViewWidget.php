<?php
require_once(DAWN_SYSTEM . 'Widget.php');
require_once(DAWN_SYSTEM . 'ComponentManager.php');

class RecordViewWidget extends Widget
{
    var $row;
    var $isset;
    var $form;
    var $componentManager;

    function RecordViewWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->componentManager =& new ComponentManager($this);
        $this->row              =  array();
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('layout' , '');
        $this->setProperty('table' , OBJECT_INVALID_VALUE);
        $this->setProperty('fields', OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        $form        =& $this->getOwner();
        $this->setProperty('widgets', array());
        $this->setProperty(
            'fields',
            $this->createFields($this->getProperty('fields'))
        );
        $this->setProperty('size', count($this->getProperty('fields')));
        $this->setProperty(
            'layout',
            array(
                'type'   => 'grid',
                'width'  => 2,
                'height' => $this->getProperty('size') + 1
            )
        );
        $this->setProperty(
            'widgets',
            $this->componentManager->createComponents(
                $this->getProperty('widgets')
            )
        );
        parent::postCreate();
    }

    function createFields(&$config)
    {
        $fields = array();
        $it     =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            $field =& $it->getCurrent();
            if (!is_array($field))
            {
                // TODO: throw the right exception
                include_once(DAWN_EXCEPTIONS . 'ConfigException.php');
                $exception =& new ConfigException(
                    'fields.' . $it->getKey(),
                    'widget',
                    $this->getObjectId()
                );
                $exception->halt();
            }
            array_push($fields, $it->getKey());
            $this->createField($it->getKey(), $field);
        }
        return $fields;
    }

    function createField($name, &$config)
    {
        $form  =& $this->getOwner();
        $table =& $form->getTable($this->getProperty('table'));
        if (!$table->hasField($name))
        {
            exit("Field $name doesn't exist");
        }
        $field =& $table->getField($name);
        if (!isset($config['type']))
        {
            if ($field->isReference())
            {
                $config['type']      = 'static_table_lookup';
                list($tName, $fName) = $field->getReference();
                $config['table']     = $tName;
                $config['field']     = $fName;
                $table =& $form->getTable($tName);
                $config['lookup']    = $table->getLookupFields();
                $config['order']     = $table->getLookupOrder();
                $config['template']  = $table->getLookupTemplate();
            }
            else
            {
                $config['type'] = 'static';
            }
        }
        if (!isset($config['css']))
        {
            $config['css'] = 'field';
        }
        $widgets =& $this->getProperty('widgets');
        $widgets[$name . '_label'] = array(
            'type'    => 'label',
            'css'     => 'label',
            'caption' => $field->getCaption() . ':'
        );
        $widgets[$name] = $config;
    }

    function build()
    {
        parent::build();
        if (!$this->isset)
        {
            return;
        }
        $this->componentManager->buildComponents();
        $this->updateFields();
    }

    function updateFields()
    {
        $table  =  $this->getProperty('table');
        $size   =  $this->getProperty('size');
        $fields =& $this->getProperty('fields');
        for ($i = 0; $i < $size; $i++)
        {
            $widget =& $this->componentManager->getComponent($fields[$i]);
            $widget->setValue($this->row[$table . '_' . $fields[$i]]);
        }
    }

    function load($data)
    {
        parent::load($data['widget']);
        $this->componentManager->loadComponents(
            $this->getProperty('widgets'),
            $data['fields']
        );
    }

    function save()
    {
        return array(
            'widget' => parent::save(),
            'fields' => $this->componentManager->saveComponents()
        );
    }

    function showComponent($indent, $name)
    {
        parent::showComponent($indent, $name);
        if (!$this->isset)
        {
            return;
        }
        list($column, $row) = explode(',', $name);
        if ($row == $this->getProperty('size'))
        {
            $this->showButtons($indent, $column);
            return;
        }
        if ($column == 0)
        {
            $this->showWidget($indent, $row, '_label');
            return;
        }
        $this->showWidget($indent, $row);
    }

    function showWidget($indent, $row, $postfix = '')
    {
        $fields =& $this->getProperty('fields');
        $widget =& $this->componentManager->getComponent($fields[$row] . $postfix);
        $widget->show($indent);
    }

    function showButtons($indent, $column)
    {
        if ($column == 0)
        {
            return;
        }
        $form =& $this->getForm();
        $form->showButtons($indent, $this->row);
    }

    function setRow($row)
    {
        $this->row   =& $row;
        $this->isset =  true;
    }

    function &getTable($name)
    {
        $form =& $this->getOwner();
        return $form->getTable($name);
    }

    function &getComponentFactory()
    {
        include_once(DAWN_SYSTEM . 'WidgetFactory.php');
        return WidgetFactory::getInstance();
    }
}
?>
