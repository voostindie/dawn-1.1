<?php
require_once(DAWN_SYSTEM . 'Component.php');
require_once(DAWN_SYSTEM . 'Html.php');

/**
 * Class Site generates HTML for a page.
 *
 * Class Page handles the content of a single page. This class puts some additional output around
 * it, like an HTML header and footer, the system menu and copyright information. This class is
 * pretty straightforward, and could easily have been merged with class Application. The reasons
 * for not doing this were:
 * 1. It would complicate system logic (a big Application class)
 * 2. It gives the configuration file a separate 'site' section. See more below.
 *
 * By default, this class generates an HTML page using a FlowLayout. First the menu is shown; then
 * then the page itself, and finally the copyright information. If this is not what is needed for
 * some site, this behavior can be overriden by putting a 'layout' section in the 'site' section
 * of the main configuration file. For example:
 *
 * site {
 *     layout {
 *         type  : border;
 *         west  : menu;
 *         center: page;
 *         south : copyright;
 *     }
 * }
 *
 * This example still prints the menu, the page and the copyright information, but puts the menu
 * on the left of the main page, using a BorderLayout. By omitting components from the layout,
 * these will not be shown in the output. If, in the example above, the line 'south : copyright;'
 * would be left out, no copyright information will be printed.
 */
class Site extends Component {

    // DATA MEMBERS

    var $page;

    // CREATORS

    /**
     * Create a new Site object.
     * @param $application the page to generate HTML for
     */
    function Site(&$application) {
        $this->Component('site', $application);
    }

    function preCreate() {
        parent::preCreate();
        $this->setProperty('charset'        , 'iso-8859-1');
        $this->setProperty('style'          , '');
        $this->setProperty('developer'      , '');
        $this->setProperty('developer_url'  , '');
        $this->setProperty('webmaster'      , '');
        $this->setProperty('webmaster_email', '');
        $this->setProperty(
            'layout',
            array(
                'type'       => 'flow',
                'components' => array('menu', 'page', 'copyright')
            )
        );
    }

    function postCreate() {
        include_once(DAWN_SYSTEM . 'Translator.php');
        parent::postCreate();
        $this->setProperty(
            'title',
            Translator::getText('SITE_TITLE')
        );
        $this->setProperty(
            'description',
            Translator::getText('SITE_DESCRIPTION')
        );
        $this->setProperty(
            'keywords',
            Translator::getText('SITE_KEYWORDS')
        );
        $this->setProperty(
            'generated',
            str_replace(
                '%1',
                $this->getFooterLink(DAWN_URL, 'Dawn ' . DAWN_VERSION),
                Translator::getText('SITE_GENERATED')
            )
        );
        $this->setProperty('developed_by', '');
        if ($this->getProperty('developer') != ''
        	|| $this->getProperty('developer_url') != '') {
            $this->setProperty(
                'developed_by',
                Translator::getText('SITE_DEVELOPER') . ': ' .
                    $this->getFooterLink(
                        $this->getProperty('developer_url'),
                        $this->getProperty('developer'),
                        'http://'
                    )
            );
        }
        $this->setProperty('supported_by', '');
        if ($this->getProperty('webmaster') != ''
        	|| $this->getProperty('webmaster_email') != '') {
            $this->setProperty(
                'supported_by',
                Translator::getText('SITE_WEBMASTER') . ': ' .
                    $this->getFooterLink(
                        $this->getProperty('webmaster_email'),
                        $this->getProperty('webmaster'),
                        'mailto:'
                    )
            );
        }
    }

    // MANIPULATORS

    /**
     * Show the site
     * @returns void
     */
    function show($indent = 0) {
        $owner      =& $this->getOwner();
        $this->page =& $this->owner->getPage();
        $indent     =  $this->showHeader($indent);
        parent::show($indent + 1);
        $this->showFooter($indent);
    }

