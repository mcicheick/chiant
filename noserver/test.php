<?php
require_once '../config.php';
require_once '../requete.php';


if (ENV != 'LOCAL')
	die('Not in local mode');

//echo file_get_contents('..\private\routes');
//die ('coucou');


function print_route($req, $params, $file) {
    echo '<form action="../entree_post.php" method="post"  enctype="multipart/form-data"><br/>';
echo '<input type="hidden" name="requete" value="'.$req.'" />';
    $str = 'requete: <span id="form_'.$req.'">'.$req."</span>\n<br/>paramètres :<br/> ";
if ($file)
   echo '<input type="file" name="'.$file.'" /><br/>';
    foreach ($params as $key => $param) 
	$str .= "\t".$key. ':  <input type="text" name="'.$key.'" value="'.$param.'" />\n<br/>';
   
    $str .= '<input type="submit" value="submit" /></form><p/>';
	echo $str;
    return ;
}


echo "<html>";

$routes = routage();
foreach(routage() as $req => $route) {
    echo 'requete: <a href="#form_'.$req.'">'.$req."</a><br/>\n";
    if (isset($route['file']))
        echo 'paramètre de fichier: '.$route['file']."<br/>\n";

    echo "paramètres : ".join($route['params'],', ')."\n<p/>\n";
}

foreach(routage() as $req => $route) {
    $params = $route['params'];
    $params = array_flip($params);
    $file = $route['file'];
    $params[MAGIC_PWD_FIELD] = MAGIC_PWD;
    $params[SESSION_USERID_NAME] = 2;
    print_route($req, $params, $file);
}
