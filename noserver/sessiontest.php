<?php

require_once '../config.php';
require_once '../requete.php';
require_once '../db.php';
require_once '../lib/medoo.php';

if (ENV != 'LOCAL')
	die('Not in local mode');


function getLastIdTable($table) {
  $sth = getDb()->prepare('SELECT MAX(ID) FROM '.$table)->execute();
  return $sth->fetchColumn();
}

function testParams($req, $params) {
  echo "requete : ".$req."\n  params : ".json_encode($params)."\n\n";
  echo json_encode(dispatchParams($req, $params));
  echo "\n\n\n";
}

function signalExc() {
  echo "should raise an exception \n";
}

echo "<pre>Vider la base de données (de ses données) avant d'exécuter les tests\n";

//testParams('update_user_picture',  array ($photo => ?));
//testParams('update_team_picture',  array ($photo => ?, $id_team => ?));

testParams('newuser',  array ("prenom" => 'user_test1', "nom" => 'name_atest', "email" => 'ta@gueule', "tel" => '0132', "hashmdp" => 'hash'));

$id_user1 - getLastIdTable(TBL_USERS);

signalExc();
testParams('login',  array ("email" => 'ta@gueule', "hashmdp" => 'rien'));
testParams('login',  array ("email" => 'ta@gueule', "hashmdp" => 'hash'));
testParams('update_user_sport_prefs',  array ("football" => 1, "basket" => 0));
testParams('new_team',  array ("pseudo" => 'test_team1', "sport" => 1));

$id_team1 = getLastIdTable(TBL_TEAMS);

testParams('newuser',  array ("prenom" => 'user_test2', "nom" => 'name_atest2', "email" => 'ta@gueule2', "tel" => '1132', "hashmdp" => 'hash2'));
testParams('login',  array ("email" => 'ta@gueule2', "hashmdp" => 'hash2'));
testParams('update_user_sport_prefs',  array ("football" => 1, "basket" => 0));
testParams('new_team',  array ("pseudo" => 'test_team2', "sport" => 1));

$id_team2 = getLastIdTable(TBL_TEAMS);

testParams('invites_in_team',  array ("id_team" => $id_team2, "id_invite" => $id_user1));

testParams('invites_team_team',  array ("id_invitant" => $id_team2, "id_invite" => $id_team1, "date" => "now", "montant" => 350));
signalExc();
testParams('team_likes_team',  array ("id_bogoss" => $id_team1, "id_amoureux" => $id_team2));
testParams('team_likes_team',  array ("id_bogoss" => $id_team2, "id_amoureux" => $id_team1));

testParams('post_result_match',  array ("id_team_user" => $id_team2, "id_team2" => $id_team1, "result" => 1));

signalExc();
testParams('post_chat_inter_teams',  array ("id_team_user" => $id_team1, "id_team_cible" => $id_team2, "msg" => "coucou l'autre team"));
testParams('post_chat_inter_teams',  array ("id_team_user" => $id_team2, "id_team_cible" => $id_team1, "msg" => "coucou l'autre team"));

testParams('list_teams_by_sport',  array ("sport" => 1));

$id_result = getLastIdTable(TBL_MATCHES);

signalExc();
testParams('validate_result_match',  array ("id_result" => $id_result));

signalExc();
testParams('join_team',  array ("id_team" => $id_team1));

signalExc();
testParams('unjoin_team',  array ("id_team" => $id_team1));

testParams('unjoin_team',  array ("id_team" => $id_team2));
testParams('join_team',  array ("id_team" => $id_team1));
testParams('validate_result_match',  array ("id_result" => $id_result));

signalExc();
testParams('post_chat_interne',  array ("id_team" => $id_team2, "msg" => "coucou"));
testParams('post_chat_interne',  array ("id_team" => $id_team1, "msg" => "coucou"));

testParams('post_recherche_team_users',  array ("id_team" => $id_team1, "frequence" => 5, "nb" => 3, "niveau" => 2, "description" => "On veut du lourd !"));
// TODO: ne pas lister ses propres annonces
testParams('list_recherche_team_users',  array ("sport" => 1));
testParams('remove_recherche_team_users',  array ("id_team" => $id_team1));


