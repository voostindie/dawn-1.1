<?php
require_once(DAWN_SYSTEM  . 'Cache.php');
require_once(DAWN_SYSTEM  . 'Locale.php');
require_once(DAWN_SYSTEM  . 'DatabaseManager.php');
require_once(DAWN_SYSTEM  . 'Menu.php');
require_once(DAWN_SYSTEM  . 'Page.php');
require_once(DAWN_SYSTEM  . 'Site.php');
require_once(DAWN_SYSTEM  . 'FormFactory.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

class Application extends Object {

    // DATA MEMBERS

    var $dbm;
    var $user;
    var $menu;
    var $page;
    var $site;

    // CREATORS

    function Application() {
        $this->Object('application');
    }

    function preCreate() {
        parent::preCreate();
        $this->setProperty('menu'  , OBJECT_INVALID_VALUE);
        $this->setProperty('locale', 'english_us');
    }

    function postCreate() {
        parent::postCreate();
        $this->setProperty('menu'   , $this->parseList($this->getProperty('menu')));
        $this->setProperty('session', 'DAWN_' . substr(md5(APP_CONFIG), 0, 10));
        $this->createUserProperties();
        $this->createMenuProperties();
    }

    function createUserProperties() {
        assert('Debug::log(\'Application: creating user properties\')');
        include_once(DAWN_SYSTEM . 'Config.php');
        include_once(DAWN_SYSTEM . 'Factory.php');
        $config  =& Config::getInstance();
        $type    =  $config->getEntry('user.type', 'static');
        $factory =& new Factory('user', DAWN_USERS, APP_USERS);
        $class   =  $factory->getClass($type);
        $this->setProperty('user_class', $class);
        $this->setProperty('user_path' , $factory->getFullClassPath($type));
    }

    function createMenuProperties() {
        assert('Debug::log(\'Application: creating menu properties\')');
        include_once(DAWN_SYSTEM . 'FormFactory.php');
        $type    =  $config->getEntry('menu.type', 'menu');
        $factory =& FormFactory::getInstance();
        $class   =  $factory->getClass($type);
        $this->setProperty('menu_class', $class);
        $this->setProperty('menu_path' , $factory->getFullClassPath($type));
    }

    // MANIPULATORS

    function createMenu() {
        assert('Debug::log(\'Application: creating menu\')');
        $this->menu =& new Menu($this);
        if (!Cache::loadObject($this->menu)) {
            $this->menu->create(array('items' => $this->getProperty('menu')));
            Cache::saveObject($this->menu);
        }
        $this->menu->initialize();
        $current = isset($_GET['page'])
            ? $_GET['page']
            : $this->menu->getFirstValid();
        $this->menu->setCurrent($current);
    }

    function &createPage($name) {
        assert('Debug::log("Application: creating page \'$name\'")');
        $page =& new Page($name, $this);
        Cache::restoreObject($page, "pages.$name", true);
        if (!$this->user->hasAccess($page->getAccess()))
        {
            include_once(DAWN_EXCEPTIONS . 'AccessDeniedException.php');
            $exception =& new AccessDeniedException();
            $exception->halt();
        }
        return $page;
    }

    function createLoginPage() {
        assert('Debug::log(\'Application: creating login page\')');
        $this->page =& new Page('_login', $this);
        if (!Cache::loadObject($this->page)) {
            $this->page->create(
                array(
                    'popup'  => 'yes',
                    'layout' => array(
                        'type'       => 'centered',
                        'components' => 'login'
                    ),
                    'forms' => array(
                        'login' => array(
                            'type' => 'login'
                        )
                    )
                )
            );
            Cache::saveObject($this->page);
        }
    }

    function createNormalPage() {
        assert('Debug::log(\'Application: creating page\')');
        $this->createMenu();
        $this->page =& $this->createPage($this->menu->getCurrent());
        assert('Debug::log(\'Application: creating menu form\')');
        include_once($this->getProperty('menu_path'));
        $class =  $this->getProperty('menu_class');
        $menu  =& new $class('menu', $this->page);
        Cache::restoreObject($menu, 'menu', 'true');
        assert('Debug::log(\'Application: adding menu form to active page\')');
        $this->page->addComponent($menu);
    }

    function initialize() {
        assert('Debug::log(\'Application: initializing system\')');
        Cache::restoreObject($this, 'application');
        assert('Debug::log(\'Application: initializing database\')');
        $this->dbm =& new DatabaseManager();
        Cache::restoreObject($this->dbm, 'database');
        assert('Debug::log(\'Application: initializing user\')');
        include_once($this->getProperty('user_path'));
        session_start();
        $session = $this->getProperty('session');
        if (!isset($_SESSION[$session])) {
            $class = $this->getProperty('user_class');
            $_SESSION[$session] =& new $class();
            Cache::restoreObject($_SESSION[$session], 'user');
        }
        $this->user =& $_SESSION[$session];
        if (isset($_GET['_logout'])) {
            $this->user->logout($this);
        }
        assert('Debug::log(\'Application: initializing locale\')');
        if (($locale = $this->user->getLocale()) == '') {
            $locale = $this->getProperty('locale');
        }
        Locale::createInstance($locale);
        assert('Debug::log(\'Application: initializing page\')');
        if ($this->user->isValid()) {
            $this->createNormalPage();
        } else {
            $this->createLoginPage();
        }
        assert('Debug::log(\'Application: initializing site\')');
        $this->site =& new Site($this);
        Cache::restoreObject($this->site, 'site', true);
        assert('Debug::log(\'Application: initialisation completed\')');
    }

    /**
     * Run the application.
     */
    function run() {
        assert('Debug::setState(DEBUG_STATE_CREATE)');
        $this->initialize();
        assert('Debug::setState(DEBUG_STATE_BUILD)');
        $this->dbm->connect();
        $this->page->build();
        $this->dbm->disconnect();
        assert('Debug::setState(DEBUG_STATE_SHOW)');
        $this->site->show();
    }

    // ACCESSORS

    function &getUser() {
        return $this->user;
    }

    function &getMenu() {
        return $this->menu;
    }

    function &getPage() {
        return $this->page;
    }

    function &getDatabaseManager() {
        return $this->dbm;
    }
}
?>
