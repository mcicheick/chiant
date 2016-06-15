<?php

require_once '../config.php';
require_once '../requete.php';
require_once '../db.php';
require_once '../lib/medoo.php';

if (ENV != 'LOCAL')
	die('Not in local mode');

    $database = new medoo(array(
            // required
            'database_type' => 'mysql',
            'database_name' => DB_NAME,
            'server' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PWD,
            'charset' => 'utf8',

            )
    );


dispatchParams('newuser',  array ($prenom => 'user_test', $nom => 'name_atest', $email => 'ta@gueule', $tel => '0132', $hashmdp => 'hash'));
dispatchParams('login',  array ($email => 'ta@gueule', $hashmdp => 'rien'));
//dispatchParams('update_user_picture',  array ($photo => ?));
//dispatchParams('update_team_picture',  array ($photo => ?, $id_team => ?));
//dispatchParams('update_user_sport_prefs',  array ($football => ?, $basket => ?));
//dispatchParams('team_likes_team',  array ($id_bogoss => ?, $id_amoureux => ?));
//dispatchParams('join_team',  array ($id_team => ?));
//dispatchParams('unjoin_team',  array ($id_team => ?));
//dispatchParams('new_team',  array ($pseudo => ?, $sport => ?));
//dispatchParams('invites_team_team',  array ($id_invitant => ?, $id_invite => ?, $date => ?, $montant => ?));
//dispatchParams('invites_in_team',  array ($id_team => ?, $id_invite => ?));
//dispatchParams('post_chat_interne',  array ($id_team => ?, $msg => ?));
//dispatchParams('post_result_match',  array ($id_team_user => ?, $id_team2 => ?, $result => ?));
//dispatchParams('validate_result_match',  array ($id_result => ?));
//dispatchParams('post_chat_inter_teams',  array ($id_team_user => ?, $id_team_cible => ?, $msg => ?));
//dispatchParams('login',  array ($email => ?, $hashmdp => ?));
//dispatchParams('post_recherche_team_users',  array ($id_team => ?, $frequence => ?, $nb => ?, $niveau => ?, $description => ?));
//dispatchParams('remove_recherche_team_users',  array ($id_team => ?));
//dispatchParams('list_recherche_team_users',  array ($sport => ?));
//dispatchParams('list_teams_by_sport',  array ($sport => ?));
