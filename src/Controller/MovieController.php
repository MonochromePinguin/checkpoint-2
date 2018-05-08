<?php

namespace Controller;

use Model\MovieManager;

/**
 * Class MovieController
 */
class MovieController extends AbstractController
{
    /*** Display item listing
     *
     * @return string
     */
    public function list()
    {
        $movieManager = new MovieManager();
        $movies = $movieManager->getListOf('title');
        try {
            return $this->twig->render('Movie/list.html.twig', ['movies' => $movies]);
        } catch (\Exception $e) {
            return \generateEmergencyPage('Erreur inattendue', ['Une exception est survenue pendant la génération de la page', $e->getMessage()]);
        }
    }
}
