<?php
require_once '../requete.php';
require_once '../config.php';

if (ENV != 'LOCAL')
	die('Not in local mode');

echo '<pre>';
$routes = routage();
foreach(routage() as $req => $route) {
    echo 'requete: '.$req."\n";
    if (isset($route['file']))
        echo 'paramètre de fichier: '.$route['file']."\n";

    echo "paramètres : ".join($route['params'],', ')."\n\n";
}
