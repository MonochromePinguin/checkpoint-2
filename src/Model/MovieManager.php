<?php

namespace Model;

/**
 * Class MovieManager
 * @package Model
 */
class MovieManager extends AbstractManager
{
    const TABLE = 'movie';

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
}
