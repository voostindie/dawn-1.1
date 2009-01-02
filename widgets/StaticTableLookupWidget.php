<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class StaticTableLookupWidget extends Widget
{
    var $value;
    var $isset;
    var $table;
    var $query;
    var $result;

    function StaticTableLookupWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->isset = false;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('table'   , OBJECT_INVALID_VALUE);
        $this->setProperty('field'   , OBJECT_INVALID_VALUE);
        $this->setProperty('lookup'  , OBJECT_INVALID_VALUE);
        $this->setProperty('order'   , '');
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
            $lookup = array($lookup);
        }
        $this->query->create(
            array(
                'fields'   => $lookup,
                'criteria' => array(
                    array(
                        'name'   => $this->getProperty('field'),
                        'fields' => $this->getProperty('field')
                    )
                )
            )
        );
        return count($lookup);
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
        if ($this->isset)
        {
            $this->query->setClause($this->getProperty('field'), $this->value);
        }
        $this->query->finish();
        $database =& $this->table->getDatabase();
        $this->result = $database->query($this->query->getSql());
    }

    function showWidget($indent)
    {
        if ($this->result->getRowCount() == 0)
        {
            Html::showLine($indent, '-');
            return;
        }
        Html::showLine($indent, $this->getValue(0));
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

    function getValue($rowIndex)
    {
        $row    =& $this->result->getRow($rowIndex, ECLIPSE_DB_NUM);
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
}
?>
