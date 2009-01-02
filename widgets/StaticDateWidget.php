<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class StaticDateWidget extends Widget
{
    var $value;
    var $isset;

    function StaticDateWidget($id, &$form)
    {
        $this->Widget($id, $form);
        $this->value = '';
        $this->isset = false;
    }

    function showWidget($indent)
    {
        if (!$this->isset)
        {
            Html::showLine($indent, '-');
            return;
        }
        Html::showLine($indent, $this->getDate());
    }

    function getDate()
    {
        include_once(DAWN_SYSTEM . 'Locale.php');
        list($year, $month, $day) =  explode('-', $this->value);
        $locale                   =& Locale::getInstance();
        return $day . ' ' . $locale->getMonth($month) . ' ' . $year;
    }

    function setValue($value)
    {
        if (!is_null($value) && !empty($value) && $value != '-')
        {
            $this->value = $value;
            $this->isset = true;
        }
    }
}
?>
