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
                'Erreur inattendue',
                [ 'Une exception est survenue pendant la génération de la page',
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
                'Erreur inattendue',
                ['Une exception est survenue pendant la génération de la page', $e->getMessage()]
            );
        }
    }

  /**
  * Display item creation page
  *
  * @return string
  */
    public function add()
    {
        try {
            $planets = (new PlanetManager())->selectAll('name');
            $movies = (new MovieManager())->selectAll('id');
        } catch (\Exception $e) {
            return \generateEmergencyPage(
                'Erreur inattendue',
                ['Une erreur relative à la base de données est survenue', $e->getMessage()]
            );
        }

        $errors = [];   #list of error messages to show in the page
        $errFlag = false;

        $datas = [      #datas to put into the form inputs
            'name' => null,
            'picture' => null,
            'size' => null,
            'area' => null,
            'id_movie' => null,
            'id_planet' => null
        ];

        if (isset($_POST) && ( 0 !== count($_POST))) {
            #test the presence of each required field and fetch it
            foreach (static::REQUIRED_FIELDS_FOR_VALIDATION as $field) {
                if (!isset($_POST[$field])) {
                    $errors[] = 'Champs « ' . $field . ' » manquant';
                    $errFlag = true;
                } else {
                    $datas[$field] = $_POST[$field];
                }
            }

            #basic validation
            #
            if (!$errFlag) {
                if ( !is_numeric($datas['size']) || ( $datas['size'] <= 0)) {
                    $errors[] = 'le champs «taille» doit être un nombre entier positif';
                    $errFlag = true;
                }
            }

            if (!$errFlag) {
                if (!self::validateId($datas['id_planet'], $planets)) {
                    $errors[] = 'Id de planète invalide';
                    $errFlag = true;
                }
            }

            if (!$errFlag) {
                if (!self::validateId($datas['id_movie'], $movies)) {
                    $errors[] = 'Id de film invalide';
                    $errFlag = true;
                }
            }


            if (!$errFlag) {
                $beastManager = new BeastManager($planets, $movies);

                $res = false;
                try {
                    $res = $beastManager->insertAndReturnId($datas);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }

                if (false !== $res) {
                    //TODO: add a "done!" message in top of page
                    header('Location: ' . '/beasts/' . $res);
                    exit;
                } else {
                    //TODO: format that as a correct html page
                    $errors[] = "Problème d'insertion des données ...";
                }
            }
        }

        #build lists of associative arrays [ 'id', 'title' ]
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
                'Beast/add.html.twig',
                [
                    'messages' => $errors,
                    'planetList' => $planetList,
                    'movieList' => $movieList,
                    'datas' => $datas
                ]
            );
        } catch (\Exception $e) {
               return \generateEmergencyPage(
                   'Exception survenue pendant la génération de la page',
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



    /**
    * Display item edition page
    *
    * @return string
    */
    public function edit()
    {
      // TODO : An edition page where your can add a new beast.
        return $this->twig->render('Beast/edit.html.twig');
    }
}
