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
        echo 'param�tre de fichier: '.$route['file']."\n";

    echo "param�tres : ".join($route['params'],', ')."\n\n";
}
