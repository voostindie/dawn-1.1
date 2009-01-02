<?php
require_once(DAWN_SYSTEM . 'Object.php');

/***
 * Class Query provides a means of dynamically creating complex queries on the
 * database.
 ***/
class Query extends Object
{
    // DATA MEMBERS

    /***
     * The main table for this query
     * @type Table
     ***/
    var $table;

    /***
     * Whether this query has been prepared
     * @type bool
     ***/
    var $prepared;

    /***
     * The list of fields in this query
     * @type array
     ***/
    var $fields;

    /***
     * The list of tables in this query
     * @type array
     ***/
    var $tables;

    /***
     * The list of criteria in this query
     * @type array
     ***/
    var $criteria;

    /***
     * The list of joins in this query
     * @type array
     ***/
    var $joins;

    /***
     * The list of orderings for this query
     * @type array
     ***/
    var $order;

    // CREATORS

    /***
     * Construct a new Query
     * @param $table the Table this query is for
     ***/
    function Query(&$table)
    {
        $this->Object($table->getName() . '_query');
        $this->table    =& $table;
        $this->prepared =  true;
        $this->fields   =  array();
        $this->tables   =  array();
        $this->criteria =  array();
        $this->joins    =  array();
        $this->order    =  array();
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('fields'  , '');
        $this->setProperty('criteria', array());
        $this->setProperty('order'   , '');
    }

    function postCreate()
    {
        $this->setProperty('tables', array($this->table->getName()));
        $this->setProperty('joins', array());
        $this->createFields();
        $this->createCriteria();
        $this->createOrder();
        parent::postCreate();
    }

    /***
     * Create the fields in this query from the argument specified on creation
     * @returns void
     * @private
     ***/
    function createFields()
    {
        $fields =  array();
        $tables =& $this->getProperty('tables');
        $joins  =& $this->getProperty('joins');
        $it     =& new ArrayIterator(
            $this->parseList($this->getProperty('fields'))
        );
        $this->setProperty('fields', array());
        for ( ; $it->isValid(); $it->next())
        {
            list($table, $field) = $this->getFullFieldNames($it->getCurrent());
            $this->addField($table, $field);
        }
    }

    /***
     * @returns void
     * @private
     ***/
    function addField($tableName, $fieldName, $requireOrdered = false)
    {
        $manager =& $this->table->getDatabaseManager();
        $fields  =& $this->getProperty('fields');
        $tables  =& $this->getProperty('tables');
        $joins   =& $this->getProperty('joins');
        $table   =& $manager->getTable($tableName);
        if (!$table->hasField($fieldName))
        {
            // TODO: throw an exception
            exit('Field doesnot exist');
        }
        $field =& $table->getField($fieldName);
        if ($requireOrdered && !$field->isOrdered())
        {
            // TODO: throw an exception
            exit('Field isnot ordered');
        }
        $selection = $field->getSelect($tableName . '.' . $fieldName) . ' AS ' .
            $tableName . '_' . $fieldName;
        if (!in_array($selection, $fields))
        {
            array_push($fields, $selection);
        }
        if (!in_array($tableName, $tables))
        {
            array_push($tables, $tableName);
            list($joinFields, $joinTables) = $this->resolveJoin($tableName);
            $joins  = array_unique(array_merge($joins, $joinFields));
            $tables = array_unique(array_merge($tables, $joinTables));
        }
    }

    /***
     * @returns void
     * @private
     ***/
    function createCriteria()
    {
        include_once(DAWN_SYSTEM . 'QueryClause.php');
        $config   = $this->getProperty('criteria');
        $criteria = array();
        for ($it =& new ArrayIterator($config) ; $it->isValid(); $it->next())
        {
            $settings =& $it->getCurrent();
            if (!is_array($settings))
            {
                // TODO: Throw an exception
                exit('Criteria has no settings');
            }
            $clause =& new QueryClause($this);
            $clause->create($settings);
            $criteria[$clause->getName()] = $clause->getConfig();
        }
        $this->setProperty('criteria', $criteria);
    }

    /***
     * @returns void
     * @private
     ***/
    function createOrder()
    {
        $order  = array();
        $fields =& $this->getProperty('fields');
        $it    =& new ArrayIterator(
            $this->parseList($this->getProperty('order'))
        );
        for ( ; $it->isValid(); $it->next())
        {
            $pair = $this->parseList($it->getCurrent(), ' ');
            if (count($pair) == 1)
            {
                $pair[1] = 'asc';
            }
            $direction = strtoupper($pair[1]);
            if ($direction != 'ASC' && $direction != 'DESC')
            {
                $direction = 'ASC';
            }
            list($table, $field) = $this->getFullFieldNames($pair[0]);
            $this->addField($table, $field, true);
            array_push($order, $table . '.' . $field . ' ' . $direction);

        }
        $this->setProperty('order', $order);
    }

    // MANIPULATORS

    /***
     * Find the shortest path from the table central to this query to the table
     * specified in $name. The result of this method is an array of joins. If
     * no path between the two tables exist, an exception will be thrown,
     * halting the application
     * @param $table
     * @returns array
     ***/
    function resolveJoin($table)
    {
        $manager =& $this->table->getDatabaseManager();
        $graph   =& $manager->getGraph();
        return $graph->getJoins($this->table->getName(), $table);
    }

    /***
     * @returns void
     ***/
    function prepare()
    {
        $this->fields = $this->getProperty('fields');
        $this->tables = $this->getProperty('tables');
        $this->joins  = $this->getProperty('joins');
        $this->order  = $this->getProperty('order');
    }

