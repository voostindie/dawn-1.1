<?php
class RelationListWidget extends Widget
{
    var $result;
    var $fieldCount;
    var $currentRow;

    function RelationListWidget($id, &$window)
    {
        $this->Widget($id, $window);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('layout' , '');
        $this->setProperty('fields' , OBJECT_INVALID_VALUE);
        $this->setProperty('size'   , OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        $window =& $this->getOwner();
        $this->setProperty(
            'layout',
            array(
                'type'   => 'grid',
                'width'  => count($this->getProperty('fields')) + 1,
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
            //Html::showLine($indent, '&nbsp;');
            return;
        }
        if ($column == $this->fieldCount)
        {
            $form =& $this->getOwner();
            $form->showButtons($indent, $this->currentRow);
            return;
        }
        $this->showField($indent, $column);
    }

    function initialize()
    {
        $this->fieldCount = count($this->getProperty('fields'));
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
    }

    function showHeader($indent, $index)
    {
        if ($index == $this->fieldCount)
        {
            Html::showLine($indent, '&nbsp;');
            return;
        }
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
        Html::showLine($indent, htmlspecialchars($field));
    }

    function setQueryResult(&$result)
    {
        $this->result =& $result;
    }
}
?>
