<?php
require_once(DAWN_SYSTEM . 'Form.php');
require_once(DAWN_SYSTEM . 'Query.php');

class TrackerForm extends Form
{
    var $table;
    var $query;
    var $keySize;
    var $current;

    function TrackerForm($name, &$page)
    {
        $this->Form($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('table', OBJECT_INVALID_VALUE);
        $this->setProperty('pager', '');
        $this->setProperty('search', '');
        $this->setProperty(
            'layout',
             array(
                'type'   => 'border',
                'west'   => 'caption',
                'center' => 'key'
            )
        );
        $this->setProperty(
            'widgets',
            array(
                'caption' => array(
                    'type'    => 'label',
                    'css'     => 'label'
                ),
                'key' => array(
                    'type' => 'label',
                    'css'  => 'normal'
                )
            )
        );
    }

    function postCreate()
    {
        $this->table =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->create(array('criteria' => $this->createCriteria()));
        parent::postCreate();
        $this->updateCurrent();
    }

    function createCriteria()
    {
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
        return $criteria;
    }

    function load(&$data)
    {
        parent::load($data['tracker']);
        $this->table =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->load($data['query']);
        $this->updateCurrent();
    }

    function updateCurrent()
    {
        $this->current = false;
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
        $this->buildQuery();
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

    function buildQuery()
    {
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
        if ($this->current !== false &&
            count($this->current) == $this->getProperty('key_size'))
        {
            $key  = $this->table->getPrimaryKey();
            $size = $this->getProperty('key_size');
            for ($i = 0; $i < $size; $i++)
            {
                $this->query->setClause($key[$i], $this->current[$i]);
            }
        }
    }

    function show($indent)
    {
        $widget =& $this->getWidget('caption');
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
            case 'defaults': return array();
        }
        return parent::getDynamicProperty($name);
    }

    function &getCurrent()
    {
        assert('Debug::checkState("TrackerForm", DEBUG_STATE_SHOW)');
        return $this->current;
    }

    function &getQuery()
    {
        return $this->query;
    }

    function &getActiveTable()
    {
        return $this->table;
    }
}
?>