    /***
     * @returns void
     ***/
    function setClause($name)
    {
        $criteria =& $this->getProperty('criteria');
        if (!isset($criteria[$name]))
        {
            // TODO: throw an exception
            exit('Trying to set unknown clause');
        }
        $clause =& $criteria[$name];
        $size   =  count($clause['fields']);
        if (func_num_args() - 1 != $size)
        {
            // TODO: throw an exception
            exit('Argument count mismatch');
        }
        $sql = $clause['template'];
        for ($i = 0; $i < $size; $i++)
        {
            $field =& $this->getField($clause['fields'][$i]);
            $value =  func_get_arg($i + 1);
            $sql   =  str_replace(
                '%' . ($i + 1),
                $clause['fields'][$i] . ' '  . $clause['operators'][$i] .
                    ' ' . $field->getSql($value),
                $sql
            );
        }
        array_push($this->criteria, $sql);
        $it =& new ArrayIterator($clause['tables']);
        for ( ; $it->isValid(); $it->next())
        {
            $table =& $it->getCurrent();
            if (!in_array($table, $this->tables))
            {
                array_push($this->tables, $table);
            }
        }
        $it =& new ArrayIterator($clause['joins']);
        for ( ; $it->isValid(); $it->next())
        {
            $join =& $it->getCurrent();
            if (!in_array($join, $this->joins))
            {
                array_push($this->joins, $join);
            }
        }
    }

    /***
     * @returns void
     ***/
    function setClauseAll($name, $value)
    {
        $criteria =& $this->getProperty('criteria');
        if (!isset($criteria[$name]))
        {
            // TODO: throw an exception
            exit('Trying to set unknown clause');
        }
        $clause     =& $criteria[$name];
        $size       =  count($clause['fields']);
        $parameters = array_fill(1, $size, $value);
        array_unshift($parameters, $name);
        call_user_func_array(
            array(&$this, 'setClause'), $parameters
        );
    }

    /***
     * @returns void
     ***/
    function finish()
    {
        $this->prepared = true;
    }

    function execute()
    {
        $database =& $this->table->getDatabase();
        return $database->query($this->getSql());
    }

    /***
     * Take two queries and compute their union. Before two queries can be
     * united, they both must be prepared (by calling finish()). The resulting
     * query will be a light Query object storing only the clauses necessary to
     * create the SQL. It cannot be saved in the cache, but this is not a
     * problem, as the whole point of this method is to create a dynamic query.
     * @param $query1 the first Query object
     * @param $query2 the second Query object
     * @param $orderOnFirst whether to use the ordering of the first query
     * (true) or of the second ($false);
     * @returns Query
     * @static
     ***/
    function &combine(&$query1, &$query2, $tables = array(), $joins = array())
    {
        if (!$query1->prepared || !$query2->prepared)
        {
            // TODO: throw an exception
            exit('Queries must be prepared before combine can be called');
        }
        $query           =& new Query($query1->table);
        $query->fields   = array_unique(
            array_merge($query1->fields, $query2->fields)
        );
        $query->tables   = array_unique(
            array_merge($query1->tables, $query2->tables)
        );
        $query->criteria = array_unique(
            array_merge($query1->criteria, $query2->criteria)
        );
        $query->joins    = array_unique(
            array_merge($query1->joins, $query2->joins)
        );
        $query->order    = $query1->order;
        if ($query1->table->getName() != $query2->table->getName())
        {
            $query->tables = array_unique(array_merge($query->tables, $tables));
            $query->joins = array_unique(array_merge($query->joins, $joins));
        }
        $query->prepared = true;
        return $query;
    }

    // ACCESSORS

    /***
     * Get the SQL code for this query. The SQL can only be generated if the
     * query has been properly prepared, and when the query has fields and
     * tables. A partial query with clauses only cannot generate a valid SQL
     * statement.
     * @returns string
     ***/
    function getSql()
    {
        if (!$this->prepared)
        {
            // TODO: throw an exception
            exit("Query wasn't prepared");
        }
        if (!count($this->fields))
        {
            // TODO: throw an exception
            exit('No SELECT in query!');
        }
        if (!count($this->tables))
        {
            // TODO: throw an exception
            exit('No FROM in query!');
        }
        $sql  = 'SELECT ' . join(', ', $this->fields);
        $sql .= ' FROM ' . join(', ', $this->tables);
        $criteria = array_merge($this->criteria, $this->joins);
        if (count($criteria))
        {
            $sql .= ' WHERE ' . join(' AND ', $criteria);
        }
        if (count($this->order))
        {
            $sql .= ' ORDER BY ' . join(', ', $this->order);
        }
        return $sql;
    }

    /***
     * @returns array
     ***/
    function getFullFieldNames($name)
    {
        $pair = explode('.', $name, 2);
        if (count($pair) == 1)
        {
            $pair[1] = $pair[0];
            $pair[0] = $this->table->getName();
        }
        $manager =& $this->table->getDatabaseManager();
        $table   =& $manager->getTable($pair[0]);
        if (!$table->hasField($pair[1]))
        {
            // TODO: throw an exception
            exit('Illegal table field: ' . $pair[0] . '.' . $pair[1]);
        }
        return $pair;
    }

    /***
     * Given a fully qualified fieldname, return a reference to its
     * corresponding object in its table.
     * @param $name the fully qualified fieldname (table.field)
     * @returns Field
     ***/
    function &getField($name)
    {
        list($table, $field) = explode('.', $name);
        if ($table == $this->table->getName())
        {
            return $this->table->getField($field);
        }
        $manager =& $this->table->getDatabaseManager();
        $table   =& $manager->getTable($table);
        return $table->getField($field);
    }

    /***
     * Get the table central to this query
     * @returns Table
     ***/
    function &getTable()
    {
        return $this->table;
    }
}
?>
