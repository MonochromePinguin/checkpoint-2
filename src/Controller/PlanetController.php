<?php

namespace Controller;

use Model\PlanetManager;

/**
 * Class PlanetController
 */
class PlanetController extends AbstractController
{
    /**
     * this page is dedicated to AJAX POST queries for planet creation
     *
     * needed POST parameters: planetToCreate → tne name of the new planet
     *
     * returned datas: a JSON array containing key/values pairs:
     *      • 'status' → an error code, similar to that of HTTP :
     *          201 → creation ok – in that case, we return a new planet list
     *          304 → already exist, no creation
     *          500 → internal server error ...
     *      • 'id' → the id of the new planet. Valid if statusCode 200
     *      • 'newPlanetList' → an indexed array of ['id', 'name'] assoc
     *                           arrays, one per planet. Valif if status 200
     *      • 'message'   → an explicative error message. Valid if status != 200
     */
    public function ajaxAddNew()
    {
        if (( 0 === count($_POST))
            || empty($_POST['planetToCreate'])
        ) {
            header('Content-Type: application/json');
            echo json_encode([ 'status' => 404, 'message' => 'ill-formed request' ]);
            exit;
        }

        $name = $_POST['planetToCreate'];
        $response = [];

        $planetManager = new PlanetManager();

        #it seems there is no uniqueness constraint in the furnished DB creation script,
        #so we handle it in the code
        try {
            $id = $planetManager->getIdOf('name', $name);

            if (isset($id[0])) {
                #planet already there: returns its id
                $response['id'] = $id[0];
                $response['status'] = 304;
                $response['message'] = 'This planet already exist';

                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            $id = $planetManager->insertAndReturnId([ 'name' => $name ]);
            $res = array_map(
                function ($planet) {
                    return [
                       'id' => $planet->getId(),
                       'name' => $planet->getName()
                    ];
                },
                $planetManager->selectAll('name')
            );

            if ($id && ($res != null)) {
                #planet created successfuly
                $response['id'] = $id;
                $response['status'] = 201;

                #we need to send back a new planet list
                $response['newPlanetList'] = $res;
            } else {
                #planet creation not successful: return an error code
                $response['message'] = 'internal server error while creating planet';
                $response['status'] = 500;
            }
        } catch (\Exception $e) {
            $response['status'] = 500;
            $response['message'] = 'Internal server error, related to the database: ' . $e->getMessage();
        }

        #send back the result:
        #WE NEED TO TELL THE CLIENT WE'LL SEND BACK JSON !
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }


    /**
     * Display item listing
     *
     * @return string
     */
    public function list()
    {
#TODO: ADD "SORT BY" BUTTONS AND CODE FOR CLIENT-SIDE LIST SORTING
        $planetManager = new PlanetManager();
        try {
            $planets = $planetManager->getListOf('name', 'name');

            return $this->twig->render('Planet/list.html.twig', ['planets' => $planets]);
        } catch (\Exception $e) {
            return \generateEmergencyPage(
                'inexpected error',
                ['Exception raised while generating page', $e->getMessage()]
            );
        }
    }
}
