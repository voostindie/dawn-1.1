<?php
require_once(DAWN_FORMS . 'BasicForm.php');
require_once(DAWN_SYSTEM . 'Record.php');

class RecordForm extends BasicForm
{
    var $table;
    var $query;
    var $activeRecord;
    var $postedRecord;
    var $doSelect;
    var $url;

    function RecordForm($name, &$page, $doSelect)
    {
        $this->BasicForm($name, $page);
        $this->doSelect = $doSelect;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('tracker'     , 'tracker');
        $this->setProperty('fields'      , OBJECT_INVALID_VALUE);
        $this->setProperty('buttons'     , '');
        $this->setProperty('widgets'     , '');
        $this->setProperty('info'        , '');
        $this->setProperty('info_css'    , 'text');
        $this->setProperty('normal_css'  , 'normal');
        $this->setProperty('required_css', 'required');
        $this->setProperty('width'       , 2);
    }

    function postCreate()
    {
        $this->table =& $this->getStaticFormProperty(
            $this->getProperty('tracker'), 'table'
        );
        $this->setProperty('table', $this->table->getName());
        $this->createMessageWidget();
        $fields = $this->getProperty('fields');
        if (!is_array($fields))
        {
            include_once(DAWN_ERRORS . 'ConfigError.php');
            $error =& new ConfigError(
                'fields',
                'window',
                $this->window->getObjectId()
            );
            $error->halt();
        }
        $fields = $this->createFields($fields);
        $hidden = array_diff($this->table->getPrimaryKey(), $fields);
        $this->setProperty('fields', $fields);
        $this->setProperty('hidden', $hidden);
        $buttons = $this->getProperty('buttons');
        if (!is_array($buttons))
        {
            $buttons = array();
        }
        $this->createToolbar($buttons);
        if ($this->doSelect)
        {
            $this->createQuery(array_unique(array_merge($fields, $hidden)));
        }
        $this->deleteProperty('buttons');
        $this->deleteProperty('info');
        $this->deleteProperty('info_css');
        $this->deleteProperty('normal_css');
        $this->deleteProperty('required_css');
        parent::postCreate();
    }

