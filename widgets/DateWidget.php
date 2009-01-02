<?php
class DateWidget extends Widget
{
    var $value;
    var $isset;

    function DateWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->isset = false;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('first', '-10');
        $this->setProperty('last' , '+10');
    }

    function postCreate()
    {
        $name = $this->getObjectId();
		$script = <<<EOF_SCRIPT
function updateDate(form, index, value) {
    var values       = form.$name.value.split('-');
    values[index]    = value;
    form.$name.value = values[0] + '-' + values[1] + '-' + values[2];
}

EOF_SCRIPT;
        $page =& $this->getPage();
        $page->addScript('updateDate', $script);
        parent::postCreate();
    }

    function showWidget($indent)
    {
        if ($this->isset)
        {
            list($year, $month, $day) = explode('-', $this->value);
        }
        else
        {
            $day   = 1;
            $month = 1;
            $year  = $this->getYear('first');
        }
        Html::showLine(
            $indent,
            '<input type="hidden" name="', $this->getObjectId(),
            '" value="', $year, '-', $month, '-', $day, '">'
        );
        $this->showDays($indent, $day);
        $this->showMonths($indent, $month);
        $this->showYears($indent, $year);
    }

    function showDays($indent, $index)
    {
        $this->startSelect($indent, 'day', 2);
        for ($i = 1; $i < 32; $i++)
        {
            $this->showOption($indent, $i, $i, $index);
        }
        $this->endSelect($indent);
    }

    function showMonths($indent, $index)
    {
        include_once(DAWN_SYSTEM . 'Locale.php');
        $locale =& Locale::getInstance();
        $this->startSelect($indent, 'month', 1);
        for ($i = 1; $i < 13; $i++)
        {
            $this->showOption(
                $indent, $locale->getMonth($i), $i, $index
            );
        }
        $this->endSelect($indent);
    }

    function showYears($indent, $index)
    {
        $this->startSelect($indent, 'year', 0);
        $first = $this->getYear('first');
        $last  = $this->getYear('last');
        for ($i = $first; $i <= $last; $i++)
        {
            $this->showOption($indent, $i, $i, $index);
        }
        $this->endSelect($indent);
    }

    function startSelect($indent, $name, $index)
    {
        $id = $this->getObjectId();
        Html::showLine(
            $indent,
            '<select name="', $id, '_', $name, '" onchange="',
            'updateDate(this.form, ', $index, ', this.value)"',
            $this->getProperty('css'), '>'
        );
    }

    function showOption($indent, $name, $value, $selected)
    {
        Html::showLine(
            $indent + 1,
            '<option value="', $value, '"',
            ($value == $selected ? ' selected' : ''),
            '>', $name, '</option>'
        );
    }

    function endSelect($indent)
    {
        Html::showLine($indent, '</select>');
    }


    function setValue($value)
    {
        if ($value != '' && !is_null($value))
        {
            $this->value = $value;
            $this->isset = true;
        }
    }

    function getYear($name)
    {
        $year = date('Y');
        $value = $this->getProperty($name);
        if ($value == '')
        {
            $value = '+0';
        }
        $operator = substr($value, 0, 1);
        if ($operator == '-')
        {
            $value = $year - (int)substr($value, 1);
        }
        elseif ($operator == '+')
        {
            $value = $year + (int)substr($value, 1);
        }
        return $value;
    }
}
?>
