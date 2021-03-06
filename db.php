<?php


require_once "nogit/config.php";
require_once "noserver/config.php";
require_once 'exceptions.php';
require_once 'lib/medoo.php';
require_once 'lib.php';

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
	
// Connexion � la base de donn�es
    static $db = null;
    if ($db == null) {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PWD);
        // Configuration facultative de la connexion
        $db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // les noms de champs seront en caract�res minuscules
        $db->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION); // les erreurs lanceront des exceptions
	$db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
	//$stmt = $db->query('SET CHARACTER SET utf8');
	//$stmt->execute();
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


interface SQLExecute {
  function execute($stmt);
}


abstract class SQLExecAbs implements SQLExecute {
  protected $vals;
  function __construct($vals =null) { $this->setvals($vals);}
  public function setvals($vals) { 
	  if (DEBUG_DUMP_SQL) {
	         echo "valeurs SQL : ";
               var_dump($vals);
		 echo "\n";
	  }
	  $this->vals = ($vals == null) ? array() : $vals; return $this ;
  }
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


function requestGeneric($requete, SQLExecute $e) {
    $db = getDb();
    if (DEBUG_DUMP_SQL)
    	echo "$requete\n";
    $stmt= $db->prepare($requete);
    return ($e->execute($stmt));
}

class SQLSelect {
  //protected $cmd;
  //var $table;
  protected $where_str = null;
  protected $orderstr = '';
  protected $limit= null;
  //protectedar $columns = array();
  protected $cols_str = '';
  protected $oexec=  null;
  protected $vals = array();
  protected $join_str = '';
  protected $from_str = '';
  protected $groupby = '';

  function groupBy($groupby) {
    $this->groupby = $groupby;
    return $this;
  }

  function setColStr($colstr) {
    $this->cols_str = $colstr;
    return $this;
  }
  // colonne
  public function setCols($cols) {
     $this->cols_str = join($cols,',');
     return $this;
  }

  public function addColStr($str) {
     set_concat_sep($this->cols_str, $str);
     return $this;
  }

  public function addCol($col, $prefix='T') {
     return $this->addColStr("$prefix.$col");
  }

  public function addCola ($alias, $col, $prefix='T') {
      return $this->addColStr("$prefix.$col AS $alias");
  }

  public function order($prefix, $col, $ordre) {
      $this->orderstr = "\nORDER BY $prefix.$col $ordre ";
      return $this;
  }


  public function from($table, $alias = 'T') {
    $this->from_str = $table.' AS '.$alias;
    return $this;
  }

  function setWhere($wherestr, $vals=array()) {
     $this->where_str = $wherestr;
     $this->vals = $vals;
     return $this;
  }

  public function limit($limit) {
     $this->limit = $limit;
     return $this;
  }

  public function joinstr($str) {
     $this->join_str .= "\nJOIN $str";
     return $this;
  } 

  
   public function joinp($table, $alias, $prefix1, $col1, $prefix2, $col2) {
     return $this->joinstr(" $table AS $alias ON $prefix1.$col1 = $prefix2.$col2");
  }

  public function addVal($val){
     $this->vals[] = $val;
     return $this;
  }

  public function addVals($vals){
     $this->vals = array_merge($this->vals, $vals);
     return $this;
  }

  public function getVals() {
    return $this->vals;
  }


  public function andWhereEqp($prefix, $col, $val = null) {
     set_concat_sep($this->where_str, "$prefix.$col = ?", ' AND ');

     if ($val !== null)
     	$this->vals[] = $val;
     return $this;
  }

  public function andWhereStr($str, $vals = array()) {
     set_concat_sep($this->where_str, "($str)", ' AND ');
     $this->vals = array_merge($this->vals, $vals);
     return $this;
  }



  protected function getWhereStr(){
     return ($this->where_str) ? "\nWHERE ".$this->where_str.' ' : '';
  }
  protected function getLimitStr() {
     return ($this->limit) ? "\nLIMIT ".intval($this->limit).' ' : '';
  }

