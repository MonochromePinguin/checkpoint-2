<?php
/**
* This file hold all routes definitions.
*
* PHP version 7
*
* @author   WCS <contact@wildcodeschool.fr>
*
* @link     https://github.com/WildCodeSchool/simple-mvc
*/

$routes = [
  'Checkpoint' => [ // Controller
    ['index', '/', 'GET'], // action, url, method
    ['error', '/error/{error:\d+}', 'GET'], // action, url, method
  ],
  'Beast' => [ // Controller
    ['list', '/beasts', 'GET'], // action, url, method
    ['details', '/beasts/{id:\d+}', 'GET'], // action, url, method
    ['add', '/beasts/add', ['GET', 'POST']], // action, url, method
    ['edit', '/beasts/{id:\d+}/edit', ['GET', 'POST']], // action, url, method
  ],
  'Planet' => [ // Controller
    ['list', '/planets', 'GET'], // action, url, method
  ],
  'Movie' => [ // Controller
    ['list', '/movies', 'GET'], // action, url, method
  ],
];
