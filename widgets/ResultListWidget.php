<?php
class ResultListWidget extends Widget
{
    var $result;
    var $url;
    var $activeKey;
    var $makeLink;
    var $currentKey;
    var $currentRow;

    function ResultListWidget($id, &$window)
    {
        $this->Widget($id, $window);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('layout' , '');
        $this->setProperty('fields' , OBJECT_INVALID_VALUE);
        $this->setProperty('key'    , OBJECT_INVALID_VALUE);
        $this->setProperty('tracker', OBJECT_INVALID_VALUE);
        $this->setProperty('size'   , OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        $window =& $this->getOwner();
        $this->setProperty(
            'layout',
            array(
                'type'   => 'grid',
                'width'  => count($this->getProperty('fields')),
                'height' => $this->getProperty('size') + 1
            )
        );
        parent::postCreate();
    }

    function showComponent($indent, $name)
    {
        parent::showComponent($indent, $name);
        list($column, $row) = explode(',', $name);
        if ($row == 0)
        {
            if ($column == 0)
            {
                $this->initialize();
            }
            if ($this->result->getRowCount() > 0)
            {
                $this->showHeader($indent, $column);
            }
            return;
        }
        if ($column == 0)
        {
            $this->synchronize($row - 1);
        }
        if ($this->currentRow === false)
        {
            Html::showLine($indent, '&nbsp;');
            return;
        }
        $this->showField($indent, $column);
    }

    function initialize()
    {
        $window          =& $this->getOwner();
        $page            =& $window->getOwner();
        $this->url       =& new Url($page->getUrl());
        $tracker         =  $this->getProperty('tracker');
        $this->activeKey =  $this->url->hasParameter($tracker)
            ? $this->url->getParameter($tracker)
            : false;
    }

    function synchronize($rowIndex)
    {
        if ($rowIndex >= $this->result->getRowCount())
        {
            $this->currentRow = false;
            return;
        }
        $this->currentRow =& $this->result->getRow(
            $rowIndex, ECLIPSE_DB_ASSOC
        );
        $key = array();
        $it  =& new ArrayIterator($this->getProperty('key'));
        for ( ; $it->isValid(); $it->next())
        {
            array_push($key, $this->currentRow[$it->getCurrent()]);
        }
        $this->currentKey = Table::encodeValues($key);
        $this->makeLink = $this->activeKey != $this->currentKey;
        if ($this->makeLink)
        {
            $this->url->setParameter(
                $this->getProperty('tracker'), $this->currentKey
            );
        }
    }

    function showHeader($indent, $index)
    {
        $fields =& $this->getProperty('fields');
        Html::showLine($indent, '<b>' . $fields[$index]['caption'] . '</b>');
    }

    function showField($indent, $index)
    {
        $fields =& $this->getProperty('fields');
        $field  =  $this->currentRow[$fields[$index]['field']];
        $width  =  $fields[$index]['width'];
        if ($width > 0 && strlen($field) > $width)
        {
            $field = substr($field, 0, $width) . '...';
        }
        if (!$this->makeLink)
        {
            Html::showLine(
                $indent,
                '<b>', $field, '</b>'
            );
            return;
        }
        Html::showLine(
            $indent,
            $this->url->getLink(
                $field,
                $this->getProperty('css')
            )
        );
    }

    function setQueryResult(&$result)
    {
        $this->result =& $result;
    }
}
?>
