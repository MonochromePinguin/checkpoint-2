<?php
/**
* Created by PhpStorm.
* User: wcs
* Date: 11/10/17
* Time: 16:07
* PHP version 7
*/

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

  /**
  * Display item listing
  *
  * @return string
  */
    public function list()
    {
        $beastManager = new BeastManager();
        $beasts = $beastManager->selectAll();
        return $this->twig->render('Beast/list.html.twig', ['beasts' => $beasts]);
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

        try{
            return $this->twig->render('Beast/details.html.twig', [ 'beast' => $beast ]);
        } catch (\Exception $e) {
//TODO: format that as a correct html page
            echo $e->getMessage();
            exit;
        }
    }

  /**
  * Display item creation page
  *
  * @return string
  */
    public function add()
    {
        $beastManager = new BeastManager();

        if (isset($_POST) && ( 0 !== count($_POST))) {
 var_dump($_POST);
 echo "●●●<br>";

            $error = false;
            $datas = [];

            foreach (static::REQUIRED_FIELDS_FOR_VALIDATION as $field) {
                if ( !isset( $_POST[$field]) ) {
//TODO: format that as a correct html page
                    $error = true;
                    echo "Les données renvoyée par POST sont incomplètes&nbsp;: manque " . $field;
                } else {
                    $datas[$field] = $_POST[$field];
                }
            }

            if ($error) {
                exit;
            }

            $res = false;
            try {
                $res = $beastManager->insertAndReturnId($datas);
            } catch (\Exception $e) {
//TODO: format that as a correct html page
                echo "Erreur de création&nbsp;: " . $e->getMessage();
                exit;
            }

            if (false !== $res) {
//TODO: add a "done!" message in top of page
                header('Location: ' . '/beasts/' . $res);
                exit;
            } else {
//TODO: format that as a correct html page
                echo "Problème d'insertion";
                exit;
//TODO: should return to the creation page
            }


        } else {

            return $this->twig->render('Beast/add.html.twig');
        }
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
