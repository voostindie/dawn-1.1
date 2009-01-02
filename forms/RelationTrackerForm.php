<?php
require_once(DAWN_SYSTEM . 'Form.php');
require_once(DAWN_SYSTEM . 'Query.php');

/***
 * For the relation tracker to work, a field in the tracked table must refer to
 * exactly one field in the primary key of the parent tracker. If that field
 * isn't found, an error is thrown.
 ***/
class RelationTrackerForm extends Form
{
    var $table;
    var $query;
    var $current;

    function RelationTrackerForm($name, &$page)
    {
        $this->Form($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('table' , OBJECT_INVALID_VALUE);
        $this->setProperty('parent', OBJECT_INVALID_VALUE);
        $this->setProperty('pager' , '');
        $this->setProperty('search', '');
        $this->setProperty(
            'layout',
             array(
                'type'   => 'border',
                'west'   => 'key_caption, link_caption',
                'center' => 'key, link'
            )
        );
        $this->setProperty(
            'widgets',
            array(
                'key_caption' => array(
                    'type'    => 'label',
                    'css'     => 'label'
                ),
                'key' => array(
                    'type' => 'label',
                    'css'  => 'normal'
                ),
                'link_caption' => array(
                    'type' => 'label',
                    'css'  => 'label'
                ),
                'link' => array(
                    'type' => 'label',
                    'css'  => 'normal'
                )
            )
        );
    }

    function postCreate()
    {
        $this->table =& $this->getTable($this->getProperty('table'));
        $criteria    = array();
        $it          =& new ArrayIterator($this->table->getPrimaryKey());
        for ( ; $it->isValid(); $it->next())
        {
            array_push(
                $criteria,
                array(
                    'name'   => $it->getCurrent(),
                    'fields' => $it->getCurrent()
                )
            );
        }
        $this->setProperty('key_size', count($criteria));
        $link = $this->resolveRelation();
        $this->setProperty(
            'in_key',
            in_array($link, $this->table->getPrimaryKey())
        );
        if (!$this->getProperty('in_key'))
        {
            array_push(
                $criteria,
                array(
                    'name'   => $link,
                    'fields' => $link
                )
            );
        }
        $this->query =& new Query($this->table);
        $this->query->create(array('criteria' => $criteria));
        parent::postCreate();
        $this->updateCurrent(true);
    }

    function resolveRelation()
    {
        $parent =& $this->getStaticFormProperty(
            $this->getProperty('parent'), 'table'
        );
        $parentKey = $parent->getPrimaryKey();
        $index     = -1;
        $link      = '';
        $it        =& new ArrayIterator($this->table->getFields());
        for ( ; $it->isValid(); $it->next())
        {
            $field =& $this->table->getField($it->getCurrent());
            if ($field->isReference())
            {
                list($tableName, $fieldName) = $field->getReference();
                if ($tableName == $parent->getName() &&
                    in_array($fieldName, $parentKey))
                {
                    if ($index > -1)
                    {
                        // TODO: throw a proper error here
                        exit('Multiple candidates found!');
                    }
                    $index = array_search($fieldName, $parentKey);
                    $link  = $it->getCurrent();
                    break;
                }
            }
        }
        if ($index == -1)
        {
            // TODO: throw a nice error here
            exit("No reference to parent's primary key exists.");
        }
        $this->setProperty('link_index', $index);
        $this->setProperty('link_field', $link);
        return $link;
    }
    
    function load(&$data)
    {
        parent::load($data['tracker']);
        $this->table =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->load($data['query']);
        $this->updateCurrent(false);
    }

    function updateCurrent($creationMode)
    {
        $this->current = false;
        $parent        = $this->getProperty('parent');
        if ($this->getProperty('link_index') != -1)
        {
            if ($creationMode)
            {
                $current = $this->getStaticFormProperty($parent, 'current');
            }
            else
            {
                $current = $this->getDynamicFormProperty($parent, 'current');
            }
            if ($current === false)
            {
                return;
            }
        }
        if (isset($_GET[$this->getObjectId()]))
        {
            $values = Table::decodeValues($_GET[$this->getObjectId()]);
            if (count($values) == $this->getProperty('key_size'))
            {
                $this->current = $values;
            }
        }
    }

    function buildWindow()
    {
        $this->query->prepare();
        if (($search = $this->getProperty('search')) != '')
        {
            if ($this->getDynamicFormProperty($search, 'new'))
            {
                $this->current = false;
            }
        }
        if ($this->current === false &&
            ($pager = $this->getProperty('pager')) != '')
        {
            $this->current = $this->getDynamicFormProperty($pager, 'first');
        }
        $size = $this->getProperty('key_size');
        if ($this->current !== false && count($this->current) == $size)
        {
            $key = $this->table->getPrimaryKey();
            for ($i = 0; $i < $size; $i++)
            {
                $this->query->setClause($key[$i], $this->current[$i]);
            }
            if (!$this->getProperty('in_key'))
            {
                $link  = $this->getProperty('link_field');
                $index = $this->getProperty('link_index');
                $current = $this->getDynamicFormProperty(
                    $this->getProperty('parent'), 'current'
                );
                $this->query->setClause($link, $current[$index]);
            }
        }
        $this->query->finish();
        if ($this->current !== false)
        {
            $page =& $this->getOwner();
            $page->setUrlParameter(
                $this,
                $this->getObjectId(),
                Table::encodeValues($this->current)
            );
        }
    }

    function show($indent)
    {
        $widget =& $this->getWidget('key_caption');
        $widget->setCaption($this->table->getName() . ':');
        $widget =& $this->getWidget('key');
        if ($this->current === false)
        {
            $widget->setCaption('- invalid -');
        }
        else
        {
            $caption = array();
            $key  =& $this->table->getPrimaryKey();
            $size =  $this->getProperty('key_size');
            for ($i = 0; $i < $size; $i++)
            {
                array_push($caption, $key[$i] . ' = ' . $this->current[$i]);
            }
            $widget->setCaption(join(' AND ', $caption));
        }
        $widget =& $this->getWidget('link_caption');
        $widget->setCaption($this->getProperty('parent') . ':');
        $widget =& $this->getWidget('link');
        if (($index = $this->getProperty('link_index')) == -1)
        {
            $widget->setCaption('- not required -');
        }
        else
        {
            $current =& $this->getDynamicFormProperty(
                $this->getProperty('parent'), 'current'
            );
            if ($current === false)
            {
                $widget->setCaption('- invalid -');
            }
            else
            {
                $widget->setCaption($current[$index]);
            }
        }
        parent::show($indent);
    }

    function save()
    {
        return array(
            'tracker' => parent::save(),
            'query'   => $this->query->save()
        );
    }

    function &getStaticProperty($name)
    {
        switch ($name)
        {
            case 'table'  : return $this->table;
            case 'current': return $this->current;
        }
        return parent::getStaticProperty($name);
    }

    function &getDynamicProperty($name)
    {
        switch ($name)
        {
            case 'query'   : return $this->query;
            case 'current' : return $this->current;
            case 'defaults': return $this->getDefaults();
        }
        return parent::getDynamicProperty($name);
    }

    function &getCurrent()
    {
        assert('Debug::checkState("RelationTrackerForm", DEBUG_STATE_SHOW)');
        return $this->current;
    }

    function getDefaults()
    {
        $index  = $this->getProperty('link_index');
        $field  = $this->getProperty('link_field');
        $current = $this->getDynamicFormProperty(
            $this->getProperty('parent'), 'current'
        );
        return array($field => $current[$index]);
    }
}
?>
