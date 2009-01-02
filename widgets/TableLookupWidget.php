<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class TableLookupWidget extends Widget
{
    var $table;
    var $query;
    var $value;

    function TableLookupWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('table'   , OBJECT_INVALID_VALUE);
        $this->setProperty('field'   , OBJECT_INVALID_VALUE);
        $this->setProperty('lookup'  , OBJECT_INVALID_VALUE);
        $this->setProperty('order'   , OBJECT_INVALID_VALUE);
        $this->setProperty('filter'  , '');
        $this->setProperty('widths'  , '');
        $this->setProperty('template', '');
    }

    function postCreate()
    {
        $lookupSize = $this->createQuery();
        $this->setProperty(
            'widths',
            array_pad(
                $this->parseList($this->getProperty('widths')),
                $lookupSize,
                ''
            )
        );
        if ($this->getProperty('template') == '')
        {
            $list = array();
            for ($i = 1; $i < $lookupSize + 1; $i++)
            {
                array_push($list, '%' . $i);
            }
            $this->setProperty('template', join(' ', $list));
        }
        $this->deleteProperty('lookup');
        $this->deleteProperty('order');
        $this->setProperty('lookup_size', $lookupSize);
        parent::postCreate();
    }

    function createQuery()
    {
        $form        =& $this->getOwner();
        $this->table =& $form->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $lookup = $this->getProperty('lookup');
        if (!is_array($lookup))
        {
            $lookup = $this->parseList($lookup);
        }
        $size = count($lookup);
        if (!in_array($this->getProperty('field'), $lookup))
        {
            array_push($lookup, $this->getProperty('field'));
            $this->setProperty('field_index', $size);
        }
        else
        {
            $this->setProperty(
                'field_index',
                array_search($this->getProperty('field'), $lookup)
            );
        }
        $this->query->create(
            array(
                'fields'   => $lookup,
                'criteria' => $this->createCriteria(),
                'order'    => $this->getProperty('order')
            )
        );
        if (($filter = $this->getProperty('filter')) != '')
        {
            $form  =& $this->getOwner();
            $table =& $form->getStaticFormProperty($filter, 'table');
            list($joins, $tables) = $this->query->resolveJoin(
                $table->getName()
            );
            $this->setProperty('filter_tables', $tables);
            $this->setProperty('filter_joins' , $joins);
        }
        return $size;
    }

    function createCriteria()
    {
        return array();
    }

    function load($data)
    {
        parent::load($data['lookup']);
        $form        =& $this->getOwner();
        $this->table =& $form->getTable($this->getProperty('table'));
        $this->query =& new Query($this->table);
        $this->query->load($data['query']);
    }

    function buildWidget()
    {
        $this->query->prepare();
        $this->buildQuery();
        $this->query->finish();
        if (($filter = $this->getProperty('filter')) != '')
        {
            $form  =& $this->getOwner();
            $query =& $form->getDynamicFormProperty($filter, 'query');
            $this->query =& Query::combine(
                $this->query,
                $query,
                $this->getProperty('filter_tables'),
                $this->getProperty('filter_joins')
            );
        }
        $sql = 'SELECT DISTINCT ' . substr($this->query->getSql(), 7);
        $database =& $this->table->getDatabase();
        $this->result = $database->query($sql);
    }

    function buildQuery()
    {
    }

    function showWidget($indent)
    {
        Html::showLine(
            $indent,
            '<select name="', $this->getObjectId(), '"',
            $this->getProperty('css'), '>'
        );
        $match = $this->getProperty('field_index');
        $size  = $this->result->getRowCount();
        for ($i = 0; $i < $size; $i++)
        {
            $row =& $this->result->getRow($i, ECLIPSE_DB_NUM);
            $selected = $row[$match] == $this->value ? ' selected' : '';
            Html::showLine(
                $indent + 1,
                '<option value="', $row[$match], '"', $selected, '>',
                $this->getValue($row),
                '</option>'
            );
        }
        Html::showLine(
            $indent,
            '</select>'
        );
    }

    function setValue($value)
    {
        if ($value != '' && !is_null($value))
        {
            $this->value = $value;
            $this->isset = true;
        }
    }

    function save()
    {
        return array(
            'lookup' => parent::save(),
            'query'  => $this->query->save()
        );
    }

    function getValue(&$row)
    {
        $result =  $this->getProperty('template');
        $size   =  $this->getProperty('lookup_size');
        $widths =  $this->getProperty('widths');
        for ($i = 0; $i < $size; $i++)
        {
            $value = $row[$i];
            if ($widths[$i] != '' && strlen($value) > $widhts[$i])
            {
                $value = substr($value, 0, $widths[$i]) . '...';
            }
            $result = str_replace('%' . ($i + 1), $value, $result);
        }
        return $result;
    }

    function &getQuery()
    {
        return $this->query;
    }
}
?>
