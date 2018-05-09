<?php

namespace Controller;

use Model\BeastManager;
use Model\PlanetManager;
use Model\MovieManager;

/**
* Class ItemController
*/
class BeastController extends AbstractController
{
    #for validating needed fields for update as for insert
    const REQUIRED_FIELDS_FOR_VALIDATION = [
        'name', 'picture', 'size', 'area', 'id_movie', 'id_planet'
    ];

    /*** Display item listing
    *
    * @return string
    */
    public function list()
    {
        $beastManager = new BeastManager();
        try {
            $beasts = $beastManager->selectAll();

            return $this->twig->render('Beast/list.html.twig', ['beasts' => $beasts]);
        } catch (\Exception $e) {
            return \generateEmergencyPage(
                'unexpected error',
                [ 'Exception raised while generating page',
                    $e->getMessage()
                ]
            );
        }
    }

    /**
    * Display item informations specified by $id
    *
    * @param int $id
    *
    * @return string
    */
    public function details(int $id)
    {
        $beastManager = new BeastManager();

        try {
            $beast = $beastManager->selectOneById($id);

            return $this->twig->render('Beast/details.html.twig', [ 'beast' => $beast ]);
        } catch (\Exception $e) {
            return \generateEmergencyPage(
                'unexpected error',
                ['Exception raised while generating page', $e->getMessage()]
            );
        }
    }


    #Just a wrapper around a call to the next method...
    public function add(): string
    {
        return $this->edit();
    }


    /**
     * Display item addition or modification page
     *
     * @param int|null $id
     * @return string
     */
    public function edit($id = null): string
    {
        try {
            $planets = (new PlanetManager())->selectAll('name');
            $movies = (new MovieManager())->selectAll('id');

            $beastManager = new BeastManager($planets, $movies);
            $beastNames = $beastManager->getListOf('name');

            if (null !== $id) {
                $beast = $beastManager->selectOneById($id);
            } else {
                $beast = null;
            }
        } catch (\Exception $e) {
            return \generateEmergencyPage(
                'unexpected error',
                ['An error relative to the database occured', $e->getMessage()]
            );
        }

        $errors = [];   #list of error messages to show in the page
        $errFlag = false;

        # initial datas to put into the form inputs
        if (null === $beast) {
            $datas = ['name' => null, 'picture' => null, 'size' => null, 'area' => null, 'id_movie' => null, 'id_planet' => null];
        } else {
            $datas = ['name' => $beast->getName(), 'picture' => $beast->getPicture(), 'size' => $beast->getSize(), 'area' => $beast->getArea(), 'id_movie' => $beast->getIdMovie(), 'id_planet' => $beast->getIdPlanet()];
        }

        if (isset($_POST) && ( 0 !== count($_POST))) {
            #test the presence of each required field and fetch it
            foreach (static::REQUIRED_FIELDS_FOR_VALIDATION as $field) {
                if (!isset($_POST[$field])) {
                    $errors[] = 'lacking field "' . $field . '"';
                    $errFlag = true;
                } else {
                    $datas[$field] = $_POST[$field];
                }
            }

            #basic field validation
            #

            #if $id is null, we're trying to create a new beast,
            # and we cannot overwrite another one;
            #if $id exist, we're editing an existing one, and we
            # allow its own name to be present in the field
            if (in_array($datas['name'], $beastNames)
                 && ( (null == $beast) || ( $datas['name'] != $beast->getName() ) )
            ) {
                $errors[] = 'The beast "' . $datas['name'] . '" already exist!';
                $errFlag = true;
            }

            if (!is_numeric($datas['size']) || ( $datas['size'] <= 0)) {
                $errors[] = 'The field "size" must be a positive interger number';
                $errFlag = true;
            }

            if (!self::validateId($datas['id_planet'], $planets)) {
                $errors[] = 'invalid planet reference';
                $errFlag = true;
            }

            if (!self::validateId($datas['id_movie'], $movies)) {
                $errors[] = 'invalid movie reference';
                $errFlag = true;
            }


            if (!$errFlag) {
                if (null === $beast) {
                    #Creation of a new beast
                    $id = false;

                    try {
                        $id = $beastManager->insertAndReturnId($datas);
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                    }

                    if (!$id) {
                        $errors[] = "Data insertion problem...";
                    }
                } else {
                    #Edition of an existing beast
                    try {
                        $res = $beastManager->update($id, $datas);
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                    }

                    if (!$res) {
                        $errors[] = 'Data updating problem ...';
                        $id = false;
                    }
                }

                if (false !== $id) {
                    //TODO: add a "done!" message in top of page
                    header('Location: ' . '/beasts/' . $id);
                    exit;
                }
                # else we stay on the page, with some more error messages
            }
        }

        #build lists of associative arrays [ 'id', 'title' ] from
        # the in-memory lists,
        # for creating selects options in twig
        $planetList = [];
        foreach ($planets as $planet) {
            $planetList[] = [ 'id' => $planet->getId(), 'label' => $planet->getName() ];
        }

        $movieList = [];
        foreach ($movies as $movie) {
            $movieList[] = [ 'id' => $movie->getId(), 'label' => $movie->getTitle() ];
        }

        try {
            return $this->twig->render(
                ($id === null) ?
                    'Beast/add.html.twig' : 'Beast/edit.html.twig',
                [
                    'messages' => $errors,
                    'planetList' => $planetList,
                    'movieList' => $movieList,
                    'editedBeastName' => json_encode(
                            ($beast === null) ? null : $beast->getName()
                    ),
                    'beastList' => json_encode($beastNames),
                    'datas' => $datas
                ]
            );
        } catch (\Exception $e) {
               return \generateEmergencyPage(
                   'Exception raised while generating page',
                   [$e->getMessage()]
               );
        }
    }


    /**
     * verify that an id belongs to an object in the given list
     *   – use object->getId()
     * @param int $id
     * @param array $objects
     */
    private static function validateId(int $id, array $objects)
    {
        foreach ($objects as $object) {
            if ($id === $object->getId()) {
                return true;
            }
        }

        return false;
    }
}
