<?php

namespace chatfcm;

// Pour identifier les chat dans les réponses de fcm
define('IDENT_CHAT_INTER', 'team_team');
define('IDENT_CHAT_USER_TEAM', 'user_team');
define('IDENT_CHAT_INTERNE', 'interne_team');

// Nom de la propriété json
define('JSON_TYPE_CHAT', 'chat');

require_once 'fcm.php';

use dbinteraction as I;


function data_team_msg($id_user, $id_team, $msg){
	return array(JSON_TYPE_CHAT =>IDENT_CHAT_INTERNE,
		'id_user' => $id_user,
		'id_team' => $id_team,
		'msg' => $msg);
}

function data_inter_team_msg($id_user, $id_team_u, $id_team_cible, $msg) {
	return array(JSON_TYPE_CHAT => IDENT_CHAT_INTER,
		'id_user' => $id_user,
		'id_team_user' => $id_team_u,
		'id_team_cible' => $id_team_cible,
		'msg' => $msg);
}

function data_team_user_msg($id_user, $id_user_cible, $id_team, $msg) {
	return array(JSON_TYPE_CHAT => IDENT_CHAT_USER_TEAM,
		'id_user' => $id_user,
		'id_user_cible' => $id_user_cible,
		'id_team' => $id_team,
		'msg' => $msg);
}

function data_user_team_msg($id_user, $id_team, $msg) {
	return array(JSON_TYPE_CHAT => IDENT_CHAT_USER_TEAM,
		'id_user' => $id_user,
		'id_user_cible' => $id_user,
		'id_team' => $id_team,
		'msg' => $msg);
}

function notify_team_msg($id_user, $id_team, $msg) {
  $tokens = I\get_tokens_team_except($id_team, $id_user);
  if (count($tokens) > 0)
  	sendMessageFCM(data_team_msg($id_user, $id_team, $msg),$tokens);
}

function notify_team_inter_msg($id_user, $id_team_u, $id_team_cible, $msg){
  $tokens = I\get_tokens_2teams_except($id_team_u, $id_team_cible, $id_user);
  if (count($tokens) > 0)
  	sendMessageFCM(data_inter_team_msg($id_user, $id_team_u, $id_team_cible, $msg),$tokens);
}
function notify_team_user_msg($id_user, $id_user_cible, $id_team, $msg) {
  $tokens = I\get_tokens_team_except($id_team, $id_user);
  $token_cible =  I\get_token_user($id_user_cible);
  if ($token_cible)
	  $tokens[] = $token_cible;

  if (count($tokens) > 0)
  	sendMessageFCM(data_team_user_msg($id_user, $id_user_cible, $id_team, $msg), $tokens);
}
function notify_user_team_msg($id_user, $id_team, $msg) {
  $tokens = I\get_tokens_team_except($id_team);
  if (count($tokens) > 0)
  	sendMessageFCM(data_user_team_msg($id_user, $id_team, $msg),$tokens);
}
