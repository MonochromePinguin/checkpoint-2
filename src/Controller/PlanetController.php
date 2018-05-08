<?php

namespace Controller;

use Model\PlanetManager;

/**
 * Class PlanetController
 */
class PlanetController extends AbstractController
{
    /*** Display item listing
     *
     * @return string
     */
    public function list()
    {
        $planetManager = new PlanetManager();
        $planets = $planetManager->getListOf('name');
        try {
            return $this->twig->render('Planet/list.html.twig', ['planets' => $planets]);
        } catch (\Exception $e) {
            return \generateEmergencyPage('Erreur inattendue', ['Une exception est survenue pendant la génération de la page', $e->getMessage()]);
        }
    }
}
