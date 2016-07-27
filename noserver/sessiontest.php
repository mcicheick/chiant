<?php

require_once '../config.php';
require_once '../requete.php';
require_once '../db.php';
require_once '../lib/medoo.php';
require_once '../routes.php';
require_once '../sports.php';

define('STOP_ON_ECHEC', 1);

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

function str_color($color, $str) {
    return '<p style="background-color:'.$color.'">'.$str."</p>\n\n";
}

function testParams($req, Validator $is_ok, $params) {
  echo $is_ok->message()."\n";

  echo "requete : ".$req."\n  params : ".json_encode($params)."\n\n";
  $ret = dispatchParams($req, $params);
  $is_good =$is_ok->validate($ret);
  if ($is_good)
	  $color = 'green';
  else {
	  $color = 'red';
	  echo "ECHEC\n";
  }
  echo str_color($color, json_encode($ret, JSON_PRETTY_PRINT));

  if (!$is_good && STOP_ON_ECHEC)
	  die('fin');
}
class OKValidator implements Validator {
   function message() { return ''; }
function validate($ret) { 
	$contents = array();
	if (isset($ret['contents']))
	  $contents = $ret['contents'];
	return $ret['answer'] == 'OK' && $this->validateContents($contents);
}
   protected function validateContents($contents) { return true; }
}
$valid_ok = new OKValidator();

class FailValidator implements Validator {
   function message() { return 'should raise an exception'; }
   function validate($ret) { return $ret['answer'] != 'OK'; }
}
$valid_fail = new FailValidator();

class MatchValidator extends OKValidator {
   var $arr;
   function __construct($arr=array()) { $this->setResultats( $arr); }
   function setResultats($arr) { $this->arr = $arr; sort($this->arr); }
   function message() { return 'should give as a result : '.json_encode($this->arr, JSON_PRETTY_PRINT); }
   function validateContents($contents) { 
   //var_dump($ret['contents']);
   ////var_dump($this->arr);
	   sort($contents);
   return $contents == $this->arr; }
        
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
lienTo('prefs-sports');
lienTo('signals-team');
lienTo('chat-inter-team');
lienTo('chat-interne-team');
lienTo('chat-user-team-annonces');
lienTo('chat-team-user-annonces');


liensrc('basique');

echo "Test de l'accord entre la liste des sports en SQL et la liste des sports en PHP";
$phpSports = listSports();
sort($phpSports);
$validatorSports = new MatchValidator($phpSports);


 $stmt = selectDbWhStr(TBL_REF_SPORTS,array(REF_SPORTS_ID, REF_SPORTS_NOM), 1, array());
 $dbSports = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
 sort($dbSports);

 if ($validatorSports->validateContents($dbSports))
	 echo str_color("green", "OK");
 else
	 echo str_color("red", "PHP : ".join($phpSports,', ')."\nSQL : ".join($dbSports, ', '));

