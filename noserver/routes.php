<?php
require_once '../requete.php';

echo '<pre>';
$routes = routage();
foreach(routage() as $req => $route) {
    echo 'requete: '.$req."\n";
    if (isset($route['file']))
        echo 'param�tre de fichier: '.$route['file']."\n";

    echo "param�tres : ".join($route['params'],', ')."\n\n";
}
