<?php
require_once(DAWN_SYSTEM . 'User.php');

class FileUser extends User
{
    var $id;
    var $login;
    var $description;
    var $valid;

    function FileUser()
    {
        $this->User();
        $this->id          = 0;
        $this->login       = '';
        $this->description = '';
        $this->valid       = false;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('file', OBJECT_INVALID_VALUE);
    }

    function postCreate()
    {
        parent::postCreate();
        if (!file_exists($this->getProperty('file')))
        {
            // TODO: throw an error
            exit("File doesn't exist");
        }
    }

    function login(&$application, $login, $password)
    {
		if ($login == '' || $password == '') {
			return false;
		}
		include_once(ECLIPSE_ROOT . 'DataFile.php');
		include_once(ECLIPSE_ROOT . 'DataFileReader.php');
		include_once(ECLIPSE_ROOT . 'DataFileIterator.php');
		$file =& new DataFile(
            $this->getProperty('file'), new DataFileReader()
        );
        $this->valid = false;
		for ($it =& new DataFileIterator($file); $it->isValid(); $it->next())
        {
			$record = &$it->getCurrent();
			if ($record['login'] == $login && $record['password'] == $password)
            {
				$this->id          = $record['id'];
				$this->login       = $login;
                $this->description = $record['description'];
				$this->valid       = true;
				return;
			}
		}
    }

    function logout(&$application)
    {
        $this->valid = false;
    }

    function getId()
    {
        return $this->id;
    }

    function getLogin()
    {
        return $this->login;
    }

    function getDescription()
    {
        return $this->description;
    }

    function isValid()
    {
        return $this->valid;
    }
}
?>
