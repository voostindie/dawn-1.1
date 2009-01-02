<?php
require_once(DAWN_SYSTEM . 'Object.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

/***
 * Class DatabaseGraph stores an undirected graph of a database and allows
 * computations on it.
 * <p>
 *   Tables in a database are normally linked together in some way, by
 *   specifying references. Given these references, this class computes an
 *   undirected graph of the links between the tables.
 * </p>
 * <p>
 *   Given the graph for a database, it is possible to automatically compute
 *   the joins necessary to link two tables: the shortest path between the two
 *   tables is computed, after which the joins can easily be computed.
 * </p>
 * <p>
 *   The computation of the shortest path between two tables is done with a
 *   straightforward Breadth-First Search (BFS) algorithm.
 * </p>
 ***/
class DatabaseGraph extends Object
{
    // DATA MEMBERS

    /***
     * The databasemanager that holds access to the tables in the database
     * @type DatabaseManager
     ***/
    var $manager;

    // CREATORS

    /***
     * Create a new graph for a database
     * @param $databaseManager the DatabaseManager holding the tables
     ***/
    function DatabaseGraph(&$databaseManager)
    {
        $this->Object('graph');
        $this->manager =& $databaseManager;
    }

    function postCreate()
    {
        parent::postCreate();
        $this->createNodes();
        $this->createEdges();
    }

    /***
     * Create a list of nodes, one for each table in the database.
     * @returns void
     * @private
     ***/
    function createNodes()
    {
        $this->setProperty('nodes', array());
        $nodes =& $this->getProperty('nodes');
        $it    =& new ArrayIterator($this->manager->getTables());
        for ( ; $it->isValid(); $it->next())
        {
            $table =& $it->getCurrent();
            $nodes[$table] =& new DatabaseGraphNode($table);
        }
    }

    /***
     * Create all links between the tables in the database. If table A has
     * some field that references table B, then an edge between table A
     * and B is added.
     * @returns void
     * @private
     ***/
    function createEdges()
    {
        $tableIt =& new ArrayIterator($this->manager->getTables());
        for ( ; $tableIt->isValid(); $tableIt->next())
        {
            $table   =& $this->manager->getTable($tableIt->getCurrent());
            $fieldIt =& new ArrayIterator($table->getFields());
            for ( ; $fieldIt->isValid(); $fieldIt->next())
            {
                $field =& $table->getField($fieldIt->getCurrent());
                if ($field->isReference())
                {
                    list($target, ) = $field->getReference();
                    $this->addEdge($tableIt->getCurrent(), $target);
                }
            }
        }
    }

    /***
     * Add an edge between to nodes. $node1 is added to the adjacency list of
     * $node2, and $node2 is added to the adjacency list of $node1.
     * @returns void
     * @private
     ***/
    function addEdge($node1, $node2)
    {
        $nodes =& $this->getProperty('nodes');
        $nodes[$node1]->addAdjacentNode($node2);
        $nodes[$node2]->addAdjacentNode($node1);
    }

    /***
     * Compute a BFS tree starting at node $source. This is an implementation
     * of the standard BFS algorithm (it even uses the three colors 'white',
     * 'gray' and 'black'), with one exception: distances between nodes aren't
     * computed. The reason is that these aren't used anyway.
     * @param $source the name of the node that is the root of the tree
     * @returns void
     * @private
     ***/
    function computeBfsTree($source)
    {
        $nodes =& $this->getProperty('nodes');
        $it    =& new ArrayIterator($nodes);
        for ( ; $it->isValid(); $it->next())
        {
            $node =& $it->getCurrent();
            $node->setColor($node->getName() == $source ? 'gray' : 'white');
            $node->setParent(false);
        }
        $queue = array($source);
        while (count($queue))
        {
            $u  =& $nodes[$queue[0]];
            $it =& new ArrayIterator($u->getAdjacentNodes());
            for ( ; $it->isValid(); $it->next())
            {
                $v =& $nodes[$it->getCurrent()];
                if ($v->getColor() == 'white')
                {
                    $v->setColor('gray');
                    $v->setParent($u->getName());
                    array_push($queue, $v->getName());
                }
            }
            array_shift($queue);
            $node->setColor('black');
        }
    }

    /***
     * Find the shortest path from $table1 to $table2. This method returns an
     * array of tablenames representing that path between the two tables. If a
     * path between the two tables couldn't be found, an exception is thrown.
     * @param $table1 the name of the first table
     * @param $table2 the name of the second table
     * @returns array
     * @private
     ***/
    function findPath($table1, $table2)
    {
        $this->computeBfsTree($table1);
        $result =  array($table2);
        $nodes  =& $this->getProperty('nodes');
        $node   =& $nodes[$table2];
        while ($node->getParent() !== false)
        {
            $node =& $nodes[$node->getParent()];
            array_push($result, $node->getName());
        }
        if ($node->getName() != $table1)
        {
            include_once(DAWN_EXCEPTIONS . 'UnresolvableJoinException.php');
            $exception =& new UnresolvableJoinException($table1, $table2);
            $exception->halt();
        }
        return array_reverse($result);
    }

    /***
     * Find a reference from a table to another table. Given a table, all
     * reference-fields in it are checked to see if it points to the target. As
     * soon as such a field is found an SQL join is returned. If no join could
     * be found, false is returned. If a join is returned, the table that comes
     * lexicographically first is placed in front.
     * @param $table the Table to check the fields in
     * @param $target the name of the target table
     * @returns string
     * @private
     ***/
    function findJoin(&$table, $target)
    {
        $it =& new ArrayIterator($table->getReferences());
        for ( ; $it->isValid(); $it->next())
        {
            $field =& $table->getField($it->getCurrent());
            list($targetTable, $targetField) = $field->getReference();
            if ($target == $targetTable)
            {
                $join = array(
                    $table->getName() . '.' . $field->getName(),
                    $targetTable . '.' . $targetField
                );
                sort($join);
                return join(' = ', $join);
            }
        }
        return false;
    }

    // ACCESSORS

    /***
     * Get the direct join between two tables. The join is either from a
     * reference from a field in the first table to a field in the second, or
     * vice versa.
     * @param $table1 The name of the first table
     * @param $table2 The name of the second table
     * @returns string
     * @private
     ***/
    function getDirectJoin($table1, $table2)
    {
        $table =& $this->manager->getTable($table1);
        if (($join = $this->findJoin($table, $table2)) !== false)
        {
            return $join;
        }
        $table =& $this->manager->getTable($table2);
        return $this->findJoin($table, $table1);
    }

    /***
     * Get all joins necessary to link two tables together.
     * @param $table1 the name of the first table
     * @param $table2 the name of the second table
     * @returns array
     * @public
     ***/
    function getJoins($table1, $table2)
    {
        $result = array();
        $path   =& $this->findPath($table1, $table2);
        $it     =& new ArrayIterator($path);
        $node1  =& $it->getCurrent();
        $it->next();
        for ( ; $it->isValid(); $it->next())
        {
            $node2 =& $it->getCurrent();
            array_push($result, $this->getDirectJoin($node1, $node2));
            $node1 =& $node2;
        }
        return array($result, $path);
    }

}

/***
 * Class DatabaseGraphNode implements a single node in a DatabaseGraph.
 * <p>
 *   A node stores the name of the table it represents, and a list of names
 *   of all tables adjacent to it (referencing it, or referenced by it).
 * </p>
 * <p>
 *   To support the BFS algorithm, every node has a parent and a color as well.
 *   On every run of the algorithm these are reset.
 * </p>
 * <p>
 *   This class is an implementation detail of class DatabaseGraphNode.
 * </p>
 ***/
class DatabaseGraphNode
{
    // DATA MEMBERS

    /***
     * The name of this node
     * @type string
     ***/
    var $name;

    /***
     * The names of the adjacent nodes
     * @type array
     ***/
    var $adjacent;

    /***
     * This node's color
     * @type string
     ***/
    var $color;

    /***
     * This node's parent
     * @type string
     ***/
    var $parent;

    // CREATORS

    /***
     * Create a new node for the graph
     * @type $name the name this node should get
     ***/
    function DatabaseGraphNode($name)
    {
        $this->name     = $name;
        $this->adjacent = array();
        $this->color    = 'white';
        $this->parent   = false;
    }

    /***
     * Add a node the adjacency list of this node
     * @param $node the name of this node
     * @returns void
     ***/
    function addAdjacentNode($node)
    {
        if (!in_array($node, $this->adjacent))
        {
            array_push($this->adjacent, $node);
        }
    }

    /***
     * Set this node's color
     * @param $color the new color
     * @returns void
     ***/
    function setColor($color)
    {
        $this->color = $color;
    }

    /***
     * Set this node's parent. $parent should be the name of the new parent
     * or false if the parent should be reset
     * @param $parent the name of the new parent
     * @returns void
     ***/
    function setParent($parent)
    {
        $this->parent = $parent;
    }

    // ACCESSORS

    /***
     * Get this node's name
     * @returns string
     ***/
    function getName()
    {
        return $this->name;
    }

    /***
     * Get the list of adjacent nodes
     * @returns array
     ***/
    function &getAdjacentNodes()
    {
        return $this->adjacent;
    }

    /***
     * Return this nodes color
     * @returns string
     ***/
    function getColor()
    {
        return $this->color;
    }

    /***
     * Return this node's parent, or false if this node doesn't have one
     * @returns string
     ***/
    function getParent()
    {
        return $this->parent;
    }
}
?>
