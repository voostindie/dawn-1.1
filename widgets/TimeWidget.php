<?php
class TimeWidget extends Widget
{
    var $value;
    var $isset;

    function TimeWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->isset = false;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('step', 5);
    }

    function postCreate()
    {
        $name = $this->getObjectId();
		$script = <<<EOF_SCRIPT
function updateTime(form, index, value) {
    var values       = form.$name.value.split(':');
    values[index]    = value;
    form.$name.value = values[0] + ':' + values[1];
}

EOF_SCRIPT;
        $page =& $this->getPage();
        $page->addScript('updateTime', $script);
        parent::postCreate();
    }

    function showWidget($indent)
    {
        if ($this->isset)
        {
            list($hour, $minutes) = explode(':', $this->value);
        }
        else
        {
            $hour    = 9;
            $minutes = 0;
        }
        Html::showLine(
            $indent,
            '<input type="hidden" name="', $this->getObjectId(),
            '" value="', (int)$hour, ':', (int)$minutes, '">'
        );
        $this->showHour($indent, (int)$hour);
        $this->showMinutes($indent, (int)$minutes);
    }

    function showHour($indent, $index)
    {
        $this->startSelect($indent, 'hour', 0);
        for ($i = 0; $i < 24; $i++)
        {
            $this->showOption($indent, $i, $i, $index);
        }
        $this->endSelect($indent);
    }

    function showMinutes($indent, $index)
    {
        $this->startSelect($indent, 'minutes', 1);
        $step = $this->getProperty('step');
        for ($i = 0; $i < 60; $i += $step)
        {
            $this->showOption($indent, $i, $i, $index);
        }
        $this->endSelect($indent);
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

    function startSelect($indent, $name, $index)
    {
        $id = $this->getObjectId();
        Html::showLine(
            $indent,
            '<select name="', $id, '_', $name, '" onchange="',
            'updateTime(this.form, ', $index, ', this.value)"',
            $this->getProperty('css'), '>'
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
}
?>