  protected function getGroupbyStr() {
     return ($this->groupby) ? "\nGROUP BY ".$this->groupby.' ' : '';
  }


  public function execute() {
     if (!$this->oexec)
       $this->oexec = new SQLExecCheck();

     $vals = $this->vals;
     $this->oexec->setvals($vals);
     return requestGeneric($this->sqlrequete(), $this->oexec);
  }

  function sqlrequete() {
    //$cols_str = join($cols,',');
    $str = sprintf('SELECT %s FROM %s %s %s %s %s %s', $this->cols_str, $this->from_str, $this->join_str, $this->getWhereStr(), $this->getGroupbyStr(), $this->orderstr, $this->getLimitStr());


    return $str;
  }

  function __toString() {
	  return $this->sqlrequete();
  }

}



function oselect() { return new SQLSelect(); }



function selReqDbWhStr($table, $cols, $wherestr) {
    $cols_str = join($cols,',');
    return ('SELECT '.$cols_str.' FROM '.$table.' WHERE '.$wherestr);
}




function execCheck($req, $vals) {
    return requestGeneric($req, new SQLExecCheck($vals));
}

function execUniqGeneric($req, $vals) {
    return requestGeneric($req, new SQLExecUniq($vals));
}
function selectDbWhStr($table, $cols, $wherestr, $vals) {
    return execCheck(selReqDbWhStr($table, $cols, $wherestr), $vals);
}

function insertDb($table, $array){
    list($cles, $vals,$marks) = arrToKeysAndMarks($array);

    $str_marks = join(',', $marks);
    $str_keys = join(',', $cles);
    $req = "INSERT INTO ".$table."(".$str_keys.') VALUES ('.$str_marks.')';

    return execUniqGeneric(($req), $vals);
    //var_dump($req);
    //var_dump($vals);

}

function update_wherestr($table, $valeurs, $wherestr, $where_vals) {
    list($set_str, $set_vals) = equalities_string($valeurs, ',');

    $req = ("UPDATE $table SET $set_str WHERE $wherestr");
    return execUniqGeneric(($req), array_merge($set_vals, $where_vals));
}

function update_wherea($table, $valeurs, $wherea) {
    list($where_str, $where_vals) = equalities_string($wherea, 'AND');
    return update_wherestr($table, $valeurs, $where_str,$where_vals);
}

function updateDb($table, $valeurs, $id) {
	return update_wherea($table, $valeurs, array('ID' => $id));
}

function updateDbEmail($table, $valeurs, $email) {
    $db = getDb();

    list($cles,$vals,$marks) = arrToKeysAndMarks($valeurs);

    $lst_str = array_map(function($key) { return $key.'=?';}, $cles);
    $str = join($lst_str, ',');

    $stmt = $db->prepare("UPDATE ".$table." SET ".$str. ' WHERE MAIL=?');
    $vals[] = $email;


    $email = exec_uniq($stmt, $vals);
    return $email;
}



function equalities_string($wherea, $sep) {
    $str = '';
    list($cles,$vals,$marks) = arrToKeysAndMarks($wherea);

    foreach ($cles as $cle)
	$str .= "$sep $cle = ? ";

    if ($str)
       $str = substr($str, strlen($sep));
    return array($str, $vals);
}

function selectDbArr($table, $cols, $wherea) {
    list($wherestr, $vals) = equalities_string($wherea, 'AND');

    return selectDbWhStr($table,$cols, $wherestr, $vals);
}

function selectId($table, $cols, $id) {
    return selectDbWhStr($table, $cols, 'ID=?', array($id));
}

function deleteDbWhStr($table, $wherestr, $vals) {
    return execCheck('DELETE FROM '.$table.' WHERE '.$wherestr, $vals);
}

function deleteDbArr($table, $wherea) {
    list($wherestr, $vals) = equalities_string($wherea, 'AND');
    return deleteDbWhStr($table,$wherestr, $vals);
}
