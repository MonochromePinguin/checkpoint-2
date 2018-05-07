<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 07/03/18
 * Time: 18:20
 * PHP version 7
 */

namespace Model;

/**
 * Class BeastManager
 * @package Model
 */
class BeastManager extends AbstractManager
{
    const TABLE = 'beast';

    public function __construct($planetList = null, $movieList = null)
    {
        Beast::initStatics($planetList, $movieList);
        parent::__construct(self::TABLE);
    }

    public function insert(array $values): bool
    {
        $query = $this->pdoConnection->prepare(
            'INSERT INTO ' . static::TABLE
            . ' ( name, picture, size, area, id_movie, id_planet )
                VALUES ( :name, :picture, :size, :area, :id_movie, :id_planet )'
        );

        $query->bindValue(':name', $values['name'], \PDO::PARAM_STR);
        $query->bindValue(':picture', $values['picture'], \PDO::PARAM_STR);
        $query->bindValue(':size', $values['size'], \PDO::PARAM_INT);
        $query->bindValue(':area', $values['area'], \PDO::PARAM_STR);
        $query->bindValue(':id_movie', $values['id_movie_list'][0], \PDO::PARAM_INT);
        $query->bindValue(':id_planet', $values['id_planet'], \PDO::PARAM_INT);

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
        if (!$this->insert($values))
            return false;


        $query = $this->pdoConnection->query('SELECT LAST_INSERT_ID()');
        $res = $query->fetch(\PDO::FETCH_NUM);

        #we get back an array of int, but we asked only for one ...
        return $res ?  $res[0] : false;
    }
}
