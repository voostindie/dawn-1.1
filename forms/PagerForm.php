<?php
require_once(DAWN_FORMS . 'BasicForm.php');
require_once(ECLIPSE_ROOT . 'PagedQuery.php');

// TODO: Joins must be added to the fieldlist, so that if they are available,
// they aren't computed automatically

class PagerForm extends BasicForm
{
    var $table;
    var $query;
    var $result;
    var $pageIndex;
    var $pageCount;

    function PagerForm($name, &$page)
    {
        $this->Form($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('tracker' , 'tracker');
        $this->setProperty('fields'  , OBJECT_INVALID_VALUE);
        $this->setProperty('search'  , '');
        $this->setProperty('filter'  , '');
        $this->setProperty('size'    , 20);
        $this->setProperty('order'   , '');
        $this->setProperty('widgets' , '');
        $this->setProperty('header'  , array('type' => 'label'));
        $this->setProperty('list_css', 'widget');
        $this->setProperty('pager'   , array('type' => 'pager'));
    }

    function postCreate()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->createQuery();
        $this->createFields();
        $this->createKey();
        $this->createWidgets();
        parent::postCreate();
    }

    function createQuery()
    {
        $this->table =& $this->getStaticFormProperty(
            $this->getProperty('tracker'), 'table'
        );
        $this->setProperty('table', $this->table->getName());
        $this->query =& new Query($this->table);
        $key         =  $this->table->getPrimaryKey();
        $columns     =  $this->getProperty('fields');
        $fields      =  is_array($columns)
            ? array_keys($columns)
            : $this->parseList($columns);
        $order = $this->parseList($this->getProperty('order'));
        $this->query->create(
            array(
                'fields' => array_merge($fields, $key),
                'order'  => count($order) ? $order : $fields
            )
        );
        if (($filter = $this->getProperty('filter')) != '')
        {
            $table =& $this->getStaticFormProperty($filter, 'table');
            list($joins, $tables) = $this->query->resolveJoin(
                $table->getName()
            );
            $this->setProperty('filter_tables', $tables);
            $this->setProperty('filter_joins' , $joins);
        }
    }

    function createFields()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        $columns = $this->getProperty('fields');
        if (!is_array($columns))
        {
            $it      =& new ArrayIterator($this->parseList($columns));
            $columns =  array();
            for ( ; $it->isValid(); $it->next())
            {
                $columns[$it->getCurrent()] = array();
            }
        }
        $fields =  array();
        $it     =& new ArrayIterator($columns);
        for ( ; $it->isValid(); $it->next())
        {
            $name   =  $it->getKey();
            $column =& $it->getCurrent();
            $field  =  join('_', $this->query->getFullFieldNames($name));
            $width  =  isset($column['width']) ? $column['width'] : 0;
            if (isset($column['caption']))
            {
                $caption = Translator::resolveText($column['caption']);
            }
            else
            {
                $caption = Translator::resolveText($field, 'FIELD');
            }
            array_push(
                $fields,
                array(
                    'field'   => $field,
                    'width'   => $width,
                    'caption' => $caption
                )
            );
        }
        $this->setProperty('fields', $fields);
    }

    function createKey()
    {
        $key =  array();
        $it  =& new ArrayIterator($this->table->getPrimaryKey());
        for ( ; $it->isValid(); $it->next())
        {
            array_push(
                $key,
                join('_', $this->query->getFullFieldNames($it->getCurrent()))
            );
        }
        $this->setProperty('key', $key);
    }

    function createWidgets()
    {
        $this->setProperty('text_empty' , Translator::getText('PAGER_EMPTY'));
        $this->setProperty('text_filled', Translator::getText('PAGER_FULL'));
        $this->setProperty(
            'widgets',
            array(
                'header' => $this->getProperty('header'),
                'list'   => array(
                    'type'    => 'result_list',
                    'fields'  => $this->getProperty('fields'),
                    'tracker' => $this->getProperty('tracker'),
                    'key'     => $this->getProperty('key'),
                    'size'    => $this->getProperty('size'),
                    'css'     => $this->getProperty('list_css')
                ),
                'pager'  => $this->getProperty('pager')
            )
        );
        $this->deleteProperty('header');
        $this->deleteProperty('pager');
        $this->deleteProperty('list_css');
        $this->deleteProperty('fields');
        $this->deleteProperty('key');
    }

    function load(&$data)
    {
        parent::load($data['pager']);
        $this->table =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->load($data['query']);
    }

    function buildWindow()
    {
        $this->buildQuery();
        $this->updatePageUrl();
        $header =& $this->getWidget('header');
        if ($this->result->getRowCount() == 0)
        {
            $header->setValue($this->getProperty('text_empty'));
        }
        else
        {
            $text = $this->getProperty('text_filled');
            $text = str_replace('%1', $this->pageIndex + 1, $text);
            $text = str_replace('%2', $this->pageCount > 0 ? $this->pageCount : 1, $text);
            $header->setValue($text);
        }
        $list  =& $this->getWidget('list');
        $list->setQueryResult($this->result);
        $pager =& $this->getWidget('pager');
        $pager->setSize($this->pageCount);
        $pager->setIndex($this->pageIndex);
    }

    function buildQuery()
    {
        $id =  $this->getObjectId();
        $this->query->prepare();
        $this->query->finish();
        if (($search = $this->getProperty('search')) != '')
        {
            $query =& $this->getDynamicFormProperty($search, 'query');
            $this->query =& Query::combine($this->query, $query);
            if ($this->getDynamicFormProperty($search, 'new'))
            {
                unset($_GET[$id . '_index']);
                unset($_GET[$id . '_count']);
            }
        }
        if (($filter = $this->getProperty('filter')) != '')
        {
            $query =& $this->getDynamicFormProperty($filter, 'query');
            $this->query =& Query::combine(
                $this->query,
                $query,
                $this->getProperty('filter_tables'),
                $this->getProperty('filter_joins')
            );
        }
        $database =& $this->getDatabase();
        $query    =& new PagedQuery(
            $database->query($this->query->getSql()),
            $this->getProperty('size')
        );
        $this->pageIndex = isset($_GET[$id . '_index'])
            ? (int)$_GET[$id . '_index'] : 0;
        $this->pageCount = isset($_GET[$id . '_count'])
            ? (int)$_GET[$id . '_count'] : $query->getPageCount();
        $this->result =& $query->getPage($this->pageIndex);
        $this->first = false;
        if ($this->result->getRowCount() > 0)
        {
            $this->first =  array();
            $table       =  $this->table->getName();
            $row         =  $this->result->getRow(0, ECLIPSE_DB_ASSOC);
            $it          =& new ArrayIterator($this->table->getPrimaryKey());
            for ( ; $it->isValid(); $it->next())
            {
                $field = $table . '_' . $it->getCurrent();
                array_push($this->first, $row[$field]);
            }
        }
    }

    function updatePageUrl()
    {
        $id   =  $this->getObjectId();
        $page =& $this->getOwner();
        $page->setUrlParameter($this, $id . '_index', $this->pageIndex);
        $page->setUrlParameter($this, $id . '_count', $this->pageCount);
    }

    function save()
    {
        return array(
            'pager' => parent::save(),
            'query' => $this->query->save()
        );
    }

    function &getDynamicProperty($name)
    {
        switch($name)
        {
            case 'first': return $this->first;
        }
        return parent::getDynamicProperty($name);
    }

    function getPageIndex()
    {
        return $this->pageIndex;
    }

    function getPageCount()
    {
        return $this->pageCount;
    }

    function &getQueryResult()
    {
        return $this->result;
    }
}
?>
