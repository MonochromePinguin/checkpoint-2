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
        $beasts = $beastManager->selectAll();
        try {
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
        $beast = $beastManager->selectOneById($id);

        try {
            return $this->twig->render(
                'Beast/details.html.twig',
                [ 'beast' => $beast ]
            );
        } catch (\Exception $e) {
            return \generateEmergencyPage(
                'Exception survenue pendant la génération de la page',
                [$e->getMessage()]
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
        $planets = (new PlanetManager())->selectAll('name');
        $movies = (new MovieManager())->selectAll('id');

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
            foreach (static::REQUIRED_FIELDS_FOR_VALIDATION as $field) {
                if (!isset($_POST[$field])) {
                    $errors[] = 'Champs « ' . $field . ' » manquant';
                    $errFlag = true;
                } else {
                    $datas[$field] = $_POST[$field];
                }
            }

            if (!$errFlag) {
                if (!self::validateId($datas['id_planet'], $planets)) {
                    $errors[] = 'Id de planète invalide';
                    $errFlag = true;
                }
            }
/*Pour commit : d'abord marcher avec une seule planète ...'
 on retrouve une liste de films, mais UN SEUL peut être inséré en SQL→ modifier
 la structure : Créer une table intermédiaire ... et le signaler ... Dans le code ...
_ le select planète doit afficher «sélectionner ...» car test twig bogue avec tbl ?*/

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
