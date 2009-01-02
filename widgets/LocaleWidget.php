<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class LocaleWidget extends Widget
{
    function LocaleWidget($name, &$form)
    {
        $this->Widget($name, $form);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('locales', '');
        $this->setProperty('caption', '');
    }
    
    function postCreate()
    {
        parent::postCreate();
        include_once(DAWN_SYSTEM . 'Translator.php');
        if ($this->getProperty('caption') != '')
        {
            $this->setProperty(
                'caption',
                Translator::getText($this->getProperty('caption')) . ': '
            );
        }
        if ($this->getProperty('locales') != '')
        {
            $list    =  $this->parseList($this->getProperty('locales'));
            $locales =  array();
            for ($it =& new ArrayIterator($list); $it->isValid(); $it->next())
            {
                $locales[$it->getCurrent()] = Translator::getText(
                    'LOCALE_' . strtoupper($it->getCurrent()),
                    strtoupper($it->getCurrent()),
                    ucfirst($it->getCurrent())
                );
            }
            $this->setProperty('locales', $locales);
        }
    }
    
    function showWidget($indent)
    {
        $user    =& $this->getUser();
        $current =  $user->getLocale();
        Html::showLine(
            $indent,
            $this->getProperty('caption'),
            '<select onchange="document.location.href = ', 
            '\'', APP_URL, '?_locale=\' + ',
            'this.options[this.selectedIndex].value;">'
        );
        $it =& new ArrayIterator($this->getProperty('locales'));
        for ( ; $it->isValid(); $it->next())
        {
            Html::showLine(
                $indent + 1,
                '<option value="', $it->getKey(), '"',
                ($it->getKey() == $current ? ' selected' : ''),
                '>', $it->getCurrent(), "</option>"
            );
        }
        Html::showLine($indent, "</select>");
    }

    function getDefaultCss()
    {
        return 'menu';   
    }
}
?>
