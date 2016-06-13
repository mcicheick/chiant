<?php
require_once 'config.php';
require_once '../requete.php';

if (ENV != 'LOCAL')
	die('Not in local mode');

$routes = routage();
echo "<pre>\n";
foreach(routage() as $req => $route) {
  $params_s = array();
  foreach ($route['params'] as $param)
	  $params_s[] = '$'.$param;

  if (isset($route['file']))
           array_unshift($params_s, $route['file']);

  echo "function ".$route['fun']."(".join($params_s, ', ').")  {\n   return true;\n}\n\n";
}
