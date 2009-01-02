<?php
require_once(DAWN_SYSTEM . 'Form.php');

class LoginForm extends Form
{
    function LoginForm($name, &$page)
    {
        $this->Form($name, $page);
    }

    function preCreate()
    {
        parent::preCreate();
        include_once(DAWN_SYSTEM . 'Translator.php');
        $this->setProperty(
            'welcome',
            Translator::getText('LOGIN_WELCOME') . '<br />&nbsp'
        );
        $this->setProperty(
            'failed' ,
            Translator::getText('LOGIN_FAILED') . '<br />&nbsp'
        );
        $this->setProperty(
            'title',
            '<b>' . Translator::getText('SITE_TITLE') . '</b><br />&nbsp;'
        );
        $this->createWidgets();
        $this->createLayout();
    }

    function createLayout()
    {
        $this->setProperty(
            'layout',
            array(
                'type'       => 'table',
                'settings'   => array(
                    'title' => array(
                        'row'     => true,
                        'colspan' => 2
                    ),
                    'message' => array(
                        'row'     => true,
                        'colspan' => 2
                    ),
                    'login_label' => array(
                        'row' => true
                    ),
                    'login' => array(
                        'row' => false
                    ),
                    'password_label' => array(
                        'row' => true
                    ),
                    'password' => array(
                        'row' => false
                    ),
                    'command' => array(
                        'row' => true
                    ),
                    'toolbar' => array(
                        'row' => false,
                    )
                ),
                'components' => 'title, message, login_label, login, ' .
                    'password_label, password, command, toolbar'
            )
        );
    }

    function createWidgets()
    {
        $this->setProperty(
            'widgets',
            array(
                'title' => array(
                    'type'    => 'label',
                    'caption' => $this->getProperty('title')
                ),
                'message' => array(
                    'type'    => 'label'
                ),
                'login_label' => array(
                    'type'    => 'label',
                    'css'     => 'label',
                    'caption' => 'USER_NAME'
                ),
                'login' => array(
                    'type' => 'text',
                    'css'  => 'required',
                    'size' => 20
                ),
                'password_label' => array(
                    'type'    => 'label',
                    'css'     => 'label',
                    'caption' => 'USER_PASSWORD'
                ),
                'password' => array(
                    'type' => 'password',
                    'css'  => 'required',
                    'size' => 20
                ),
                'command' => array(
                    'type' => 'label',
                    'caption' => '<input type="hidden" name="_login" value="submit">'
                ),
                'toolbar' => array(
                    'type' => 'toolbar',
                    'command_field' => '_login',
                    'buttons' => array(
                        'submit' => array(
                            'type'    => 'form_submit_button',
                            'caption' => 'USER_LOGIN'
                        ),
                        'clear' => array(
                            'type' => 'form_reset_button'
                        )
                    )
                )
            )
        );
    }

    function buildWindow()
    {
        $message =& $this->getWidget('message');
        if (isset($_POST['_login']))
        {
            $application =& $this->getApplication();
            $user =& $application->getUser();
            $user->login($application, $_POST['login'], $_POST['password']);
            if ($user->isValid())
            {
                header('Location: ' . APP_URL);
                exit;
            }
            $message->setValue($this->getProperty('failed'));
        }
        else
        {
            $message->setValue($this->getProperty('welcome'));
        }
    }

    function getFormMethod()
    {
        return 'post';
    }
}
?>
