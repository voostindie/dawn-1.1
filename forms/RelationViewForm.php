<?php
require_once(DAWN_FORMS . 'BasicForm.php');
require_once(ECLIPSE_ROOT . 'PagedQuery.php');

class RelationViewForm extends BasicForm
{
    var $isBuilt;
    var $query;
    var $result;
    var $pageIndex;
    var $pageCount;
    var $editButton;
    var $deleteButton;

    function RelationViewForm($name, &$page)
    {
        $this->BasicForm($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('tracker' , 'tracker');
        $this->setProperty('table'   , OBJECT_INVALID_VALUE);
        $this->setProperty('fields'  , OBJECT_INVALID_VALUE);
        $this->setProperty('order'   , '');
        $this->setProperty('buttons' , OBJECT_INVALID_VALUE);
        $this->setProperty('size'    , 10);
        $this->setProperty('widgets' , '');
        $this->setProperty('info'    , '');
        $this->setProperty('info_css', 'text');
        $this->setProperty('list_css', 'widget');
        $this->setProperty(
            'pager',
            array(
                'type'      => 'pager',
                'auto_hide' => 'true'
            )
        );
    }

    function postCreate()
    {
        $this->createQuery();
        $this->createFields();
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
        $widgets = array();
        $widgets['error'] = array(
            'type'    => 'label',
            'css'     => 'error',
            'caption' => ''
        );
        if ($info != '')
        {
            $widgets['info'] = array(
                'type'    => 'label',
                'css'     => $this->getProperty('info_css'),
                'caption' => $info
            );
        }
        $widgets['message'] = array(
            'type'    => 'label',
            'css'     => 'widget',
            'caption' => ''
        );
        $widgets['command'] = array('type' => 'hidden');
        $widgets['list']    = array(
            'type'    => 'relation_list',
            'fields'  => $this->getProperty('fields'),
            'size'    => $this->getProperty('size'),
            'css'     => $this->getProperty('list_css')
        );
        $widgets['pager'] = $this->getProperty('pager');
        $widgets['new']   = $this->getButtonConfig(
            'new', 'relation_new_button'
        );
        $this->setProperty('widgets', $widgets);
        $this->deleteProperty('list_css');
        $this->deleteProperty('info');
        $this->deleteProperty('info_css');
        $this->deleteProperty('pager');
        $this->editButton   =& $this->createButton(
            'edit', 'relation_edit_button'
        );
        $this->deleteButton =& $this->createButton(
            'delete', 'relation_delete_button'
        );
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty('error_empty', Translator::getText('ERROR_RELATION_EMPTY'));
        parent::postCreate();
    }

    function createQuery()
    {
        $table       =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($table);
        $key         =  $table->getPrimaryKey();
        $columns     =  $this->getProperty('fields');
        $fields     =  is_array($columns)
            ? array_keys($columns)
            : $this->parseList($columns);
        $order  = $this->parseList($this->getProperty('order'));
        $this->query->create(
            array(
                'fields' => array_merge($fields, $key),
                'order'  => count($order) ? $order : $fields
            )
        );
        $it =& new ArrayIterator($key);
        for ( ; $it->isValid(); $it->next())
        {
            $current =& $it->getCurrent();
            $current = $table->getName() . '_' . $current;
        }
        $this->setProperty('key', $key);
        $sourceTable =& $this->getStaticFormProperty(
                $this->getProperty('tracker'), 'table'
        );
        list($joins, $tables) = $this->query->resolveJoin(
            $sourceTable->getName()
        );
        $this->setProperty('tables', $tables);
        $this->setProperty('joins', $joins);
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
                $caption = Translator::getText($column['caption']);
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

    function getButtonConfig($name, $type)
    {
        $result  = array();
        $buttons = $this->getProperty('buttons');
        if (is_array($buttons) &&
            isset($buttons[$name]) &&
            is_array($buttons[$name]))
        {
            $result = $buttons[$name];
        }
        if (!isset($result['type']))
        {
            $result['type'] = $type;
        }
        if (!isset($result['css']))
        {
            $result['css'] = 'toolbar';
        }
        $result['command_field'] = '_' . $this->getObjectId() . '_command';
        $result['key_field']     =  '_key';
        return $result;
    }

    function &createButton($name, $type)
    {
        include_once(DAWN_SYSTEM . 'WidgetFactory.php');
        $config              =  $this->getButtonConfig($name, $type);
        $factory             =& WidgetFactory::getInstance();
        $class               =  $factory->getClass($config['type']);
        $button              =& new $class($name, $this);
        $button->create($config);
        $this->setProperty($name . '_path', $factory->getFullClassPath($config['type']));
        $this->setProperty($name . '_class', $class);
        return $button;
    }

    function &loadButton($name, $data)
    {
        include_once($this->getProperty($name . '_path'));
        $class  =  $this->getProperty($name . '_class');
        $button =& new $class($name, $this);
        $button->load($data);
        return $button;
    }

    function load($data)
    {
        parent::load($data['relation']);
        $table       =& $this->getTable($this->getProperty('table'));
        $this->query =& new Query($table);
        $this->query->load($data['query']);
        $this->editButton   =& $this->loadButton('edit',   $data['edit']);
        $this->deleteButton =& $this->loadButton('delete', $data['delete']);
    }

    function buildWindow()
    {
        $this->isBuilt = false;
        $current = $this->getDynamicFormProperty(
            $this->getProperty('tracker'), 'current'
        );
        if ($current === false)
        {
            return;
        }
        $error =& $this->getWidget('error');
        $command = '_' . $this->getObjectId() . '_command';
        if (isset($_POST[$command]))
        {
            switch($_POST[$command])
            {
                case 'new':
                    $button =& $this->getWidget('new');
                    break;
                case 'edit':
                    $button =& $this->editButton;
                    break;
                case 'delete':
                    $button =& $this->deleteButton;
                    break;
            }
            if (!$button->handleClick())
            {
                $error->setValue($button->getErrorMessage());
            }
        }
        $this->buildQuery();
        $command =& $this->getWidget('command');
        $command->setField('_' . $this->getObjectId() . '_command', 'new');
        $command->setField('_key', '');
        $widget =& $this->getWidget('list');
        $widget->setQueryResult($this->result);
        $pager =& $this->getWidget('pager');
        $pager->setSize($this->pageCount);
        $pager->setIndex($this->pageIndex);
        $this->isBuilt = true;
    }

    function buildQuery()
    {
        $id = $this->getObjectId();
        $this->query->prepare();
        $this->query->finish();
        $this->query =& Query::combine(
            $this->query,
            $this->getDynamicFormProperty(
                $this->getProperty('tracker'), 'query'
            ),
            $this->getProperty('tables'),
            $this->getProperty('joins')
        );
        $database =& $this->getDatabase();
        $sql = 'SELECT DISTINCT ' . substr($this->query->getSql(), 7);
        $query =& new PagedQuery(
            $database->query($sql), $this->getProperty('size')
        );
        $this->pageIndex = isset($_GET[$id . '_index'])
            ? (int)$_GET[$id . '_index'] : 0;
        $this->pageCount = isset($_GET[$id . '_count'])
            ? (int)$_GET[$id . '_count'] : $query->getPageCount();
        $this->result =& $query->getPage($this->pageIndex);
        if ($this->result->getRowCount() == 0)
        {
            $message =& $this->getWidget('message');
            $message->setValue($this->getProperty('error_empty'));
        }
    }

    function show($indent)
    {
        if (!$this->isBuilt)
        {
            return;
        }
        $page =& $this->getOwner();
        $url = isset($_POST['_url']) ? $_POST['_url'] : $page->getUrl();
        $command  =& $this->getWidget('command');
        $command->setField('_url', $url);
        parent::show($indent);
    }

    function showButtons($indent, $row)
    {
        $user =& $this->getUser();
        $key  =  array();
        $it   =& new ArrayIterator($this->getProperty('key'));
        for ( ; $it->isValid(); $it->next())
        {
            array_push($key, $row[$it->getCurrent()]);
        }
        $key = Table::encodeValues($key);
        $this->editButton->setKey($key);
        $this->deleteButton->setKey($key);
        Html::showLine($indent, '<div class="toolbar">');
        if ($user->hasAccess($this->editButton->getAccess()))
        {
            $this->editButton->show($indent + 1);
        }
        if ($user->hasAccess($this->deleteButton->getAccess()))
        {
            $this->deleteButton->show($indent + 1);
        }
        Html::showLine($indent, '</div>');
    }

    function save()
    {
        return array(
            'relation' => parent::save(),
            'query'    => $this->query->save(),
            'edit'     => $this->editButton->save(),
            'delete'   => $this->deleteButton->save()
        );
    }

    function getFormMethod()
    {
        return 'post';
    }

    function &getQuery()
    {
        return $this->query;
    }
}
?>
