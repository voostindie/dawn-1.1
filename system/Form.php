<?php
require_once(DAWN_SYSTEM . 'Window.php');
require_once(DAWN_SYSTEM . 'Html.php');

class Form extends Window
{
    function Form($name, &$page)
    {
        $this->Window($name, $page, 'widgets');
    }

    function postCreate()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        parent::postCreate();
        $this->setProperty(
            'title',
             Translator::resolveText(
                $this->getObjectId(), 'FORM_TITLE', 'FORM'
             )
        );
    }

    function addScript($name, $script)
    {
        $page =& $this->getPage();
        $page->addScript($name, $script);
    }

    function show($indent)
    {
        $this->showTitle($indent);
        if (($method = $this->getFormMethod()) != '')
        {
            $page =& $this->getPage();
            Html::showLine(
                $indent,
                '<form style="margin: 0px 0px 0px 0px" method="',
                $method, '" action="', $page->getUrl($this), '"',
                $this->getFormOptions(), '>'
            );
            $indent++;
        }
        parent::show($indent);
        if ($method != '')
        {
            $indent--;
            Html::showLine($indent, '</form>');
        }
    }

    function showTitle($indent)
    {
        Html::showLine(
            $indent,
            '<h1', $this->getProperty('css'), '>', $this->getTitle(), '</h1>'
        );
    }

    function getTitle()
    {
        return $this->getProperty('title');
    }

    function &getPage()
    {
        return $this->getOwner();
    }

    function &getApplication()
    {
        $page =& $this->getOwner();
        return $page->getOwner();
    }

    function &getUser()
    {
        $application = &$this->getApplication();
        return $application->getUser();
    }

    function &getDatabase()
    {
        $application =& $this->getApplication();
        $manager     =& $application->getDatabaseManager();
        return $manager->getDatabase();
    }

    function &getTable($name)
    {
        $application =& $this->getApplication();
        $manager     =& $application->getDatabaseManager();
        return $manager->getTable($name);
    }

    function &getComponentFactory()
    {
        include_once(DAWN_SYSTEM . 'WidgetFactory.php');
        return WidgetFactory::getInstance();
    }

    function getDefaultLayout()
    {
        return 'flow';
    }

    function getDefaultCss()
    {
        return 'form';
    }

    function &getStaticFormProperty($form, $property)
    {
        $page =& $this->getOwner();
        return $page->getStaticComponentProperty($this, $form, $property);
    }

    function &getDynamicFormProperty($form, $property)
    {
        $page =& $this->getOwner();
        return $page->getDynamicComponentProperty($this, $form, $property);
    }

    function &getWidget($name)
    {
        return $this->getComponent($name);
    }

    /***
     * Return the method used to post this form. If this form cannot be posted
     * this method should return the empty string
     ***/
    function getFormMethod()
    {
        return '';
    }

    /***
     * Return this form's encoding type
     ***/
    function getFormOptions()
    {
        return '';
    }
}
?>
