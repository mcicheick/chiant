<?php

require_once 'config.php';
require_once 'sports.php';

function routage() {
	/*
    "update_user_sport_prefs": {
        "fun": "update_user_sp_prefs",
        "params": [
            "football",
            "basketball"
        ]
    },
	 */
   $routes = getRoutesFromFile();
    $routes['update_user_sport_prefs'] =
	    array('fun' => "update_user_sp_prefs",
		    "params" => array_values(listSports()),
		    'args_as_array' => true);

    return $routes;
}

function getRoutesFromFile() {
   return (json_decode(file_get_contents( PRIVATE_DIR.'/routes',true), true));
}

function putRoutes($routes) {
  appendFile('../'.PRIVATE_DIR.'/routes', json_encode($routes, JSON_PRETTY_PRINT));
}
