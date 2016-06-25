<?php

require_once '../config.php';
require_once '../requete.php';
require_once '../db.php';
require_once '../lib/medoo.php';

if (ENV != 'LOCAL')
	die('Not in local mode');


function getLastIdTable($table) {
	$sth = getDb()->prepare('SELECT MAX(ID) FROM '.$table);
	$sth->execute();
  return $sth->fetchColumn();
}



$uniq = uniqid();
function genName($name) {
	global $uniq;
	return $name.$uniq;
}


interface Validator {
   function message() ;
   function validate($ret);
}

function testParams($req, Validator $is_ok, $params) {
  echo $is_ok->message()."\n";

  echo "requete : ".$req."\n  params : ".json_encode($params)."\n\n";
  $ret = dispatchParams($req, $params);
  if ($is_ok->validate($ret))
	  $color = 'green';
  else
	  $color = 'red';

	  echo '<p style="background-color:'.$color.'">';
  echo json_encode($ret);
	  
  echo "</p>\n\n\n";
}
class OKValidator implements Validator {
   function message() { return ''; }
   function validate($ret) { return $ret['answer'] == 'OK'; }
}
$valid_ok = new OKValidator();

class FailValidator implements Validator {
   function message() { return 'should raise an exception'; }
   function validate($ret) { return $ret['answer'] != 'OK'; }
}
$valid_fail = new FailValidator();

class MatchValidator implements Validator {
   var $arr;
   function __construct($arr) { $this->arr = $arr; }
   function message() { return 'should give as a result : '.json_encode($this->arr, JSON_PRETTY_PRINT); }
   function validate($ret) { 
   //var_dump($ret['contents']);
   ////var_dump($this->arr);
   return $ret['answer'] == 'OK' && $ret['contents'] == $this->arr; }
        
}




echo "<pre>Vider la base de données (de ses données) avant d'exécuter les tests\n";

//testParams('update_user_picture',  array ($photo => ?));
//testParams('update_team_picture',  array ($photo => ?, $id_team => ?));

$user1_name = genName('user_test');
$user1_mail = genName('ta@gueule');
testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));

$id_user1 = getLastIdTable(TBL_USERS);

testParams('login', $valid_fail, array ("email" => 'ta@gueule', "hashmdp" => 'rien'));
testParams('login', $valid_ok, array ("email" =>$user1_mail, "hashmdp" => 'hash'));
testParams('update_user_sport_prefs', $valid_ok, array ("football" => 1, "basket" => 0));
testParams('new_team', $valid_ok, array ("pseudo" => genName('test_team1'), "sport" => 1));

$id_team1 = getLastIdTable(TBL_TEAMS);

$mail2 = genName('ta@gueule2_');
testParams('newuser',$valid_ok,  array ("prenom" => genName('user_test2'), "nom" => 'name_atest2', "email" =>$mail2, "tel" => '1132', "hashmdp" => 'hash2'));
testParams('login', $valid_ok, array ("email" =>$mail2, "hashmdp" => 'hash2'));
testParams('update_user_sport_prefs',$valid_ok,  array ("football" => 1, "basket" => 0));
testParams('new_team',$valid_ok,  array ("pseudo" => genName('test_team2'), "sport" => 1));

$id_team2 = getLastIdTable(TBL_TEAMS);

testParams('invites_in_team', $valid_ok, array ("id_team" => $id_team2, "id_invite" => $id_user1));

testParams('invites_team_team', $valid_ok, array ("id_invitant" => $id_team2, "id_invite" => $id_team1, "date" => "now", "montant" => 350));
testParams('team_likes_team', $valid_fail, array ("id_bogoss" => $id_team1, "id_amoureux" => $id_team2));
testParams('team_likes_team', new MatchValidator(array('match' => false)), array ("id_bogoss" => $id_team2, "id_amoureux" => $id_team1));

testParams('post_result_match', $valid_ok,  array ("id_team_user" => $id_team2, "id_team2" => $id_team1, "result" => 1, "avis" => "bonne équipe", "fairplay" => 1));

testParams('post_chat_inter_teams', $valid_fail,  array ("id_team_user" => $id_team1, "id_team_cible" => $id_team2, "msg" => "coucou l'autre team"));
testParams('post_chat_inter_teams', $valid_ok, array ("id_team_user" => $id_team2, "id_team_cible" => $id_team1, "msg" => "coucou l'autre team"));

testParams('list_teams_by_sport', $valid_ok, array ("sport" => 1));

$id_result = getLastIdTable(TBL_MATCHES);

testParams('validate_result_match', $valid_fail, array ("id_result" => $id_result, "avis" => "Très mauvaise équipe", "fairplay" => 2));

testParams('join_team', $valid_fail, array ("id_team" => $id_team1));

testParams('unjoin_team', $valid_fail, array ("id_team" => $id_team1));

testParams('unjoin_team', $valid_ok, array ("id_team" => $id_team2));
testParams('join_team', $valid_ok, array ("id_team" => $id_team1));
testParams('validate_result_match',$valid_ok,  array ("id_result" => $id_result, "avis" => "Très mauvaise équipe", "fairplay" => 2));

testParams('team_likes_team', new MatchValidator(array('match' => true)), array ("id_bogoss" => $id_team1, "id_amoureux" => $id_team2));

testParams('post_chat_interne', $valid_fail, array ("id_team" => $id_team2, "msg" => "coucou"));
testParams('post_chat_interne', $valid_ok, array ("id_team" => $id_team1, "msg" => "coucou"));

testParams('post_recherche_team_users', $valid_ok,  array ("id_team" => $id_team1, "frequence" => 5, "nb" => 3, "niveau" => 2, "description" => "On veut du lourd !"));
// TODO: ne pas lister ses propres annonces
testParams('list_recherche_team_users',  $valid_ok, array ("sport" => 1));
testParams('remove_recherche_team_users', $valid_ok,  array ("id_team" => $id_team1));


