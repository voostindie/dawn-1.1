<?php
require_once(DAWN_SYSTEM . 'Component.php');
require_once(DAWN_SYSTEM . 'Html.php');

/***
 * Widgets are the low-level components of the system: text fields, labels, but
 * can also be more complicated entities like grids and tables.
 ***/
class Widget extends Component
{
    function Widget($name, &$form)
    {
        $this->Component($name, $form);
    }
    
    function preCreate()
    {
        parent::preCreate();
        $this->setProperty(
            'layout',
            array(
                'components' => array('widget')
            )
        );
    }
    
    function build()
    {
        parent::build();
        $this->buildWidget();
    }

    function buildWidget()
    {
    }
    
    function showComponent($indent, $name)
    {
        parent::showComponent($indent, $name);
        if ($name == 'widget')
        {
            $this->showWidget($indent);   
        }   
    }

    function setValue($value)
    {
    }
    
    function showWidget($indent)
    {
    }
    
    function &getForm()
    {
        return $this->getOwner();
    }

    function &getPage()
    {
        $form =& $this->getOwner();
        return $form->getOwner();
    }

    function &getApplication()
    {
        $page =& $this->getPage();
        return $page->getOwner();
    }

    function &getUser()
    {
        $application =& $this->getApplication();
        return $application->getUser();
    }

    function getDefaultCss()
    {
        return 'widget';
    }

    function getDefaultLayout()
    {
        return 'vertical';
    }
}
?>
