<?php
require_once(DAWN_FORMS . 'BasicForm.php');
require_once(ECLIPSE_ROOT . 'PagedQuery.php');

class ExtendedRelationViewForm extends BasicForm
{
    var $isBuilt;
    var $query;
    var $result;
    var $pageIndex;
    var $pageCount;
    var $editButton;
    var $deleteButton;

    function ExtendedRelationViewForm($name, &$page)
    {
        $this->BasicForm($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('tracker'    , 'tracker');
        $this->setProperty('table'      , OBJECT_INVALID_VALUE);
        $this->setProperty('fields'     , OBJECT_INVALID_VALUE);
        $this->setProperty('order'      , '');
        $this->setProperty('buttons'    , OBJECT_INVALID_VALUE);
        $this->setProperty('size'       , 5);
        $this->setProperty('widgets'    , '');
        $this->setProperty('info'       , '');
        $this->setProperty('info_css'   , 'text');
        $this->setProperty('spacer_css' , 'label');
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
        if (($css = $this->getProperty('spacer_css')) != '')
        {
            $this->setProperty('spacer_css', ' class="' . $css . '"');
        }
        $size = $this->getProperty('size');
        $widgets = array();
        $widgets['error'] = array(
            'type'    => 'label',
            'css'     => 'error',
            'caption' => ''
        );
        $widgets['info'] = array(
            'type'    => 'label',
            'css'     => $this->getProperty('info_css'),
            'caption' => $info
        );
        $widgets['message'] = array(
            'type'    => 'label',
            'css'     => 'widget',
            'caption' => ''
        );
        for ($i = 0; $i < $size; $i++)
        {
            $widgets['record_' . $i] = array(
                'type'   => 'record_view',
                'table'  => $this->getProperty('table'),
                'fields' => $this->getProperty('fields')
            );
            if ($i < $size - 1)
            {
                $widgets['spacer_' . $i] = array(
                    'type'    => 'label',
                    'css'     => 'label',
                    'caption' => ''
                );
            }
        }
        $widgets['command'] = array(
            'type' => 'hidden'
        );
        $widgets['pager'] = $this->getProperty('pager');
        $widgets['new']   = $this->getButtonConfig('new', 'relation_new_button');
        $this->setProperty('widgets', $widgets);
        $this->editButton   =& $this->createButton(
            'edit', 'relation_edit_button'
        );
        $this->deleteButton =& $this->createButton(
            'delete', 'relation_delete_button'
        );
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty('error_empty', Translator::getText('ERROR_RELATION_EMPTY'));
        $this->deleteProperty('info');
        $this->deleteProperty('info_css');
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
        $result['key_field'] =  '_key';
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
        $error =& $this->getWidget('message');
        $error->setValue('');
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
        $size = $this->result->getRowCount();
        for ($i = 0; $i < $size; $i++)
        {
            if ($i > 0)
            {
                $widget =& $this->getWidget('spacer_' . ($i - 1));
                $widget->setCaption('<hr' . $this->getProperty('spacer_css') . ' />');
            }
            $widget =& $this->getWidget('record_' . $i);
            $widget->setRow($this->result->getRow($i, ECLIPSE_DB_ASSOC));
        }
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
        $query =& new PagedQuery(
            $this->query->execute(), $this->getProperty('size')
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

    function showButtons($indent, &$row)
    {
        $key =  array();
        $it  =& new ArrayIterator($this->getProperty('key'));
        for ( ; $it->isValid(); $it->next())
        {
            array_push($key, $row[$it->getCurrent()]);
        }
        $key = Table::encodeValues($key);
        $this->editButton->setKey($key);
        $this->deleteButton->setKey($key);
        Html::showLine($indent, '<div class="toolbar">');
        $user =& $this->getUser();
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
}
?>
