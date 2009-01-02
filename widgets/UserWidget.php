<?php
require_once(DAWN_SYSTEM . 'Widget.php');

class UserWidget extends Widget
{
    function UserWidget($name, &$form)
    {
        $this->Widget($name, $form);
    }
    
    function preCreate()
    {
        parent::preCreate();
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
        $this->setProperty(
            'logout', 
            ' (<a href="' . APP_URL . '?_logout=1"' . 
                $this->getProperty('css') . '>' . 
                Translator::getText('USER_LOGOUT') . '</a>)'
        );
    }
    
    function showWidget($indent)
    {
        $user =& $this->getUser();
        Html::showLine(
            $indent, 
            $this->getProperty('caption'),
            '<b>', $user->getDescription(), '</b>',
            $this->getProperty('logout')
        );
    }

    function getDefaultCss()
    {
        return 'menu';   
    }
}
?>
