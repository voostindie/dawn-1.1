<?php
require_once(DAWN_SYSTEM . 'Form.php');

class MenuForm extends Form
{
    function MenuForm($name, &$page)
    {
        $this->Form($name, $page);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty(
            'layout', 
            array(
                'type'   => 'border',
                'center' => 'menu',
                'east'   => 'user'
            )
        );
        $this->setProperty(
            'widgets',
            array(
                'menu' => array(
                    'type' => 'menu',
                ),
                'user' => array(
                    'type' => 'user'
                )
            )
        );
    }
    
    function getTitle()
    {
        $page        =& $this->getPage();
        return parent::getTitle() . ': <i>' . $page->getTitle() . '</i>';
    }
}
?>
