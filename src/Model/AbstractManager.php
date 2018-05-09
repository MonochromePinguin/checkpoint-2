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
     * @param array $values assoc array as column=>value pair
     * @return bool
     * @throws \Exception
     */
    public function insert(array $values): bool
    {
        $keys = array_keys($values);

        #escaped name of columns
        $columns = array_map(function ($str) {
            return '`' . substr($this->pdoConnection->quote($str), 1, -1) . '`';
        }, $keys);

        #text for bindings
        $bindings = array_map(function ($str) {
            return ':' . $str;
        }, $keys);

        $query = $this->pdoConnection->prepare(
            'INSERT INTO ' . static::TABLE . ' ( ' . implode(', ', $columns) . ' ) VALUES ( ' . implode(', ', $bindings) . ' )'
        );

        #bind each data to its placeholder
        foreach ($values as $key => $value) {
             $query->bindValue(':' . $key, $value, self::getPdoDataType($value));
        }

        return $query->execute();
    }


    /**
     * generic update method – UPDATE the datas in a new record
     * @param int $id   id of the record to update
     * @param array $datas assoc array as column=>value pair
     * @return bool
     * @throws \Exception
     */
    public function update(int $id, array $datas): bool
    {
        $bindings = '';

        #these two indexed arrays are related – used to bind the variables
        $bindValue = [];
        $pdoDataType = [];

        #used in the generation of these previous two arrays
        $i = 0;

        #generate "column name = value binding" pairs
        # as well as the $escapedKeys[] and $pdoDataType[] indexed arrays
        foreach ($datas as $key => $value) {
            $bindValue[$i] = $value;
            $pdoDataType[$i] = self::getPdoDataType($value);

            $bindings .=  ' `' . substr($this->pdoConnection->quote($key), 1, -1) . '` = ?,';
            ++$i;
        }
        #delete the last surnumerary colon
        $bindings[ strlen($bindings) -1] = ' ';

        $query = $this->pdoConnection->prepare(
            'UPDATE ' . static::TABLE . ' SET ' . $bindings . ' WHERE id = ?'
        );

        #bind each data to its placeholder
        for ($c = 0; $c < $i; ++$c) {
            $query->bindValue($c+1, $bindValue[$c], $pdoDataType[$c]);
        }

        $query->bindValue($i+1, $id, \PDO::PARAM_INT);

        return $query->execute();
    }


    /**
     *determine the data type for use in PDO
     * @param $value
     * @return int
     * @throws \Exception
     */
    private static function getPdoDataType($value): int
    {
        switch (gettype($value)) {
            case 'integer':
                return \PDO::PARAM_INT;
            case 'string':
                return \PDO::PARAM_STR;
            case 'boolean':
                return \PDO::PARAM_BOOL;
            default:
                throw new \Exception('Unplanned type "' . gettype($value) . '" used in a generic method of AbstractManager');
        }
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


    /**
     * @param int $id   the id of the record to delete
     * @return bool     success boolean
     */
    public function delete(int $id): bool
    {
        $query = $this->pdoConnection->prepare(
            'DELETE FROM ' . static::TABLE . ' WHERE id = :id'
        );
        $query->bindValue(':id', $id, \PDO::PARAM_INT);

        return $query->execute();
    }
}
