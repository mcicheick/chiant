<?php


require_once "nogit/config.php";
require_once "noserver/config.php";
require_once 'exceptions.php';
require_once 'lib/medoo.php';

if (ENV == "LOCAL")
{
	define ('DB_NAME', LOCAL_DB_NAME);
	define ('DB_PWD' , LOCAL_DB_PWD);
	define ('DB_USER', LOCAL_DB_USER);
	define ('DB_HOST', LOCAL_DB_HOST);
}
else {
	define ('DB_NAME', SERVER_DB_NAME);
	define ('DB_PWD' , SERVER_DB_PWD);
	define ('DB_USER', SERVER_DB_USER);
	define ('DB_HOST', SERVER_DB_HOST);
}

function getMedoo() {
   static $db = null;

   if ($db == null) {
    $db = new medoo(array(
            // required
            'database_type' => 'mysql',
            'database_name' => DB_NAME,
            'server' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PWD,
            'charset' => 'utf8',

            )
    );
    }
    return $db;
}


function getDb() {
	
// Connexion à la base de données
    static $db = null;
    if ($db == null) {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PWD);
        // Configuration facultative de la connexion
        $db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // les noms de champs seront en caractères minuscules
        $db->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION); // les erreurs lanceront des exceptions
    }
    return $db;
}

function arrToKeysAndMarks($array) {
    $vals = array();
    $marks = array();
    $cles = array_keys($array);
    foreach ($cles as $cle) {
        $vals[] = $array[$cle];
        $marks[] = '?';
    }
    return array($cles, $vals, $marks);
}

function exec_uniq($stmt,$vals) {
    try {
    $stmt->execute($vals);
    
    }
    catch (PDOException $e) {
	if ($e->errorInfo[1] == 1062) {
	    throw new DbInsertUniqueExc();
	}
	else {
	    throw $e;
	}	
    }
    $id = getDb()->lastInsertId();
    return $id;
}

function insertDb($table, $array){
    $db = getDb();
    list($cles, $vals,$marks) = arrToKeysAndMarks($array);

    $str_marks = join(',', $marks);
    $str_keys = join(',', $cles);

    $stmt = $db->prepare("INSERT INTO ".$table."(".$str_keys.') VALUES ('.$str_marks.')');

    return exec_uniq($stmt, $vals);
}

function updateDb($table, $valeurs, $id) {
    $db = getDb();

    list($cles,$vals,$marks) = arrToKeysAndMarks($valeurs);

    $lst_str = array_map(function($key) { return $key.'=?';}, $cles);
    $str = join($lst_str, ',');

    $stmt = $db->prepare("UPDATE ".$table." SET ".$str. ' WHERE ID=?');
    $vals[] = $id;

    $id = exec_uniq($stmt, $vals);
    return $id;
}

function selectDbWhStr($table, $cols, $wherestr, $vals) {
    $db = getDb();


    $cols_str = join($cols,',');

    $stmt= $db->prepare('SELECT '.$cols_str.' FROM '.$table.' WHERE '.$wherestr);
    if ($stmt->execute($vals))
	return $stmt;
    else
	return false;
}

function selectDbArr($table, $cols, $wherea) {
    $db = getDb();

    list($cles,$vals,$marks) = arrToKeysAndMarks($wherea);

    $lst = array_map(function($key) { return $key.'=?';}, $cles);
    $lst_str = join($lst, ' AND ');
    return selectDbWhStr($table,$cols, $lst_str, $vals);
}

function selectId($table, $cols, $id) {
    return selectDbWhStr($table, $cols, 'ID=?', array($id));
}

function deleteDbWhStr($table, $wherestr, $vals) {
    $db = getDb();

    $stmt= $db->prepare('DELETE FROM '.$table.' WHERE '.$wherestr);
    return $stmt->execute($vals);
}

function deleteDbArr($table, $wherea) {
    list($cles,$vals,$marks) = arrToKeysAndMarks($wherea);

    $lst = array_map(function($key) { return $key.'=?';}, $cles);
    $lst_str = join($lst, ' AND ');
    return deleteDbWhStr($table,$lst_str, $vals);
}