 echo "\n";

//testParams('update_user_picture',  array ($photo => ?));
//testParams('update_team_picture',  array ($photo => ?, $id_team => ?));

$user1_name = genName('user_test');
$user1_mail = genName('ta@gueule');
//testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));
//$id_user1 = getLastIdTable(TBL_USERS);
    $id_user1 = I\create_user( $user1_name, "nom_atest", $user1_mail, '0132', 'hash',null,0,0,'','');


testParams('login', $valid_fail, array ("email" => 'ta@gueule', "hashmdp" => 'rien'));
// drole de bug : ne d?tecte pas que les arguments ne sont pas les bons...
//testParams('list_msg_chat_inter_team', $valid_ok, array('id_team_user' => 4, 'id_team2' => 3, '0' => 0));

testParams('login', $valid_fail, array ("email" => 'ta@gueule', "hashmdp" => 'rien'));
testParams('login', $valid_ok, array ("email" =>$user1_mail, "hashmdp" => 'hash'));
testParams('update_user_sport_prefs', $valid_ok, array ("football" => 1, "basketball" => 0));
testParams('new_team', $valid_ok, array ("pseudo" => genName('test_team1'), "id_sport" => 1));

$id_team1 = getLastIdTable(TBL_TEAMS);

$mail2 = genName('ta@gueule2_');
$id_user2 = I\create_user( genName('user_test2'), "nom_atest2", $mail2, '1132', 'hash2',null,0,0,'','');
//testParams('newuser',$valid_ok,  array ("prenom" => genName('user_test2'), "nom" => 'name_atest2', "email" =>$mail2, "tel" => '1132', "hashmdp" => 'hash2'));
testParams('login', $valid_ok, array ("email" =>$mail2, "hashmdp" => 'hash2'));
testParams('update_user_sport_prefs',$valid_ok,  array ("football" => 1, "basketball" => 0));
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

echo "TODO : affiner le validateur (pour l'instant on v?rifie juste que le serveur renvoie OK)\n\n";

// On cr?e 10 utilisateurs, avec chacun 10 ?quipes avec des scores du tableau $scores. Chacune des ?quipes
// rencontre les autres
$nb = 10;
//
$nbrencontres = 200;

echo "$nb ?quipes, $nbrencontres rencontres\n";

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
// CHaque ?quipe rencontre entre 1 et 5 ?quipes
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

echo "Test de la liste des matches ? valider\n\n";
liensrc('list-matches-valid');

$user1_name = genName('user_validator');
$user1_mail = genName('ta@validator');
//testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));
I\create_user( $user1_name, "nom_atest", $user1_mail, '1132', 'hash',null,0,0,'','');
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

liensrc('prefs-sports');
echo "Test des pr?f?rences de sports\n";

$user1_name = genName('user_pref_sports');
$user1_mail = genName('ta@user_pref_sport');
//testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));
I\create_user( $user1_name, "nom_atest", $user1_mail, '1132', 'hash',null,0,0,'','');
testParams('login', $valid_ok, array ("email" =>$user1_mail, "hashmdp" => 'hash'));

$valid = new MatchValidator();
echo "On va tester toutes les possibilit?s de transition pour football/basket \n";

function testListPrefs ($i,$j) {
	global $valid_ok;
	global $valid;
	$valeurs =array ("football" => $i, "basketball" => $j);
	/*
	var_dump($valeurs);
	var_dump(array_filter($valeurs));
	var_dump(array_keys(array_filter($valeurs)));
	 */
	$valid->setResultats(array_keys(array_filter($valeurs)));
  testParams('update_user_sport_prefs', $valid_ok, $valeurs);
  testParams('list_prefs_sport', $valid, array());
}

for ($i = 0; $i < 2;$i++) {
for ($j = 0; $j < 2;$j++) {
for ($i2 = 0; $i2 < 2;$i2++) {
for ($j2 = 0; $j2 < 2;$j2++) {
	testListPrefs($i,$j);
	testListPrefs($i2,$j2);
}
}
}
}

testParams('update_user_sport_prefs', $valid_ok, array ("football" => 1, "basketball" => 0));

liensrc('signals-team');
$user1_name = genName('user_signal_teams');
$user1_mail = genName('ta@user_signal_teams');
//testParams('newuser', $valid_ok,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));
I\create_user( $user1_name, "nom_signal", $user1_mail, '1132', 'hash',null,0,0,'','');
testParams('login', $valid_ok, array ("email" =>$user1_mail, "hashmdp" => 'hash'));

testParams('new_team', $valid_ok, array ("pseudo" => genName('test_team1'), "id_sport" => 1));

$id_team = getLastIdTable(TBL_TEAMS);

$valid = new MatchValidator();

testParams('signale_team', $valid_ok, array ("id_team_signalante" => $id_team,
	"id_team_signale" => 3));
$res = selectDbArr(TBL_SIGNALS_TEAMS, array(SIGNALS_TEAMS_ID_TEAM2), array(SIGNALS_TEAMS_ID_TEAM1 => $id_team))->fetchColumn();


 if ($res == 3)
	 echo str_color("green", "OK");
 else
	 echo str_color("red", "R?sultat obtenu : $res (attendu : 3)");