    function createMessageWidget()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        if (($info = $this->getProperty('info')) != '')
        {
            $info = Translator::getText($info);
        }
        else
        {
            $info = Translator::getText(
                strtoupper($this->getObjectId() . '_INFO')
            );
        }
        $widgets           = array();
        $settings          = array();
        $widgets['_keys']  = array('type' => 'hidden');
        $settings['_keys'] = array('row' => true, 'colspan' => 2);
        $widgets['_error'] = array(
            'type'    => 'label',
            'css'     => 'error',
            'caption' => ''
        );
        $settings['_error'] = array('row' => true, 'colspan' => 2);
        if ($info != '')
        {
            $widgets['_info'] = array(
                'type'    => 'label',
                'css'     => $this->getProperty('info_css'),
                'caption' => $info
            );
            $settings['_info'] = array('row' => true, 'colspan' => 2);
        }
        $this->setProperty('widgets', $widgets);
        $this->setProperty('settings', $settings);
        $this->setProperty(
            'error_empty',
            Translator::getText('ERROR_RECORD_EMPTY')
        );
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
                include_once(DAWN_ERRORS . 'ConfigError.php');
                $error =& new ConfigError(
                    'fields.' . $it->getKey(),
                    'window',
                    $this->window->getObjectId()
                );
                $error->halt();
            }
            array_push($fields, $it->getKey());
            $this->createField($it->getKey(), $field);
        }
        return $fields;
    }

    function createField($name, &$config)
    {
        if (!$this->table->hasField($name))
        {
            exit("Field $name doesn't exist");
        }
        $field =& $this->table->getField($name);
        if (!isset($config['type']))
        {
            if ($field->isReference())
            {
                $config['type']      = $this->getDefaultLookupWidget();
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
                $config['type'] = $this->getDefaultFieldWidget();
            }
        }
        if (!isset($config['css']))
        {
            $config['css'] = $field->isNullValid()
                ? $this->getProperty('normal_css')
                : $this->getProperty('required_css');
        }
        $access = isset($config['access']) ? $config['access'] : '';
        $widgets =& $this->getProperty('widgets');
        $widgets[$name . '_label'] = array(
            'type'    => 'label',
            'css'     => 'label',
            'caption' => $field->getCaption() . ':',
        	'access'  => $access
        );
        $widgets[$name] = $config;
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

    function createQuery($fields)
    {
        $this->query =& new Query($this->table);
        $this->query->create(array('fields' => join(', ', $fields)));
    }

    function load($data)
    {
        if ($this->doSelect)
        {
            parent::load($data['record']);
            $this->table =& $this->getTable($this->getProperty('table'));
            $this->query =& new Query($this->table);
            $this->query->load($data['query']);
        }
        else
        {
            parent::load($data);
            $this->table =& $this->getTable($this->getProperty('table'));
        }
    }

    function save()
    {
        if ($this->doSelect)
        {
            return array(
                'record' => parent::save(),
                'query'  => $this->query->save()
            );
        }
        else
        {
            return parent::save();
        }
    }

    function buildWindow()
    {
        $posted = false;
        if (isset($_POST['_' . $this->getObjectId() . '_command']))
        {
            $this->postedRecord =& new Record($this->table, $_POST);
            $it                 =& new ArrayIterator(
                $this->getDynamicFormProperty(
                    $this->getProperty('tracker'), 'defaults'
                )
            );
            for ( ; $it->isValid(); $it->next())
            {
                $this->postedRecord->setField(
                    $it->getKey(), $it->getCurrent()
                );
            }
            $posted = true;
        }
        $this->activeRecord = false;
        if ($this->doSelect)
        {
            $this->buildQuery();
        }
        if ($posted)
        {
            $toolbar =& $this->getWidget('_toolbar');
            $button  =& $toolbar->getButton(
                $_POST['_' . $this->getObjectId() . '_command']
            );
            if (!$button->handleClick())
            {
                $this->setErrorMessage($button->getErrorMessage());
                $this->buildWidgets($button->needsPost());
                return;
            }
        }
        $this->buildWidgets($posted);
    }

    function buildQuery()
    {
        $this->query->prepare();
        $this->query->finish();
        $current =& $this->getDynamicFormProperty(
            $this->getProperty('tracker'), 'current'
        );
        if ($current === false)
        {
            $this->setErrorMessage($this->getProperty('error_empty'));
            return;
        }
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

    function buildWidgets($posted)
    {
        $hidden =& $this->getWidget('_keys');
        $it =& new ArrayIterator($this->getProperty('hidden'));
        for ( ; $it->isValid(); $it->next())
        {
            $hidden->setField(
                $it->getCurrent(),
                $this->getFieldValue($it->getCurrent(), $posted, false)
            );
        }
        $it =& new ArrayIterator($this->getProperty('fields'));
        for ( ; $it->isValid(); $it->next())
        {
            $widget =& $this->getWidget($it->getCurrent());
            $widget->setValue(
                $this->getFieldValue($it->getCurrent(), $posted)
            );
        }
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

    function setErrorMessage($message)
    {
        $widget =& $this->getWidget('_error');
        $widget->setCaption($message);
    }

    function getFormMethod()
    {
        return 'post';
    }

    function getDefaultFieldWidget()
    {
        return 'static';
    }

    function getDefaultLookupWidget()
    {
        return 'static_table_lookup';
    }

    function getEmptyField($name)
    {
        return '-';
    }

    function getValidButtons()
    {
        return array();
    }

    function getFieldValue($field, $posted, $useDefault = true)
    {
        if ($posted)
        {
            return $this->postedRecord->getValue($field);
        }
        elseif ($this->activeRecord !== false &&
                $this->activeRecord->hasValue($field))
        {
            return $this->activeRecord->getValue($field);
        }
        elseif ($useDefault)
        {
            return $this->getEmptyField($field);
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
        if ($name == 'new' || $name == 'insert')
        {
            $access = $config->getEntry('database.tables.' .
                $this->table->getName() . '.insert.access', '');
        }
        elseif ($name == 'update')
        {
            $access = $config->getEntry('database.tables.' .
                $this->table->getName() . '.update.access', '');
        }
        elseif ($name == 'delete')
        {
            $access = $config->getEntry('database.tables.' .
                $this->table->getName() . '.delete.access', '');
        }
        else
        {
            $access = '';
        }
        if (!isset($settings['type']))
        {
            $settings['type'] = 'record_' . $name . '_button';
        }
        if (!isset($settings['access']))
        {
            $settings['access'] = $access;
        }
        return $settings;
    }

    function &getActiveRecord()
    {
        return $this->activeRecord;
    }

    function &getPostedRecord()
    {
        return $this->postedRecord;
    }

    function &getActiveTable()
    {
        return $this->table;
    }
}
?>
