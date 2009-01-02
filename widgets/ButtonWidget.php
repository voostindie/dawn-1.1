<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class ButtonWidget extends Widget
{
    function ButtonWidget($id, &$form)
    {
        $this->Widget($id, $form);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('caption'      , $this->getObjectId());
        $this->setProperty('command_field', '');
        $this->setProperty('default'      , false);
        $this->setProperty('value'        , '');
        $this->setProperty('button_css'   , 'button');
    }

    function postCreate()
    {
        parent::postCreate();
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty(
            'caption',
            Translator::resolveText($this->getProperty('caption'), 'BUTTON')
        );
        if (($css = $this->getProperty('button_css')) != '')
        {
            $this->setProperty('button_css', ' class="' . $css . '"');
        }
    }

    function showWidget($indent)
    {
        $type = $this->getProperty('default') ? 'submit' : 'button';
        Html::showLine(
            $indent,
            '<input type="', $type, '" value="', $this->getProperty('caption'),
            '" ', $this->getOptions(), $this->getProperty('button_css'),  '>'
        );
    }

    function getOptions()
    {
        return '';
    }

    function getValue()
    {
        return $this->getProperty('value');
    }
}
?>
