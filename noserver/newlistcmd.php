<?php
require_once 'config.php';
require_once '../requete.php';

if (ENV != 'LOCAL')
	die('Not in local mode');

$serieux = false;
function appendFile($file, $content, $flag) {
if ($serieux) { 
 if ($flag) file_put_contents($file, $content,$flag); else file_put_contents ($file,$content);
}
echo "--- ".$file."\n\n".$content."\n\n\n";
}

function putRoutes($routes) {
  appendFile('../'.PRIVATE_DIR.'/routes', json_encode($routes, JSON_PRETTY_PRINT));
}

echo "PLEASE Check with git diff <pre>\n\n";
$routes = routage();


$req = $_POST['requete'];
if(!$req) {
?>


    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
<input type='text' name='requete' value="requete" />
<input type='text' name='fun' value="fun" />
<input type='submit' value='submit' />
mettre serieux pour que ca ecrive vraiment
<input type='text' name='serieux' value='pas serieux' />
Paramètres
<input type='text' name='params[]' value="" />
<input type='text' name='params[]' value="" />
<input type='text' name='params[]' value="" />
<input type='text' name='params[]' value="" />
<input type='text' name='params[]' value="" />

<?php
die('fin');
}

$params = array_filter($_POST['params']);
$serieux = $_POST['serieux'] == 'serieux';

if ($serieux)
  echo "ATTENTION: SERIEUX\n";
$fun = $_POST['fun'];

$route = array('fun' -> $fun, 'params' => $params);

$routes[$requete] = $route;
putRoutes($routes);

  $params_s = array();
  foreach ($route['params'] as $param)
	  $params_s[] = '$'.$param;

  if (isset($route['file']))
           array_unshift($params_s, '$'.$route['file']);

  $funproto =$route['fun']."(".join($params_s, ', ').')'.
  appendFile('../requete.php', "function ".$funproto."  {\n   return I\\".$funproto.";\n}\n\n", FILE_APPEND);

 $body = <<<'BODY'
   $db = getDb();

   $requete ='SELECT t.* FROM '.TBL_?.' as t WHERE t.'. ?. ' =? LIMIT 10 ';
   $stmt= $db->prepare($requete);

    if ($stmt->execute(array($?)))
	return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    else
	return false;
BODY;
  appendFile('../dbinteraction.php', "function ".$funproto."  {\n   return I\\".$funproto.";\n}\n\n", FILE_APPEND);



