<?php

require_once '../config.php';
require_once '../requete.php';
require_once '../db.php';
require_once '../lib/medoo.php';

use dbinteraction as I;
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
	return $name.'_'.$uniq;
}

//function genUniqName($name) {
	//return $name.'_'.uniqid();
//}


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
  else {
	  $color = 'red';
	  echo "ECHEC\n";
  }

	  echo '<p style="background-color:'.$color.'">';
  echo json_encode($ret, JSON_PRETTY_PRINT);
	  
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


function lienTo($lien) {
   echo "<a href='#$lien'>$lien</a>\n";
}
function liensrc($lien) {
   echo "<a id='$lien'>$lien</a>\n";
}

lienTo('basique');
lienTo('historique-teams');
lienTo('list-matches-valid');


liensrc('basique');

//testParams('update_user_picture',  array ($photo => ?));
//testParams('update_team_picture',  array ($photo => ?, $id_team => ?));

$user1_name = genName('user_test');
$user1_mail = genName('ta@gueule');
//testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));
//$id_user1 = getLastIdTable(TBL_USERS);
    $id_user1 = I\create_user( $user1_name, "nom_atest", $user1_mail, '0132', 'hash',null);


testParams('login', $valid_fail, array ("email" => 'ta@gueule', "hashmdp" => 'rien'));
testParams('login', $valid_ok, array ("email" =>$user1_mail, "hashmdp" => 'hash'));
testParams('update_user_sport_prefs', $valid_ok, array ("football" => 1, "basket" => 0));
testParams('new_team', $valid_ok, array ("pseudo" => genName('test_team1'), "id_sport" => 1));

$id_team1 = getLastIdTable(TBL_TEAMS);

$mail2 = genName('ta@gueule2_');
$id_user2 = I\create_user( genName('user_test2'), "nom_atest2", $mail2, '1132', 'hash2',null);
//testParams('newuser',$valid_ok,  array ("prenom" => genName('user_test2'), "nom" => 'name_atest2', "email" =>$mail2, "tel" => '1132', "hashmdp" => 'hash2'));
testParams('login', $valid_ok, array ("email" =>$mail2, "hashmdp" => 'hash2'));
testParams('update_user_sport_prefs',$valid_ok,  array ("football" => 1, "basket" => 0));
testParams('new_team',$valid_ok,  array ("pseudo" => genName('test_team2'), "id_sport" => 1));

$id_team2 = getLastIdTable(TBL_TEAMS);

testParams('invites_in_team', $valid_ok, array ("id_team" => $id_team2, "id_invite" => $id_user1));

testParams('invites_team_team', $valid_ok, array ("id_invitant" => $id_team2, "id_invite" => $id_team1, "date" => "now", "montant" => 350));
testParams('team_likes_team', $valid_fail, array ("id_bogoss" => $id_team1, "id_amoureux" => $id_team2));
testParams('team_likes_team', new MatchValidator(array('match' => false)), array ("id_bogoss" => $id_team2, "id_amoureux" => $id_team1));

testParams('post_result_match', $valid_ok,  array ("id_team_user" => $id_team2, "id_team2" => $id_team1, "result" => 1, "avis" => "bonne équipe", "fairplay" => 1));

testParams('post_chat_inter_teams', $valid_fail,  array ("id_team_user" => $id_team1, "id_team_cible" => $id_team2, "msg" => "coucou l'autre team"));
testParams('post_chat_inter_teams', $valid_ok, array ("id_team_user" => $id_team2, "id_team_cible" => $id_team1, "msg" => "coucou l'autre team"));

testParams('list_teams_by_sport', $valid_ok, array ("id_sport" => 1));

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
testParams('list_recherche_team_users',  $valid_ok, array ("id_sport" => 1));
testParams('remove_recherche_team_users', $valid_ok,  array ("id_team" => $id_team1));


liensrc('historique-teams');

echo "TODO : affiner le validateur (pour l'instant on v�rifie juste que le serveur renvoie OK)\n\n";

// On cr�e 10 utilisateurs, avec chacun 10 �quipes avec des scores du tableau $scores. Chacune des �quipes
// rencontre les autres
$nb = 10;
//
$nbrencontres = 200;

echo "$nb �quipes, $nbrencontres rencontres\n";

$scores= array();
//$users_id = array();
$teams_ids = array();
for ($i=0; $i < $nb; $i++) {
  $score = rand();
  $scores[] = $score;
  $id_team = insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>genName("hist_team_$i"), TEAMS_SPORT => 1, TEAMS_SCORE => $score));
  $teams_ids[] = $id_team;
}

/*
for ($i=0; $i < $nbrencontres; $i++) {
	echo rand(0,1);
}
 */


for ($i=0; $i < $nbrencontres; $i++) {
// CHaque �quipe rencontre entre 1 et 5 �quipes
  list($team1, $team2) = array_rand($teams_ids, 2);
  $date = date("Y-m-d H:i:s", rand(1,100000000));
    insertDb(TBL_MATCHES, array(
	MATCHES_ID_TEAM1 => $teams_ids[$team1],
	MATCHES_ID_TEAM2 => $teams_ids[$team2],
	MATCHES_VICTOIRE => rand(0,2),
	MATCHES_VALIDE => rand(0,1),
        MATCHES_DATE => $date));
}


testParams('classement_teams', $valid_ok, array ('limit' => 20));
testParams('historique_team',$valid_ok,  array ('id_team' =>$teams_ids[0], 'limit' => 20));

echo "Test de la liste des matches � valider\n\n";
liensrc('list-matches-valid');

$user1_name = genName('user_validator');
$user1_mail = genName('ta@validator');
//testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));
I\create_user( $user1_name, "nom_atest", $user1_mail, '1132', 'hash',null);
testParams('login', $valid_ok, array ("email" =>$user1_mail, "hashmdp" => 'hash'));
testParams('join_team', $valid_ok, array ("id_team" => $teams_ids[0]));

for ($i=0; $i < 20; $i++) {
  $team1 = array_rand($teams_ids, 1);
  $date = date("Y-m-d H:i:s", rand(1,100000000));
    insertDb(TBL_MATCHES, array(
	MATCHES_ID_TEAM1 => $teams_ids[$team1],
	MATCHES_ID_TEAM2 => $teams_ids[0],
	MATCHES_VICTOIRE => rand(0,2),
	MATCHES_VALIDE => 0,
        MATCHES_DATE => $date));
}
testParams('list_results_a_valider', $valid_ok, array ("id_team" => $teams_ids[0]));
