<?php
require_once 'config.php';
require_once '../requete.php';

if (ENV != 'LOCAL')
	die('Not in local mode');


$routes = routage();


echo "<h1>Liste de fonctions automatiquement générés</h1>"
echo "<pre>\n";



// Génère les fonctions
foreach(routage() as $req => $route) {
  $params_s = array();
  foreach ($route['params'] as $param)
	  $params_s[] = '$'.$param;

  if (isset($route['file']))
           array_unshift($params_s, '$'.$route['file']);

  echo "function ".$route['fun']."(".join($params_s, ', ').")  {\n   return true;\n}\n\n";
}

// Génère les requetes
foreach(routage() as $req => $route) {
  $params_s = array();
  foreach ($route['params'] as $param)
	  $params_s[] = '$'.$param;

  if (isset($route['file']))
           array_unshift($params_s, $route['file']);

  echo $route['fun']."(".join($params_s, ', ').");\n";
}
echo "\n\n";
foreach(routage() as $req => $route) {
  $params_s = array();
  foreach ($route['params'] as $param)
	  $params_s[] = "'".$param. "' => ?";

  //if (isset($route['file']))
           //array_unshift($params_s, '$'.$route['file'].' => ?');

  echo "dispatchParams('".$req."',  array (".join($params_s, ', ')."));\n";
}
