<?php
require_once 'env.php';
require_once '../db.php';

if (ENV != 'LOCAL')
	die('Not in local mode');

function showDescriptions(){
    $result = "";
    $db = getDb();
    $tables = $db->query("SHOW TABLES");
    while($table = $tables->fetch()){
	$strtable = $table[0];
	$Ustrtable = strtoupper($strtable);
	echo "\ndefine('TBL_".$Ustrtable."', '".$strtable."');\n\n";
        $columns = $db->query("SHOW FULL COLUMNS FROM `".$strtable."`");
        while($column = $columns->fetch(PDO::FETCH_ASSOC)){
            $strcol = $column['field'];
            $Ustrcol = strtoupper($strcol);
            echo "define('".$Ustrtable."_".$Ustrcol."','".$strcol."');\n";
        }
    }
    return $result;
}


echo '<pre>';
$db = getDb();
$query = $db->query('SELECT * FROM schema_version');
echo '// Version de la bdd : ';
echo join($query->fetch(PDO::FETCH_NUM),'.');
echo "(+1) \n";
 (showDescriptions());