 echo "\n";

liensrc('chat-inter-team');
$nequipes = 7;
$nbmsg = 30;
$nbuserspargroupe = 2;
echo "</pre>Chat inter équipes : $nequipes équipes, $nbmsg messages, $nbuserspargroupe utilisateurs par groupes<pre>\n";

$teams_ids = array();
$users_ids = array();
$users_mails = array();
for ($i=0; $i < $nequipes ;$i++) {
  $id_team = insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>genName("chat_inter_team_$i"), TEAMS_SPORT => 1, TEAMS_SCORE => 1 ));
  $teams_ids[] = $id_team;
  $u_ids= array();
  $u_mails = array();
  for($j=0; $j < $nbuserspargroupe; $j++) {
	  $uname = genName( "user_{$j}_chat_inter_team_$i");
	  $umail = $uname.'@mail';
	  $u_mails[] = $umail;
	  $u_ids[] = I\create_user($uname, "nom",$umail, '0132', 'hash',null,0,0,'','');
	  login($umail, 'hash');
	  join_team($id_team);
  }
  $users_ids [] = $u_ids;
  $users_mails[] = $u_mails;
}

$msgs = array();
for($i=0; $i< $nbmsg; $i++) {
    $n_user = rand(0, $nbuserspargroupe-1);
    $n_team_user = rand(0,$nequipes-1);
    $n_team_cible = $n_team_user;
    while ($n_team_cible == $n_team_user) 
    	$n_team_cible = rand(0, $nequipes -1);
    $msg = "Msg numéro $i";

    $msgs[] = array('user' => $n_user, 'team_user' => $n_team_user, 'team_cible' => $n_team_cible, 'msg' => $msg);

    //$id_user = $users_ids[$n_team_user][$n_user];
    $mail_user = $users_mails[$n_team_user][$n_user];

    login($mail_user, 'hash');
    testParams('post_chat_inter_teams', $valid_ok, array('id_team_user' => $teams_ids[$n_team_user], 'id_team_cible' => $teams_ids[$n_team_cible], 'msg' => $msg));
   testParams('list_msg_chat_inter_team', $valid_ok, array('id_team_user' => $teams_ids[$n_team_user], 'id_team2' => $teams_ids[$n_team_cible], 'date_last' => 0));
   testParams('last_chat_msg', $valid_ok, array());

}

liensrc('chat-interne-team');
$nequipes = 3;
$nbmsg = 20;
$nbuserspargroupe = 3 ;
echo "</pre>Chat interne équipes : $nequipes équipes, $nbmsg messages, $nbuserspargroupe utilisateurs par équipe<pre>\n";

$teams_ids = array();
$users_ids = array();
$users_mails = array();
for ($i=0; $i < $nequipes ;$i++) {
  $id_team = insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>genName("chat_interne_team_$i"), TEAMS_SPORT => 1, TEAMS_SCORE => 1 ));
  $teams_ids[] = $id_team;
  $u_ids= array();
  $u_mails = array();
  for($j=0; $j < $nbuserspargroupe; $j++) {
	  $uname = genName( "user_{$j}_chat_interne_team_$i");
	  $umail = $uname.'@mail';
	  $u_mails[] = $umail;
	  $u_ids[] = I\create_user($uname, "nom",$umail, '0132', 'hash',null,0,0,'','');
	  login($umail, 'hash');
	  join_team($id_team);
  }
  $users_ids [] = $u_ids;
  $users_mails[] = $u_mails;
}

$msgs = array();
for($i=0; $i< $nbmsg; $i++) {
    $n_user = rand(0, $nbuserspargroupe-1);
    $n_team_user = rand(0,$nequipes-1);
    $msg = "Msg interne numéro $i";

    $msgs[] = array('user' => $n_user, 'team_user' => $n_team_user,  'msg' => $msg);

    //$id_user = $users_ids[$n_team_user][$n_user];
    $mail_user = $users_mails[$n_team_user][$n_user];

    login($mail_user, 'hash');
    testParams('post_chat_interne', $valid_ok, array('id_team' => $teams_ids[$n_team_user],  'msg' => $msg));
   testParams('list_msg_chat_interne_team', $valid_ok, array('id_team' => $teams_ids[$n_team_user], 'date_last' => 0));
   testParams('last_chat_msg', $valid_ok, array());
}

