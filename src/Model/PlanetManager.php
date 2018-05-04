<?php

namespace Model;

/**
 * Class PlanetManager
 * @package Model
 */
class PlanetManager extends AbstractManager
{
    const TABLE = 'planet';

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
}
