<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 07/03/18
 * Time: 20:52
 * PHP version 7
 */

namespace Model;

use App\Connection;

/**
 * Abstract class handling default manager.
 */
abstract class AbstractManager
{
    protected $pdoConnection; //variable de connexion

    protected $table;
    protected $className;


    /**
     *  Initializes Manager Abstract class.
     *
     * @param string $table Table name of current model
     */
    public function __construct(string $table)
    {
        $connexion = new Connection();
        $this->pdoConnection = $connexion->getPdoConnection();
        $this->table = $table;
        $this->className = __NAMESPACE__ . '\\' . ucfirst($table);
    }


    /**
     * Get all row from database.
     * @param string|null $orderBy give an optional "ORDER BY" parameter to the SQL query
     * @return array
     */
    public function selectAll($orderBy = null): array
    {
        return $this->pdoConnection->query('SELECT * FROM ' . $this->table . (isset($orderBy) ? ' ORDER BY `' . substr($this->pdoConnection->quote($orderBy), 1, -1) . '`' : ''), \PDO::FETCH_CLASS, $this->className)->fetchAll();
    }


    /**
     * Get one row from database by ID.
     *
     * @param int $id
     *
     * @return array
     */
    public function selectOneById(int $id)
    {
        // prepared request
        $statement = $this->pdoConnection->prepare("SELECT * FROM `$this->table` WHERE id=:id");
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->className);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }


    /**
     * generic insert method – INSERT the datas in a new record
     * @param array $values     assoc array as column=>value pair
     * @return bool
     */
    public function insert(array $values): bool
    {
        $keys = array_keys($values);

        #escaped name of columns
        $columns = array_map(
            function ($str) {
                    return '`' . substr($this->pdoConnection->quote($str), 1, -1) . '`';
            },
            $keys
        );

        #text for bindings
        $bindings = array_map(
            function ($str) {
                    return ':' . $str;
            },
            $keys
        );

        $query = $this->pdoConnection->prepare(
            'INSERT INTO ' . static::TABLE
            . ' ( ' . implode(', ', $columns) . ' ) VALUES ( '
            . implode(', ', $bindings) . ' )'
        );

        #bind each data to its placeholder
        foreach ($values as $key => $value) {
            #determine the data type for PDO
            switch (gettype($value)) {
                case 'integer':
                    $dataType = \PDO::PARAM_INT;
                    break;
                case 'string':
                    $dataType = \PDO::PARAM_STR;
                    break;
                case 'boolean':
                    $dataType = \PDO::PARAM_BOOL;
                    break;
                default:
                    throw new \Exception(
                        'Unplanned type "' . gettype($value) . '" used in generic method AbstractManager::insert()'
                    );
            }

            $query->bindValue(':' . $key, $value, $dataType);
        }

        return $query->execute();
    }


    /**
     * insert a new record and returns its corresponding id
     * @param array $values
     * @return int|false    return the id of the new record in case of success,
     *                          or false in case of failure
     */
    public function insertAndReturnId(array $values)
    {
        if (!$this->insert($values)) {
            return false;
        }

        $query = $this->pdoConnection->query('SELECT LAST_INSERT_ID()');
        $res = $query->fetch(\PDO::FETCH_NUM);

        #we get back an array of int, but we asked only for one ...
        return $res ? $res[0] : false;
    }


    /**
     * return the given property (column) of all records in an indexed array,
     *    or null if nothing was found.
     * @param $prop             name of the column to select
     * @param string|null $orderBy give an optional "ORDER BY" parameter to the SQL query
     * @return array|null    an array of string
     */
    public function getListOf(string $prop, $orderBy = null): array
    {
        $query = $this->pdoConnection->query('SELECT `' . substr($this->pdoConnection->quote($prop), 1, -1) . '` FROM ' . static::TABLE . (isset($orderBy) ? ' ORDER BY `' . substr($this->pdoConnection->quote($orderBy), 1, -1) . '`' : ''));

        return $query->fetchAll(\PDO::FETCH_COLUMN, $prop);
    }


    /**
     * Return the id(s) of the record(s) whose column $prop has value $lookedFor
     *      or null if none was found
     * @param string $prop          name of the column
     * @param string $lookedFor     value to look for
     * @return array|null
     */
    public function getIdOf(string $prop, string $lookedFor): array
    {
        $query = $this->pdoConnection->query(
            'SELECT id FROM ' . static::TABLE . ' WHERE `'
            . substr($this->pdoConnection->quote($prop), 1, -1)
            . '` = ' . $this->pdoConnection->quote($lookedFor)
        );

        return $query->fetchAll(\PDO::FETCH_COLUMN, $prop);
    }
}
