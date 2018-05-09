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
#TODO: ADD "SORT BY" BUTTONS AND CODE FOR CLIENT-SIDE LIST SORTING
        $movieManager = new MovieManager();
        $movies = $movieManager->getListOf('title','title');
        try {
            return $this->twig->render('Movie/list.html.twig', ['movies' => $movies]);
        } catch (\Exception $e) {
            return \generateEmergencyPage('unexpected error', ['Exception raised while generating page', $e->getMessage()]);
        }
    }
}
