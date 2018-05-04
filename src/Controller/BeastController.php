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
      // TODO : A page which displays all details of a specific beasts
        $beastManager = new BeastManager();
        $beast = $beastManager->selectOneById($id);

        return $this->twig->render('Beast/details.html.twig', [ 'beast' => $beast ]);
    }

  /**
  * Display item creation page
  *
  * @return string
  */
    public function add()
    {
      // TODO : A creation page where your can add a new beast.

        return $this->twig->render('Beast/add.html.twig');
    }
  /**
  * Display item creation page
  *
  * @return string
  */
    public function edit()
    {
      // TODO : An edition page where your can add a new beast.
        return $this->twig->render('Beast/edit.html.twig');
    }
}
