<?php
require_once(DAWN_FORMS . 'BasicForm.php');
require_once(DAWN_SYSTEM . 'Record.php');

class FieldViewForm extends BasicForm
{
    var $table;
    var $query;
    var $activeRecord;
    var $url;

    function FieldViewForm($name, &$page)
    {
        $this->BasicForm($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('tracker'     , 'tracker');
        $this->setProperty('field'       , OBJECT_INVALID_VALUE);
        $this->setProperty('buttons'     , '');
        $this->setProperty('widgets'     , '');
        $this->setProperty('width'       , 1);
    }

    function postCreate()
    {
        $this->table =& $this->getStaticFormProperty(
            $this->getProperty('tracker'), 'table'
        );
        $this->setProperty('table', $this->table->getName());
        $this->createMessageWidget();
        $field = $this->getProperty('field');
        if (!is_array($field))
        {
            include_once(DAWN_ERRORS . 'ConfigError.php');
            $error =& new ConfigError(
                'fields',
                'window',
                $this->window->getObjectId()
            );
            $error->halt();
        }
        $field  = $this->createField($field);
        $hidden = array_diff($this->table->getPrimaryKey(), array($field));
        $this->setProperty('field' , $field);
        $this->setProperty('hidden', $hidden);
        $buttons = $this->getProperty('buttons');
        if (!is_array($buttons))
        {
            $buttons = array();
        }
        $this->createToolbar($buttons);
        $this->deleteProperty('buttons');
        $this->query =& new Query($this->table);
        $this->query->create(
            array(
                'fields' => join(
                    ', ',
                    array_unique(array_merge(array($field), $hidden))
                )
            )
        );
        parent::postCreate();
    }

    function createMessageWidget()
    {
        $this->setProperty(
            'widgets',
            array(
                '_keys' => array(
                    'type'     => 'hidden'
                ),
            )
        );
    }

    function createField(&$settings)
    {
        $it     =& new ArrayIterator($settings);
        for ( ; $it->isValid(); $it->next())
        {
            $name   =  $it->getKey();
            $config =& $it->getCurrent();
            if (!is_array($config))
            {
                include_once(DAWN_ERRORS . 'ConfigError.php');
                $error =& new ConfigError(
                    'fields.' . $it->getKey(),
                    'window',
                    $this->window->getObjectId()
                );
                $error->halt();
            }
            if (!$this->table->hasField($name))
            {
                exit("Field $name doesn't exist");
            }
            $field =& $this->table->getField($name);
            if (!isset($config['type']))
            {
                if ($field->isReference())
                {
                    $config['type']      = 'static_table_lookup';
                    list($tName, $fName) = $field->getReference();
                    $config['table']     = $tName;
                    $config['field']     = $fName;
                    $table =& $this->getTable($tName);
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
            $widgets[$name] = $config;
        }
        return $name;
    }

    function createToolbar(&$config)
    {
        $buttons = array();
        $it =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            $settings = $this->getButtonConfig($it->getKey(), $it->getCurrent());
            if ($settings !== false)
            {
                $buttons[$it->getKey()] = $settings;
            }
        }
        $widgets =& $this->getProperty('widgets');
        $widgets['_commands']  = array('type' => 'hidden');
        $widgets['_toolbar'] = array(
            'type'          => 'toolbar',
            'command_field' => '_' . $this->getObjectId() . '_command',
            'buttons'       => $buttons
        );
    }

    function load($data)
    {
        parent::load($data['record']);
        $this->table =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->load($data['query']);
    }

    function save()
    {
        return array(
            'record' => parent::save(),
            'query'  => $this->query->save()
        );
    }

    function buildWindow()
    {
        $this->activeRecord = false;
        $this->buildQuery();
        if (isset($_POST['_' . $this->getObjectId() . '_command']))
        {
            $toolbar =& $this->getWidget('_toolbar');
            $button  =& $toolbar->getButton(
                $_POST['_' . $this->getObjectId() . '_command']
            );
            $button->handleClick();
        }
        $this->buildWidgets();
    }

    function buildQuery()
    {
        $this->query->prepare();
        $this->query->finish();
        $query =& Query::combine(
            $this->query,
            $this->getDynamicFormProperty(
                $this->getProperty('tracker'), 'query'
            )
        );
        $database =& $this->table->getDatabase();
        $result   =& $database->query($query->getSql());
        if ($result->getRowCount() == 1)
        {
            $this->activeRecord =& new Record(
                $this->table, $result->getRow(0, ECLIPSE_DB_ASSOC)
            );
        }
        else
        {
            $this->setErrorMessage($this->getProperty('error_empty'));
        }
    }

    function buildWidgets()
    {
        $hidden =& $this->getWidget('_keys');
        $it =& new ArrayIterator($this->getProperty('hidden'));
        for ( ; $it->isValid(); $it->next())
        {
            $hidden->setField(
                $it->getCurrent(),
                $this->getFieldValue($it->getCurrent(), false)
            );
        }
        $widget =& $this->getWidget($this->getProperty('field'));
        $widget->setValue($this->getFieldValue($this->getProperty('field')));
    }

    function show($indent)
    {
        $page =& $this->getOwner();
        $this->url = isset($_POST['_url']) ? $_POST['_url'] : $page->getUrl();
        $command  =& $this->getWidget('_commands');
        $command->setField('_url', $this->url);
        $command->setField(
            '_' . $this->getObjectId() . '_command',
            current($this->getValidButtons())
        );
        parent::show($indent);
    }

    function getFormMethod()
    {
        return 'post';
    }

    function getValidButtons()
    {
        return array('edit');
    }

    function getFieldValue($field, $useDefault = true)
    {
        if ($this->activeRecord !== false &&
            $this->activeRecord->hasValue($field))
        {
            return $this->activeRecord->getValue($field);
        }
        if ($useDefault)
        {
            return '-';
        }
        return '';
    }

    function getButtonConfig($name, &$settings)
    {
        if (!in_array($name, $this->getValidButtons()))
        {
            return false;
        }
        include_once(DAWN_SYSTEM . 'Config.php');
        $config =& Config::getInstance();
        if (!isset($settings['type']))
        {
            $settings['type'] = 'record_' . $name . '_button';
        }
        return $settings;
    }

    function &getActiveRecord()
    {
        return $this->activeRecord;
    }

    function &getActiveTable()
    {
        return $this->table;
    }
}
?>
