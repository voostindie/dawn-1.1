<?php
require_once(DAWN_SYSTEM . 'Layout.php');

class TableLayout extends Layout
{
    function TableLayout(&$owner)
    {
        $this->Layout($owner);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('settings', array());
    }

    function postCreate()
    {
        parent::postCreate();
        $this->createTable();
    }

    function createTable()
    {
        $table       =  array();
        $row         =  array();
        $column      =  array();
        $components  =  array();
        $settings    =& $this->getProperty('settings');
        $it          =& new ArrayIterator($this->getProperty('components'));
        for ( ; $it->isValid(); $it->next())
        {
            $component =& $it->getCurrent();
            $config    = isset($settings[$component])
                ? $settings[$component]
                : array('row' => false);
            if (isset($settings[$component]))
            {
                if (isset($components[0]))
                {
                    $column['components'] = $components;
                    array_push($row, $column);
                    $components = array();
                }
                $column = array(
                    'style'   => $this->getAttribute('style'  , $config),
                    'colspan' => $this->getAttribute('colspan', $config),
                    'rowspan' => $this->getAttribute('rowspan', $config)
                );
            }
            if (isset($config['row']) && $config['row'] && isset($row[0]))
            {
                array_push($table, $row);
                $row = array();
            }
            array_push($components, $component);
        }
        if (isset($components[0]))
        {
            $column['components'] = $components;
            array_push($row, $column);
        }
        if (isset($row[0]))
        {
            array_push($table, $row);
        }
        $this->setProperty('table', $table);
        $this->deleteProperty('settings');
    }

    function show($indent)
    {
        parent::show($indent);
        $owner =& $this->getOwner();
        $this->showTable($indent, $owner->getProperty('css'));
    }

    function showTable($indent, $css)
    {
        $it =& new ArrayIterator($this->getProperty('table'));
        if ($it->isValid())
        {
            Html::showLine(
                $indent,
                '<table', $css, ' cellpadding="0" cellspacing="0">'
            );
            for ( ; $it->isValid(); $it->next())
            {
                $this->showRow($indent + 1, $css, $it->getCurrent());
            }
            Html::showLine($indent, '</table>');
        }
    }

    function showRow($indent, $css, &$row)
    {
        Html::showLine($indent, '<tr', $css, '>'
        );
        for ($it =& new ArrayIterator($row); $it->isValid(); $it->next())
        {
            $this->showColumn($indent + 1, $css, $it->getCurrent());
        }
        Html::showLine($indent, '</tr>');
    }

    function showColumn($indent, $css, &$column)
    {
        Html::showLine(
            $indent,
            '<td', $css, $column['style'],
            $column['colspan'], $column['rowspan'], '>'
        );
        $this->showComponents($indent + 1, $column['components']);
        Html::showLine($indent, '</td>');
    }

    function getAttribute($name, &$config)
    {
        return (isset($config[$name]) && $config[$name] != '')
            ? ' ' . $name . '="' . $config[$name] . '"'
            : '';
    }
}