liensrc('chat-user-team-annonces');
$nequipes = 10;
$nusers_annonce = 4;
$nbmsg = 50;
$nbuserspargroupe = 3 ;
echo "</pre>Chat user vers team ( annonces) : $nusers_annonce utilisateurs cherchant une équipe, $nequipes équipes, $nbmsg messages, $nbuserspargroupe utilisateurs par équipe<pre>\nOn teste les deux sens (user vers team et team vers user)\n";

$users_cherchant_ids = array();
$users_cherchant_mails = array();
 for($j=0; $j < $nusers_annonce; $j++) {
	  $uname = genName( "user_{$j}_cherchant_annonce");
	  $umail = $uname.'@mail';
	  $users_cherchant_mails[] = $umail;
	  $users_cherchant_ids[] = I\create_user($uname, "nom",$umail, '0132', 'hash',null,0,0,'','');
	  // pour vérifier le mot de passe
	  login($umail, 'hash');
 }

$teams_ids = array();
$users_ids = array();
$users_mails = array();
for ($i=0; $i < $nequipes ;$i++) {
  $id_team = insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>genName("chat_team_user_$i"), TEAMS_SPORT => 1, TEAMS_SCORE => 1 ));
  $teams_ids[] = $id_team;
  $u_ids= array();
  $u_mails = array();
  for($j=0; $j < $nbuserspargroupe; $j++) {
	  $uname = genName( "user_{$j}_chat_team_user_$i");
	  $umail = $uname.'@mail';
	  $u_mails[] = $umail;
	  $u_ids[] = I\create_user($uname, "nom",$umail, '0132', 'hash',null,0,0,'','');
	  login($umail, 'hash');
	  join_team($id_team);
  }
  $users_ids [] = $u_ids;
  $users_mails[] = $u_mails;
}

$msgs = array();
for($i=0; $i< $nbmsg; $i++) {

	if (rand(0,1) == 0) {
		// C'est un utilisateur d'une équipe qui va poster
		$n_user = rand(0, $nbuserspargroupe-1);
		$n_team_user = rand(0,$nequipes-1);
		$msg = "Msg team annonce numéro $i";
		$n_user_cible = rand(0,$nusers_annonce-1);

		$msgs[] = array('origine' => 'team' , 'user' => $n_user, 'team' => $n_team_user,  'msg' => $msg, 'user_cible' => $n_user_cible);

		//$id_user = $users_ids[$n_team_user][$n_user];
		$mail_user = $users_mails[$n_team_user][$n_user];

		login($mail_user, 'hash');
		testParams('post_chat_team_user_annonce', $valid_ok, array('id_team' => $teams_ids[$n_team_user],  'msg' => $msg, 
			'id_user_cible' => $users_cherchant_ids[$n_user_cible]));
		testParams('list_msg_chat_annonce_team_user', $valid_ok, array('id_team_user' => $teams_ids[$n_team_user],
			'id_user_cible' => $users_cherchant_ids[$n_user_cible],
		      	'date_last' => 0));
	}
	else {
		$n_user = rand(0, $nusers_annonce-1);
		$n_team = rand(0,$nequipes-1);
		$msg = "Msg cherchant annonce numéro $i";
		$msgs[] = array('origine' => 'user' , 'user' => $n_user, 'team' => $n_team,  'msg' => $msg);
		login($mail_user, 'hash');
		$mail_user = $users_cherchant_mails[$n_user];
		login($mail_user, 'hash');
		testParams('post_chat_user_team_annonce', $valid_ok, array('id_team' => $teams_ids[$n_team],  'msg' => $msg));
		testParams('list_msg_chat_annonce_user_team', $valid_ok, array('id_team' => $teams_ids[$n_team], 'date_last' => 0));
	}
   testParams('last_chat_msg', $valid_ok, array());
}

liensrc('chat-team-user-annonces');
echo "Inutile : on le fait déjà au dessus";
