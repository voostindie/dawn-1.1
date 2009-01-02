<?php
require_once(DAWN_FORMS . 'BasicForm.php');

// TODO: add joins to fields, so that they needn't always be computed
// automatically.

class SearchForm extends BasicForm
{
    var $table;
    var $query;
    var $new;

    function SearchForm($name, &$page)
    {
        $this->Form($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('tracker'   , 'tracker');
        $this->setProperty('fields'    , OBJECT_INVALID_VALUE);
        $this->setProperty('widgets'   , '');
        $this->setProperty('normal_css', 'normal');
        $this->setProperty('width'     , 2);
    }

    function postCreate()
    {
        $this->table =& $this->getStaticFormProperty(
            $this->getProperty('tracker'), 'table'
        );
        $this->setProperty('table', $this->table->getName());
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
        $this->query =& new Query($this->table);
        $this->query->create(
            array('criteria' => $this->createCriteria($fields))
        );
        $this->setProperty('regexes', $this->createRegexes($fields));
        $this->setProperty('widgets', $this->createWidgets($fields));
        $this->setProperty('fields', $this->createFields($fields));
        $this->createToolbar();
        parent::postCreate();
    }

    function createCriteria(&$config)
    {
        $clauses =  array();
        $it      =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            $current =& $it->getCurrent();
            $name    =  str_replace('.', '_', $it->getKey());
            $clause  =  isset($current['clause'])
                ? $current['clause'] : array();
            $clause['name'] = $name;
            if (!isset($clause['fields']))
            {
                $clause['fields'] = $it->getKey();
            }
            if (!isset($clause['operators']))
            {
                $size = count($this->parseList($clause['fields']));
                $clause['operators'] = array_fill(0, $size, 'LIKE');
                if (!isset($current['regex']))
                {
                    $current['regex'] = array(
                        'ltrim' => array(
                            'search'  => '/^\s+/',
                            'replace' => ''
                        ),
                        'rtrim' => array(
                            'search'  => '/\s+$/',
                            'replace' => ''
                        ),
                        'multi' => array(
                            'search'  => '/\*/',
                            'replace' => '%'
                        ),
                        'single' => array(
                            'search'  => '/\?/',
                            'replace' => '_'
                        ),
                        'mask' => array(
                            'search' => '/(.*)/',
                            'replace' => '%\\1'
                        )
                    );
                }
            }
            array_push($clauses, $clause);
        }
        return $clauses;
    }

    function createRegexes(&$config)
    {
        $regexes = array();
        $it =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            $name    =  str_replace('.', '_', $it->getKey());
            $current =& $it->getCurrent();
            if (isset($current['regex']))
            {
                $regex = $this->createRegex($current['regex']);
                if ($regex !== false)
                {
                    $regexes[$name] = $regex;
                }
            }
        }
        return $regexes;
    }


    function createRegex(&$config)
    {
        if (!is_array($config))
        {
            $config = array('only_one' => $config);
        }
        $search  = array();
        $replace = array();
        $it =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            $regex =& $it->getCurrent();
            if (isset($regex['search']) && isset($regex['replace']))
            {
                array_push($search , $regex['search']);
                array_push($replace, $regex['replace']);
            }
        }
        if (count($search))
        {
            return array(
                'search'  => $search,
                'replace' => $replace
            );
        }
        return false;
    }

    function createWidgets(&$config)
    {
        $manager =& $this->table->getDatabaseManager();
        $widgets =  array();
        $it      =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            $current =& $it->getCurrent();
            $name    =  str_replace('.', '_', $it->getKey());
            $widget  =  isset($current['widget'])
                ? $current['widget'] : array();
            if (!isset($widget['type']))
            {
                $widget['type'] = 'text';
                if (!isset($widget['size']))
                {
                    $widget['size'] = 15;
                }
            }
            if (isset($current['caption']))
            {
                include_once(DAWN_SYSTEM . 'Translator.php');
                $caption = Translator::getText($current['caption']);
            }
            else
            {
                list($tableName, $fieldName) =
                    $this->query->getFullFieldNames($it->getKey());
                $table   =& $manager->getTable($tableName);
                $field   =& $table->getField($fieldName);
                $caption =  $field->getCaption();
            }
            $widgets[$name . '_label'] = array(
                'type'    => 'label',
                'css'     => 'label',
                'caption' => $caption . ':'
            );
            $widgets[$name] = $widget;
        }
        return $widgets;
    }

    function createFields(&$config)
    {
        $fields  =  array();
        $it      =& new ArrayIterator($config);
        for ( ; $it->isValid(); $it->next())
        {
            array_push($fields, str_replace('.', '_', $it->getKey()));
        }
        return $fields;
    }

    function createToolbar()
    {
        $buttons = array(
            'search' => array(
                'type'    => 'form_submit_button'
            ),
            'clear' => array(
                'type' => 'form_reset_button'
            )
        );
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
        parent::load($data['search']);
        $this->table =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->load($data['query']);
    }

    function buildWindow()
    {
        $command  =& $this->getWidget('_commands');
        $command->setField('_' . $this->getObjectId() . '_command', 'search');
        $page =& $this->getOwner();
        $this->new =  isset($_POST['_' . $this->getObjectId() . '_command']);
        if ($this->new)
        {
            $source =& $_POST;
            $prefix = '';
        }
        else
        {
            $source =& $_GET;
            $prefix = $this->getObjectId() . '_';
        }
        $this->query->prepare();
        $regexes =& $this->getProperty('regexes');
        $it      =& new ArrayIterator($this->getProperty('fields'));
        for ( ; $it->isValid(); $it->next())
        {
            $name =& $it->getCurrent();
            if (isset($source[$prefix . $name])
                && $source[$prefix . $name] != '')
            {
                $value = $source[$prefix . $name];
                $widget =& $this->getWidget($name);
                $widget->setValue($value);
                $page->setUrlParameter(
                    $this, $this->getObjectId() . '_' . $name, $value
                );
                if (isset($regexes[$name]))
                {
                    $value = preg_replace(
                        $regexes[$name]['search'],
                        $regexes[$name]['replace'],
                        $value
                    );
                }
                $this->query->setClauseAll($name, $value);
            }
        }
        $this->query->finish();
    }

    function save()
    {
        return array(
            'search' => parent::save(),
            'query'  => $this->query->save()
        );
    }

    function getFormMethod()
    {
        return 'post';
    }

    function &getDynamicProperty($name)
    {
        switch ($name)
        {
            case 'query': return $this->query;
            case 'new'  : return $this->new;
        }
        return parent::getDynamicProperty($name);
    }
}
?>
