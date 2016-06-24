<?php
require_once '../config.php';
require_once '../requete.php';


if (ENV != 'LOCAL')
	die('Not in local mode');

//echo file_get_contents('..\private\routes');
//die ('coucou');


?>

<script>
// js function to reset a form
function resetForm(form) {
    // clearing inputs
    var inputs = form.getElementsByTagName('input');
    for (var i = 0; i<inputs.length; i++) {
        switch (inputs[i].type) {
            // case 'hidden':
            case 'text':
                inputs[i].value = '';
                break;
            case 'radio':
            case 'checkbox':
                inputs[i].checked = false;   
        }
    }

    // clearing selects
    var selects = form.getElementsByTagName('select');
    for (var i = 0; i<selects.length; i++)
        selects[i].selectedIndex = 0;

    // clearing textarea
    var text= form.getElementsByTagName('textarea');
    for (var i = 0; i<text.length; i++)
        text[i].innerHTML= '';

    return false;
}
</script>
<?php

function print_route($req, $params, $file) {
    echo '<form action="../entree_post.php" method="post"  enctype="multipart/form-data"><br/>';
echo '<input type="hidden" name="requete" value="'.$req.'" />';
    $str = 'requete: <span id="form_'.$req.'">'.$req."</span>\n<br/>paramètres :<br/> <p/>";
if ($file)
   $str .= '<input type="file" name="'.$file.'" /><br/>';
    foreach ($params as $key => $param) 
	$str .= "\t".$key. ':  <input type="text" name="'.$key.'" value="'.$param.'" />\n<br/>';
   
    $str .= '<input type="reset" value="Reset" onclick="return resetForm(this.form);">';
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
