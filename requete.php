<?php

session_start();


require_once 'lib.php';
require_once 'exceptions.php';
require_once 'config.php';
require_once 'dbinteraction.php';
require_once 'check.php';

use dbcheck as C;
use dbinteraction as I;


function routage() {
    return
        array('newuser'=>
                array('fun' => 'register', 
                      //'file' => 'photo',
                      'params' => array('prenom', 'nom', 'email', 'hashmdp')),
              'update_user_sport_prefs' =>
                array('fun' => 'update_user_sp_prefs', 
                      'params' => array('football', 'basket')),
              'team_likes_team' =>
                array('fun' => 'like_team_ch', 
                      'params' => array('id_bogoss', 'id_amoureux')),
              'join_team' =>
                array('fun' => 'join_team', 
                      'params' => array('id_team')),
              'new_team' =>
                array('fun' => 'newteam_byuser_p',
                      'params' => array('pseudo', 'sport')),
              'invites_team_team' =>
                array('fun' => 't_invites_t', 
                'params' => array('id_invitant', 'id_invite', 'date', 'montant')),
              'invites_in_team' =>
                array('fun' => 't_invites_u_ch', 
                'params' => array('id_team', 'id_invite')),
              'post_chat_interne' =>
                array('fun' => 'u_post_msg_t', 
                'params' => array('id_team', 'msg')),
              'post_chat_inter_teams' =>
                array('fun' => 'u_post_msg_tt', 
                'params' => array('id_team_user','id_team_cible', 'msg')),
              'login' =>
                array('fun' => 'login', 
                'params' => array('email', 'hashmdp'))

            );

}

/*
 * Pour créer un nouvel utilisateur
 * { requete = "newuser";
 *   content = {mdp="skjqhd"; prenom="kljkl"; nom="lkj"; email="kljklj"} }
 *
 * renvoie {answer = "OK"}
 * renveoi {answer = "NO", code = ERR_DUPLICATE_ENTRY, msg="Duplicate etnry}
 */



 function errLoginNeeded() { return makeErrorJson (ERR_NOLOGIN, "Login needed");}
function errForbidden      () { return makeErrorJson(ERR_FORBIDDEN,  "Forbidden");}
function errDuplicateEntry () { return makeErrorJson(ERR_DUPLICATE_ENTRY, 'Duplicate entry');}

function errError() {
    return makeErrorJson(array("answer" => "NO", "code" => ERR_ERROR, 'msg' => 'Error'));
}

 function makeErrorJson($code, $msg) {
     return array('answer' => 'NO', "code" => $code, 'msg' => $msg);
 }

function exc_to_jsonErr($e) {
     return makeErrorJson($e->getCode(), $e->getMessage());    
}

 
function error($msg) {
    return (array("answer" => "Error", "msg" =>$msg));
}

function dispatchJson($jsonStr){
    $obj = json_decode($jsonStr);
    if ($obj == null)
        return error('invalid json');
    switch ($obj->requete) {
    case "newuser":
        if (register($obj->content->prenom, $obj->content->nom, $obj->content->email, $obj->content->hashmdp))
            return (array("answer" => "OK"));
        break;
    default:
        return error("unknow request : " . $obj->requete);
    }
}

function dispatchReq($params){  
    $req = $params['requete'];
    $routes = routage();

    if (!isset($routes[$req]))
        return error("unknow request : " . $obj->requete);

    $route = $routes[$req];

    try {
        $fun = $route['fun'];
        $args = $route['params'];
        $args_fun = array_values_from_keys($params,$args);
        // Si upload de fichier
        if (isset($route['file']))
            // le premièr argument de fun est les infos relatifs au fichier
            // sur le serveur
		true;
           //array_unshift($args_fun, $_FILE[$route['file']]);

        return bret(call_user_func_array($fun,
                $args_fun));
    }
    catch (DbInsertUnique $e) {
             return errDuplicateEntry();
    }
    catch (Forbidden $e) {
        return errForbidden();
    }
    catch (NeedLoginExc $e) {
        return errLoginNeeded();
    }
    catch (MyExc $e) {
        return exc_to_jsonErr($e);
    }
    catch(HermetiqueExc $e) {
        if (HERMETIQUE)
            return errError();
        else
            return exc_to_jsonErr($e);


    }
}