    /**
     * Show the component as specified with $name. This ought to be either 'menu', 'page' or
     * 'copyright'.
     * @protected
     */
    function showComponent($indent, $name) {
        parent::showComponent($indent, $name);
        switch($name) {
            case 'menu':
                $this->showMenu($indent);
                break;
            case 'page':
                $this->showPage($indent);
                break;
            case 'copyright':
                $this->showCopyright($indent);
                break;
        }
    }

    /**
     * Generate the HTML header. If the style of the site hasn't been set, the
     * default stylesheet '<code>dawn.css</code>' in the root directory of
     * <b>Dawn</b> is included.
     * @private
     */
    function showHeader($indent) {
        $title = $this->getProperty('title') . ': ' . $this->page->getTitle();
        Html::showBlock(
            $indent,
            '<!doctype html public "-//W3C//DTD XHTML 1.0 Strict//EN">',
            '<html>'
        );
        Html::showLine(++$indent, '<head>');
        Html::showLine(
            ++$indent,
            '<meta http-equiv-"Content-Type" content="text/html; charset=',
            $this->getProperty('charset'), '">'
        );
        Html::showLine(
            $indent,
            '<meta http-equiv="Content-Script-Type" content="text/javascript">'
        );
        Html::showLine(
            $indent,
            '<meta http-equiv="title" content="', $title, '">'
        );
        Html::showLine(
            $indent,
            '<meta name="author" content="',
            $this->getProperty('developer'), ', ',
            $this->getProperty('developer_url'), '">'
        );
        Html::showLine(
            $indent,
            '<meta name="description" content="',
            $this->getProperty('description'), '">'
        );
        Html::showLine(
            $indent,
            '<meta name="keywords" content="',
            $this->getProperty('keywords'), '">'
        );
        Html::showLine(
            $indent,
            '<title>', $title, '</title>'
        );
        $style =& $this->getProperty('style');
        if (empty($style)) {
            Html::showLine($indent, '<style>');
            readfile(DAWN_ROOT . 'dawn.css');
            Html::showLine($indent, '</style>');
        } else {
            Html::showLine(
                $indent,
                '<link href="', $style, '" rel="stylesheet" type="text/css">'
            );
        }
        Html::showBlock(--$indent, '</head>', '<body>');
        return $indent;
    }

    /**
     * Show the menu, but only if this page isn't a popup.
     */
    function showMenu($indent) {
        if (!$this->page->getProperty('popup')) {
            $this->page->showComponent($indent, 'menu');
        }
    }

    /**
     * Show the page
     */
    function showPage($indent) {
        $this->page->show($indent);
    }

    /**
     * Show the copyright information
     * @private
     */
    function showCopyright($indent) {
        Html::showLine($indent, '<p class="copyright">');
        $runtime = number_format(
            array_sum(explode(' ', microtime())) - DAWN_TIME_BEGIN, 4
        );
        Html::showLine(
            ++$indent,
            str_replace('%2', $runtime, $this->getProperty('generated')),
            '<br />'
        );
        if (($text =& $this->getProperty('developed_by')) != '') {
            Html::showLine($indent, $text, '<br />');
        }
        if (($text =& $this->getProperty('supported_by')) != '') {
            Html::showLine($indent, $text);
        }
        Html::showLine(--$indent, '</p>');
    }

    function showFooter($indent)
    {
        Html::showLine($indent, '</body>');
        Html::showLine(--$indent, '</html>');
    }

    // ACCESSORS

    /**
     * Generate a link for use in the footer
     * @returns string
     * @private
     */
    function getFooterLink($url, $name, $protocol = 'http://') {
        if (empty($url)) {
            return "<b>$name</b>";
        }
        if (empty($name)) {
            $name = $url;
        }
        if (strpos($url, $protocol) === false) {
            $url = $protocol . $url;
        }
        return "<a href=\"$url\" class=\"copyright\">$name</a>";
    }
}
?>
