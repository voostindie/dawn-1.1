<?php
require_once(DAWN_SYSTEM . 'Form.php');

class AboutForm extends Form
{
    function AboutForm($name, &$page)
    {
        $this->Form($name, $page);
    }
    
    function preCreate()
    {
        parent::preCreate();
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty(
            'title',
            Translator::getText('SITE_TITLE')
        );
        $this->setProperty(
            'layout',
            array(
                'type'   => 'border',
                'center' => 'about_widget'
            )
        );
        $this->setProperty(
            'widgets',
            array(
                'about_widget' => array(
                    'type'    => 'label',
                    'caption' => 'This is getting along nicely...'
                )
            )
        );
    }
}
?>
