<?php
/**
 * absolutely not created by this sh**** PHPStorm
 */

namespace Model;

use Model\PlanetManager;
use Model\MovieManager;

/**
 * Class Item
 */
class Beast
{
    private $id;

    private $name;
    private $picture;
    private $size;
    private $area;
    private $id_movie;
    private $id_planet;

    private static $planets;
    private static $movies;


    /**
     * @param array|null $planetList
     * @param array|null $movieList
     */
    public static function initStatics($planetList = null, $movieList = null)
    {
        //these ASSOCIATIVE ARRAYS use the id as index
        static::$planets = [];
        static::$movies = [];

        if (null === $planetList) {
             $planetList = (new PlanetManager())->selectAll('id');
        }
        foreach ($planetList as $object) {
                static::$planets[$object->getId()] = $object;
        }

        if (null === $movieList) {
            $movieList = (new MovieManager())->selectAll('id');
        }
        foreach ($movieList as $object) {
            static::$movies[$object->getId()] = $object;
        }
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Beast
     */
    public function setId($id): Beast
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getPicture()
    {
        return $this->picture;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function getIdMovie(): int
    {
        return $this->id_movie;
    }

    public function getMovieTitle(): string
    {
        $movie = static::$movies[$this->id_movie];
        return ($movie) ? $movie->getTitle() : 'Bad movie indice: ' . $this->id_movie;
    }

    public function getIdPlanet(): int
    {
        return $this->id_planet;
    }

    public function getPlanetName(): string
    {
        $planet = static::$planets[$this->id_planet];
        return ($planet) ? $planet->getName() : 'Bad planet indice: ' . $this->id_planet;
    }
}
