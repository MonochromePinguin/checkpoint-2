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
     * Get a list of the given property (column) of all entries.
     * @param $prop             name of the column to select
     * @param string|null $orderBy give an optional "ORDER BY" parameter to the SQL query
     * @return array    an array of string
     */
    public function getListOf(string $prop, $orderBy = null): array
    {
        $query = $this->pdoConnection->query(
            'SELECT `' . substr($this->pdoConnection->quote($prop), 1, -1)
            . '` FROM ' . static::TABLE
            . (isset($orderBy) ? ' ORDER BY `' . substr($this->pdoConnection->quote($orderBy), 1, -1) . '`'
                : '' )
        );

        return $query->fetchAll(\PDO::FETCH_COLUMN, $prop);
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
}
