<?php
require_once(DAWN_SYSTEM . 'Object.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

/***
 * Class QueryClause generates a single clause for use in class Query.
 * <p>
 *   This class is only used in class Query inside its create-method. As soon
 *   as all clauses have been created, they are transformed to simple array
 *   structures and stored in the cache.
 * </p>
 * <p>
 *   As a single clause in a query can be a complex thing, this class was
 *   created to keep the complexity of creating one out of class Query. Also,
 * </p>
 * <p>
 *  A single class supports the following arguments:
 * </p>
 * <ul>
 *   <li>
 *     <b>name</b> (required): the name of the clause, necessary for referring
 *     to it when setting the clause's value.
 *   </li>
 *   <li>
 *     <b>fields</b> (required): a comma-separated list of fields. For most
 *     clauses, only one field is enough.
 *   </li>
 *   <li>
 *     <b>operators</b>: the operators to use, one for each field in the list
 *     of fields. If no operators are specified '=' is assumed.
 *   </li>
 *   <li>
 *     <b>joins</b>: the joins necessary to link the fields in the clause to
 *     the table in the query. Normally, these shouldn't have to be specified
 *     at all, for they will be computed automatically.
 *   </li>
 *   <li>
 *     <b>template</b>: the SQL template for the clause; it defines how the
 *     fields in the clause are combined together. By default this is '%1' for
 *     one field, and '(%1 OR %2 OR ... %n)' for n fields.
 *   </li>
 * </ul>
 ***/
class QueryClause extends Object
{
    // DATA MEMBERS

    /***
     * The query this clause is for
     * @type Query
     ***/
    var $query;

    /***
     * The tables in this clause
     * @type array
     ***/
    var $tables;

    /***
     * The number of fields in this clause
     * @type int
     ***/
    var $fieldCount;

    // CREATORS

    /***
     * Create a new clause for the specified Query
     * @param $query the Query to create this clause for
     ***/
    function QueryClause(&$query)
    {
        $this->Object($query->getObjectId() . '_clause');
        $this->query      =& $query;
        $table            =& $query->getTable();
        $this->tables     =  array($table->getName());
        $this->fieldCount =  0;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('name'     , OBJECT_INVALID_VALUE);
        $this->setProperty('fields'   , OBJECT_INVALID_VALUE);
        $this->setProperty('operators', '');
        $this->setProperty('joins'    , '');
        $this->setProperty('template' , '');
    }

    function postCreate()
    {
        parent::postCreate();
        $this->createFields();
        $this->createOperators();
        $this->createJoins();
        $this->createTemplate();
    }

    /***
     * Create fields for this clause. For every field in the list of fields
     * specified on creation, get the table and the field and add them to
     * the clause.
     * @returns void
     * @private
     ***/
    function createFields()
    {
        $fields = array();
        $it     =& new ArrayIterator(
            $this->parseList($this->getProperty('fields'))
        );
        for ( ; $it->isValid(); $it->next())
        {
            $names = $this->query->getFullFieldNames($it->getCurrent());
            if (!in_array($names[0], $this->tables))
            {
                array_push($this->tables, $names[0]);
            }
            $name = join('.', $names);
            if (!in_array($name, $fields))
            {
                array_push($fields, $name);
            }
        }
        $this->setProperty('fields', $fields);
        $this->fieldCount = count($fields);
    }

    /***
     * Create the operators for this clause. All fields that do not have
     * operators are set the '='.
     * @returns void
     * @private
     ***/
    function createOperators()
    {
        $list = $this->parseList($this->getProperty('operators'));
        $this->setProperty(
            'operators',
            array_pad($list, $this->fieldCount, '=')
        );
    }

    /***
     * Create the joins for this clause. If the joins were specified on
     * construction, they are checked to make sure they exist. If no joins were
     * specified at all, the necessary joins will be computed automatically.
     * @returns void
     * @private
     ***/
    function createJoins()
    {
        $joins = $this->parseList($this->getProperty('joins'));
        if (count($joins))
        {
            $this->setProperty('joins', $this->checkJoins($joins));
        }
        else
        {
            $this->setProperty('joins', $this->resolveJoins());
        }
    }

    // MANIPULATORS

    /***
     * For every join in the list of joins specified on construction, check
     * that it is valid. This method first checks that the join is of a correct
     * syntax ('[table.]field = [table.]field'), and then checks each field in
     * the join in turn. On syntax error, an exception as thrown, and if a
     * field is invalid this happens as well. If all is well, the join is added
     * to the clause, as well as the necessary tables.
     * @returns array
     * @private
     ***/
    function checkJoins(&$list)
    {
        $result = array();
        for ($it =& new ArrayIterator($list); $it->isValid(); $it->next())
        {
            $join = $this->parseList($it->getCurrent, '=');
            if (count($join) != 2)
            {
                include_once(DAWN_EXCEPTIONS . 'IllegalJoinException.php');
                $exception =& new IllegalJoinException(
                    $this->getObjectId(),
                    $it->getCurrent()
                );
                $exception->halt();
            }
            list($table1, $field1) = $this->query->getFullFieldNames($join[0]);
            if (!in_array($table1, $this->tables))
            {
                array_push($this->tables, $table1);
            }
            list($table2, $field2) = $this->query->getFullFieldNames($join[1]);
            if (!in_array($table2, $this->tables))
            {
                array_push($this->tables, $table2);
            }
            $join = array(
                $table1 . '.' . $field1,
                $table2 . '.' . $field2
            );
            sort($join);
            array_push($result, join(' = ', $join));
        }
        return $result;
    }

    /***
     * Automatically resolve all joins between the tables in this clause and
     * the table in the query. If a join isn't yet in the list of joins for
     * this class, it is added.
     * @returns array
     * @private
     ***/
    function resolveJoins()
    {
        $result = array();
        $size   = count($this->tables);
        $tables = array();
        for ($i = 1; $i < $size; $i++)
        {
            list($joins, $joinTables) = $this->query->resolveJoin(
                $this->tables[$i]
            );
            $it =& new ArrayIterator($joins);
            for (; $it->isValid(); $it->next())
            {
                if (!in_array($it->getCurrent(), $result))
                {
                    array_push($result, $it->getCurrent());
                }
            }
            $tables = array_unique(array_merge($tables, $joinTables));
        }
        $this->tables = array_unique(array_merge($this->tables, $tables));
        return $result;
    }

    /***
     * If no template was given on construction, create it. If the clause
     * contains just one field, this is '%1'. For n fields it is
     * '(%1 OR ... OR %i OR ... OR %n)'.
     * @private
     ***/
    function createTemplate()
    {
        if ($this->getProperty('template') != '')
        {
            return;
        }
        if ($this->fieldCount == 1)
        {

            $this->setProperty('template', '%1');
            return;
        }
        $list = array();
        for ($i = 0; $i < $this->fieldCount; $i++)
        {
            $list[$i] = '%' . ($i + 1);
        }
        $this->setProperty('template', '(' . join(' OR ', $list) . ')');
    }

    // ACCESSORS

    /***
     * Get the name of this clause
     * @returns string
     ***/
    function getName()
    {
        return $this->getProperty('name');
    }

    /***
     * Get this clause's config
     * @returns array
     ***/
    function getConfig()
    {
        return array(
            'tables'    => $this->tables,
            'fields'    => $this->getProperty('fields'),
            'operators' => $this->getProperty('operators'),
            'joins'     => $this->getProperty('joins'),
            'template'  => $this->getProperty('template')
        );
    }
}
?>
