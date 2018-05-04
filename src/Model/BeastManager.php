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

    public function __construct()
    {
        Beast::initStatics();
        parent::__construct(self::TABLE);
    }

    public function insert(array $values): bool
    {
        $query = $this->pdoConnection->prepare(
            'INSERT INTO ' . static::TABLE
            . ' ( name, picture, size, area, id_movie, id_planet )
                VALUES ( :picture, :size, :area :id_movie, :id_planet )'
        );

        $query->bindValue(':name', $values['name'], \PDO::PARAM_STR);
        $query->bindValue(':picture', $values['picture'], \PDO::PARAM_STR);
        $query->bindValue(':size', $values['size'], \PDO::PARAM_INT);
        $query->bindValue(':area', $values['area'], \PDO::PARAM_STR);
        $query->bindValue(':id_movie', $values['id_movie'], \PDO::PARAM_INT);
        $query->bindValue(':id_planet', $values['id_planet'], \PDO::PARAM_INT);

        return $query->execute();
    }

}
