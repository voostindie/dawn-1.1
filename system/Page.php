<?php
require_once(DAWN_SYSTEM . 'Window.php');
require_once(DAWN_SYSTEM . 'Html.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');
require_once(ECLIPSE_ROOT . 'Url.php');

/***
 * A <code>Page</code> is a <code>Window</code> that shows a number of
 * forms on a web page.
 * <p>
 *   In <b>Dawn</b> there are two types of pages: modal pages and popup
 *   pages. The only difference between the two types is that the first
 *   shows the global application menu, while the second doesn't.
 * </p>
 ***/
class Page extends Window
{
    var $url;
    var $parameters;

    // CREATORS

    function Page($name, &$application)
    {
        $this->Window($name, $application, 'forms');
        $this->url        =& new Url(APP_URL);
        $this->parameters =  array();
        $this->url->setParameter('page', $name);
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('popup' , false);
        $this->setProperty('layout', '');
    }

    function postCreate()
    {
        include_once(DAWN_SYSTEM . 'Translator.php');
        $id = $this->getObjectId();
        $this->setProperty(
            'title',
            Translator::resolveText($id, 'PAGE_TITLE', 'PAGE')
        );
        $this->setProperty(
            'caption',
            Translator::resolveText($id, 'PAGE_CAPTION', 'PAGE')
        );
        if (!is_array($this->getProperty('layout')))
        {
            $this->createDefaultLayout();
        }
        $this->setProperty('scripts', array());
        parent::postCreate();
        $script = '';
        $it =& new ArrayIterator($this->getProperty('scripts'));
        for ( ; $it->isValid(); $it->next())
        {
            $script .= $it->getCurrent() . "\n";
        }
        $this->deleteProperty('scripts');
        $this->setProperty('script', base64_encode($script));
    }

    /***
     * Create the default layout for the page. This method is only called if no
     * layout was set in the configuration at all. It produces a FlowLayout
     * with all forms in the page configuration, except for the forms that have
     * the name 'tracker' in them. Many pages have trackers but these should
     * almost never be shown. By giving them a name with the word 'tracker' in
     * it, these forms are automatically hidden.
     * @returns void
     * @private
     ***/
    function createDefaultLayout()
    {
        $forms =  array();
        $it    =& new ArrayIterator($this->getProperty('forms'));
        for ( ; $it->isValid(); $it->next())
        {
            if (strpos('tracker', $it->getKey()) === false)
            {
                array_push($forms, $it->getKey());
            }
        }
        $this->setProperty(
            'layout',
            array(
                'type'       => 'flow',
                'components' => join(', ', $forms)
            )
        );
    }

    /***
     * Add a block of JavaScript to the page. A name has to be specified for the
     * script, so that each script is only shown once. Also, this allows forms
     * and widgets on the page to reuse scripts.
     * @param $name the name of the script
     * @param $script the JavaScript code
     * @returns void
     ***/
    function addScript($name, $script)
    {
        assert('Debug::checkState(\'Page\', DEBUG_STATE_CREATE)');
        assert('$id = $this->getObjectId()');
        assert('Debug::log("Page: adding script \'$name\' to page \'$id\'")');
        $scripts = &$this->getProperty('scripts');
        if (!isset($scripts[$name]))
        {
            $scripts[$name] = $script;
        }
    }

    // MANIPULATORS

    function show($indent)
    {
        $script =& base64_decode($this->getProperty('script'));
        if ($script != '')
        {
            Html::showLine($indent, '<script type="text/javascript"><!--');
            Html::showLines($indent + 1, $script);
            Html::showLine($indent, '// --></script>');
        }
        parent::show($indent);
    }

    function setUrlParameter(&$form, $name, $value)
    {
        assert('Debug::checkState("Page", DEBUG_STATE_BUILD)');
        $formName = $form->getObjectId();
        if (!isset($this->parameters[$formName]))
        {
            $this->parameters[$formName] = array();
        }
        array_push($this->parameters[$formName], $name);
        $this->url->setParameter($name, $value);
    }

    // ACCESSORS

    function getTitle()
    {
        return $this->getProperty('title');
    }

    function getCaption()
    {
        return $this->getProperty('caption');
    }

    function &getComponentFactory()
    {
        include_once(DAWN_SYSTEM . 'FormFactory.php');
        return FormFactory::getInstance();
    }

    function getDefaultCss()
    {
        return 'page';
    }

    function getDefaultLayout()
    {
        return 'border';
    }

    function &getForm($name)
    {
        return $this->getComponent($name);
    }

    function &getApplication()
    {
        return $this->getOwner();
    }

    function &getUser()
    {
        $application =& $this->getApplication();
        return $application->getUser();
    }

    function getUrl()
    {
        assert('Debug::checkState("Page", DEBUG_STATE_SHOW)');
        return $this->url->getUrl();
    }
}
?>