function bret($b) {
    if (is_array($b))
        return array('answer' => 'OK', 'contents' => $b);

    if ($b)
        return (array("answer" => "OK"));
    else
        return errError();
}

function login($email, $hashmdp) {
    if ($email == EMAIL_SUPERUSER && $hashmdp == PWD_SUPERUSER)
        $id_user = ID_SUPER_USER;
    else
        $id_user = C\check_credentials($email, $hashmdp);
    
    if (!$id_user)
        raiseBadCredentials();

    $_SESSION[SESSION_USERID_NAME] = $id;
    return true;
}

function checkLogged() {
    $id = null;
    if (! MAGIC_USER) {
        if (isset ($_SESSION[SESSION_USERID_NAME]))
            $id = $_SESSION[SESSION_USERID_NAME];
    }
    elseif(isset($_REQUEST[MAGIC_PWD_FIELD])) {
        if ($_REQUEST[MAGIC_PWD_FIELD] == MAGIC_PWD)
           $id = $_REQUEST[SESSION_USERID_NAME];
    }

    if (is_null($id))
        throw new NeedLoginExc();

    return $id;
}

function photo_user_path($iduser, $extension) {
    return 'pictures/u'.$iduser.'.'.$extension;
}

function register(/*$photoparams,*/$prenom, $nom, $email, $mdp) {
    $iduser = I\create_user( $prenom, $nom, $email, $mdp);
    /*
    if ($photoparams) {
        $up_path = $photoparams['tmp_name'];
        $ext = pathinfo($photoparams['name'], PATHINFO_EXTENSION);
        $photopath = photo_user_path($iduser, $ext);
        if (! move_uploaded_file($up_path, $photopath))
            raiseHermetiqueExc(ERR_ERROR, 'Impossible de déplacer le fichier uploadé');
        I\update_user_photo($iduser, $photopath);
    }*/
    return true;
}

function update_user_sp_prefs($football, $basket){
    return I\updateBAffinitesSports(checkLogged(), $params['football'], $params['basket']);
}


function like_team_ch($id_bogoss, $id_amoureux) {
    check_logged_u_t();
    
    if (!C\same_sport_t($id_bogoss, $id_amoureux));
        raiseMyExc(ERR_FORBIDDEN, 'Teams have different sport');

    return I\like_team($id_bogoss, $id_amoureux);
}

function newteam_byuser_p($pseudo, $sport) {
    return I\create_team_by_user(checkLogged(),
        $pseudo,
        $sport);
}

function check_user_team ($id_user, $id_team) {
        if (!C\belongs_to_u_t($id_user, $id_team)) 
            raiseMyExc(ERR_FORBIDDEN, 'User does not belong to team');
}


function t_invites_u_ch($id_team, $id_invite) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);
    // TODO:Vérifier que l'invité n'est pas déjà dans la team

    return I\t_invites_u($id_user, $id_team, $id_invite);
}


function join_team($id_team) {
    $id_user = checkLogged();
    I\delete_invitations_ut($id_user, $id_team);
    //TODO: CHeck that it is not already in the team
    //(normalement automatique : c'est la contrainte d'unicité)
    return I\u_joins_t($id_user, $id_team);
}


function check_logged_u_t ($id_team) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);
    return $id_user;
}

function t_invites_t($id_invitant, $id_invite, $date_rencontre, $montant) {
    check_logged_u_t($id_invitant);
    return I\t_invites_t($id_invitant, $id_invite, $date_rencontre, $montant);
}

function u_post_msg_t ($id_team, $msg) {
    $id_user = check_logged_u_t($id_team);
    return I\u_post_msg_t($id_user, $id_team, $msg);
}

function u_post_msg_tt ($id_team_u, $id_team_cible, $msg) {
    $id_user = check_logged_u_t($id_team_u);
    //TODO: vérifier quoi d'autres ? (
    return I\u_post_msg_tt($id_user, $id_team_u, $id_team_cible, $msg);
}
