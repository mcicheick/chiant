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

function testParams($req, $is_ok, $params) {
 if (!$is_ok)
    echo "should raise an exception \n";

  echo "requete : ".$req."\n  params : ".json_encode($params)."\n\n";
  $ret = dispatchParams($req, $params);
  if (($ret['answer'] == 'OK' && $is_ok) || (!$is_ok && $ret['answer'] != 'OK'))
	  $color = 'green';
  else
	  $color = 'red';

	  echo '<p style="background-color:'.$color.'">';
  echo json_encode($ret);
	  
  echo "</p>\n\n\n";
}


echo "<pre>Vider la base de données (de ses données) avant d'exécuter les tests\n";

//testParams('update_user_picture',  array ($photo => ?));
//testParams('update_team_picture',  array ($photo => ?, $id_team => ?));

$user1_name = genName('user_test');
$user1_mail = genName('ta@gueule');
testParams('newuser', true,  array ("prenom" => $user1_name, "nom" => 'name_atest', "email" => $user1_mail, "tel" => '0132', "hashmdp" => 'hash'));

$id_user1 = getLastIdTable(TBL_USERS);

testParams('login', false, array ("email" => 'ta@gueule', "hashmdp" => 'rien'));
testParams('login', true, array ("email" =>$user1_mail, "hashmdp" => 'hash'));
testParams('update_user_sport_prefs', true, array ("football" => 1, "basket" => 0));
testParams('new_team', true, array ("pseudo" => genName('test_team1'), "sport" => 1));

$id_team1 = getLastIdTable(TBL_TEAMS);

$mail2 = genName('ta@gueule2_');
testParams('newuser',true,  array ("prenom" => genName('user_test2'), "nom" => 'name_atest2', "email" =>$mail2, "tel" => '1132', "hashmdp" => 'hash2'));
testParams('login', true, array ("email" =>$mail2, "hashmdp" => 'hash2'));
testParams('update_user_sport_prefs',true,  array ("football" => 1, "basket" => 0));
testParams('new_team',true,  array ("pseudo" => genName('test_team2'), "sport" => 1));

$id_team2 = getLastIdTable(TBL_TEAMS);

testParams('invites_in_team', true, array ("id_team" => $id_team2, "id_invite" => $id_user1));

testParams('invites_team_team', true, array ("id_invitant" => $id_team2, "id_invite" => $id_team1, "date" => "now", "montant" => 350));
testParams('team_likes_team', false, array ("id_bogoss" => $id_team1, "id_amoureux" => $id_team2));
testParams('team_likes_team', true, array ("id_bogoss" => $id_team2, "id_amoureux" => $id_team1));

testParams('post_result_match', true,  array ("id_team_user" => $id_team2, "id_team2" => $id_team1, "result" => 1));

testParams('post_chat_inter_teams', false,  array ("id_team_user" => $id_team1, "id_team_cible" => $id_team2, "msg" => "coucou l'autre team"));
testParams('post_chat_inter_teams', true, array ("id_team_user" => $id_team2, "id_team_cible" => $id_team1, "msg" => "coucou l'autre team"));

testParams('list_teams_by_sport', true, array ("sport" => 1));

$id_result = getLastIdTable(TBL_MATCHES);

testParams('validate_result_match', false, array ("id_result" => $id_result));

testParams('join_team', false, array ("id_team" => $id_team1));

testParams('unjoin_team', false, array ("id_team" => $id_team1));

testParams('unjoin_team', true, array ("id_team" => $id_team2));
testParams('join_team', true, array ("id_team" => $id_team1));
testParams('validate_result_match',true,  array ("id_result" => $id_result));

testParams('post_chat_interne', false, array ("id_team" => $id_team2, "msg" => "coucou"));
testParams('post_chat_interne', true, array ("id_team" => $id_team1, "msg" => "coucou"));

testParams('post_recherche_team_users', true,  array ("id_team" => $id_team1, "frequence" => 5, "nb" => 3, "niveau" => 2, "description" => "On veut du lourd !"));
// TODO: ne pas lister ses propres annonces
testParams('list_recherche_team_users',  true, array ("sport" => 1));
testParams('remove_recherche_team_users', true,  array ("id_team" => $id_team1));


