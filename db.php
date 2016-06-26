<?php


require_once "nogit/config.php";
require_once "noserver/config.php";
require_once 'exceptions.php';
require_once 'lib/medoo.php';
require_once 'lib.php'

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

interface SQLRequete {
  function sqlrequete();
}

interface SQLExecute {
  function execute($stmt);
}

interface SQLIRE extends SQLRequete, SQLExecute {
}


class SQLExecReq {
  public $requete ;
  public $execute ;

  function __construct($req, $exec) { $this->requete = $req; $this->execute = $execute; }
}

abstract class SQLExecAbs implements SQLExecute {
  protected $vals;
  function __construct($vals =null) { $this->setvals($vals);}
  public function setvals($vals) { $this->vals = ($vals == null) ? array() : $vals; return $this ;}
}

class SQLExecCheck extends SQLExecAbs {
  function execute($stmt) {
    if ($stmt->execute($this->vals))
    	return $stmt;
    else
    	return false;
  }
}

class SQLExecUniq extends SQLExecAbs {
  function execute($stmt) {
    $id = exec_uniq($stmt, $this->vals);
    return $id;
  }
}

class SQLRequeteStr implements SQLRequete {
  protected $req;
  function __construct($req) { $this->req = $req; }
  function sqlrequete() { return $this->req; }
}

/*
function table($table, $alias) {
   return $table.' AS '.$alias;
}

/*
function col ($prefix, $col) {
   return $prefix.'.'.$col;
}

function eq($s1, $s2) {
   return $s1.'='.$s2;
}

class buildCond {
  var $cond_str = '';

  function and($cond) {
    
  }
}
*/

class buildQuery implements SQLIRE {
  //protected $cmd;
  //var $table;
  protected $wherestr = null;
  protected $limit= null;
  //protectedar $columns = array();
  protected $cols_str = '';
  protected $oexec=  null;
  protected $where_vals = array();
  protected $join_str = '';
  protected $from_str = '';


  function setColStr($colstr) {
    $this->cols_str = $colstr;
    return $this;
  }
  // colonne
  function setCols($cols) {
     $this->cols_str = join($cols,',');
     return $this;
  }

  function addCol($col, $prefix='T') {
     set_concat_sep($this->cols_str, $prefix.'.'.$col);
     return $this;
  }


  function from($table, $alias = 'T') {
    $this->from_str = $table.' AS '.$alias;
    return $this;
  }

  function wherestr($wherestr, $vals) {
     $this->str = $wherestr;
     $this->where_vals = $vals;
     return $this;
  }

  function limit($limit) {
     $this->limit = $limit;
     return $this;
  }

  function join($table, $oncond, $alias = 'J') {
     $this->join_str .= ' JOIN '.$table.' AS '.$alias.' ON '.$oncond.' ';
     return $this;
  }

  function exec(SQLExecAbs $o) {
     $this->oexec = $o;
     return $this;
  }

  function addWhereStr (&$str) {
    if ($this->wherestr)
        $str.= ' WHERE '.$this->wherestr.' '; 
  }

  function addLimitStr(&$str) {
    if ($this->limit)
        $str.= ' LIMIT '.intval($this->limit).' ';
  }

  function sqlrequete() {
    //$cols_str = join($cols,',');
    $str = sprintf('SELECT %s %s', $this->cols_str, $this->from_str);

    $str.= $this->join_str;
    $this->addWhereStr($str);
    $this->addLimitStr($str);


    return $str;
  }

  function execute($stmt) {
     if (!$this->oexec)
       $this->oexec = new SQLExecCheck();

     $vals = $this->where_vals;
     $this->oexec->setvals($vals);
     return $this->oexec->execute($stmt);
  }

}

//function newSelect($cmd) { return new buildRequete($cmd); }



function selReqDbWhStr($table, $cols, $wherestr) {
    $cols_str = join($cols,',');
    return new SQLRequeteStr('SELECT '.$cols_str.' FROM '.$table.' WHERE '.$wherestr);
}


function requestGeneric(SQLRequete $r, SQLExecute $e) {
    $db = getDb();
    $stmt= $db->prepare($r->sqlrequete());
    return ($e->execute($stmt));
}

function requestGeneric1(SQLIRE $o) {
    return requestGeneric($o, $o);
}

function execCheckGeneric($req, $vals) {
    return requestGeneric($req, new SQLExecCheck($vals));
}

function execUniqGeneric($req, $vals) {
    return requestGeneric($req, new SQLExecUniq($vals));
}
function selectDbWhStr($table, $cols, $wherestr, $vals) {
    return execCheckGeneric(selReqDbWhStr($table, $cols, $wherestr), $vals);
}

function insertDb($table, $array){
    list($cles, $vals,$marks) = arrToKeysAndMarks($array);

    $str_marks = join(',', $marks);
    $str_keys = join(',', $cles);
    $req = "INSERT INTO ".$table."(".$str_keys.') VALUES ('.$str_marks.')';

    return execUniqGeneric(new SQLRequeteStr($req), $vals);
    //var_dump($req);
    //var_dump($vals);

}

function updateDb($table, $valeurs, $id) {

    list($cles,$vals,$marks) = arrToKeysAndMarks($valeurs);

    $lst_str = array_map(function($key) { return $key.'=?';}, $cles);
    $str = join($lst_str, ',');

    $req = ("UPDATE ".$table." SET ".$str. ' WHERE ID=?');
    $vals[] = $id;

    return execUniqGeneric(new SQLRequeteStr($req), $vals);

}




function selectDbArr($table, $cols, $wherea) {

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
