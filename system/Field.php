<?
class Field extends Object
{
    var $table;

    function Field($name, &$table)
    {
        $this->Object($name);
        $this->table =& $table;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('type'       , '');
        $this->setProperty('access'     , '');
        $this->setProperty('null'       , false);
        $this->setProperty('references' , '');
        $this->setProperty('caption'    , '');
        $this->setProperty('default'    , '');
    }

    function postCreate()
    {
        $this->deleteProperty('type');
        parent::postCreate();
        $reference = $this->parseList($this->getProperty('references'), '.');
        switch (count($reference))
        {
            case 0:
                $this->deleteProperty('references');
                break;
            case 1:
                $this->checkReference(
                    $this->table->getName(),
                    $reference[0]
                );
                $this->setProperty(
                    'references',
                    array($this->table->getName(), $reference[0])
                );
                break;
            case 2:
                $this->checkReference(
                    $reference[0],
                    $reference[1]
                );
                $this->setProperty(
                    'references',
                    array($reference[0], $reference[1])
                );
                break;
            default:
                include_once(DAWN_EXCEPTIONS . 'TableReferenceException.php');
                $exception = new TableReferenceException(
                    $this->table->getName(),
                    $this->getName(),
                    $this->getProperty('references')
                );
                $exception->halt();
        }
        include_once(DAWN_SYSTEM . 'Translator.php');
        if (($caption = $this->getProperty('caption')) != '')
        {
            $this->setProperty(
                'caption',
                Translator::resolveText($caption)
            );
        }
        else
        {
            $table = strtoupper($this->table->getName());
            $this->setProperty(
                'caption',
                Translator::resolveText(
                    $this->getName(), $table . '_FIELD', $table
                )
            );
        }
    }

    function checkReference($table, $field)
    {
        include_once(DAWN_SYSTEM . 'Config.php');
        $config =& Config::getInstance();
        $target = $config->getEntry(
            'database.tables.' . $table . '.fields.' . $field,
            OBJECT_INVALID_VALUE
        );
        if ($target == OBJECT_INVALID_VALUE)
        {
            include_once(DAWN_EXCEPTIONS . 'TableReferenceException.php');
            $exception = new TableReferenceException(
                $this->table->getName(),
                $this->getName(),
                $table . '.' . $field
            );
            $exception->halt();
        }
    }

    function getName()
    {
        return $this->getObjectId();
    }

    function getCaption()
    {
        return $this->getProperty('caption');
    }

    function getDefault()
    {
        return $this->getProperty('default');
    }

    function &getTable()
    {
        return $this->table;
    }

    /***
     * Check if this field type is ordered; that is: if it can be used in an
     * ORDER BY-clause.
     * @returns bool
     ***/
    function isOrdered()
    {
        return true;
    }

    /***
     * Check if this field references another field
     * @returns bool
     ***/
    function isReference()
    {
        return $this->hasProperty('references');
    }

    /***
     * Get information on the field this field refers to. The result is returned
     * as an array with two keys: 'table' and 'field'.
     * @returns array
     ****/
    function getReference()
    {
        return $this->getProperty('references');
    }

    /***
     * Check a value for nullness; this can be overridden by subclasses
     * @returns bool
     ***/
    function isNull($value)
    {
        return ($value === '' || is_null($value));
    }

    /***
     * Check whether NULL is a valid value for this field
     * @returns bool
     ***/
    function isNullValid()
    {
        return $this->getProperty('null');
    }

    /***
     * Return the selection of the field, given its name. By default the name
     * itself is returned, but subclasses can override this method to, for
     * example, wrap DBMS-specific functions around it.
     * @returns string
     ***/
    function getSelect($name)
    {
        return $name;
    }

    /***
     * Format a value to its equivalent SQL value.
     * @returns bool
     ***/
    function getSql($value)
    {
        return $value;
    }
}
?>
