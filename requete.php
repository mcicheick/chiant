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
   return (json_decode(file_get_contents( PRIVATE_DIR.'/routes',true), true));
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
    return makeErrorJson(ERR_ERROR, 'Error');
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


function dispatchReq( $params) {
   $req = $params['requete'];
   return dispatchParams($req, $params);
}

function dispatchParams($req, $params){  
    $routes = routage();

    if (!isset($routes[$req]))
        return error("unknow request : " . $obj->requete);

    $route = $routes[$req];

    try {
        $fun = $route['fun'];
        $args = $route['params'];
        $args_fun = array_values_from_keys($params,$args);
        // Si upload de fichier
        if (isset($route['file'])) {
            // le premièr argument de fun est les infos relatifs au fichier
            // sur le serveur
		//true;
           array_unshift($args_fun, $_FILES[$route['file']]);

	}
        return bret(call_user_func_array($fun,
                $args_fun));
    }
    catch (DbInsertUniqueExc $e) {
             return errDuplicateEntry();
    }
    catch (ForbiddenExc $e) {
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
        return raiseHermetiqueExc('False return value : '.$b, ERR_ERROR);
}

function login($email, $hashmdp) {
    if ($email == EMAIL_SUPERUSER && $hashmdp == PWD_SUPERUSER)
        $id_user = ID_SUPER_USER;
    else
        $id_user = C\check_credentials($email, $hashmdp);
    
    if (!$id_user)
        raiseBadCredentials();

    echo 'coucou';
    $_SESSION[SESSION_USERID_NAME] = $id_user;
    return true;
}

function checkLogged() {
    $id = null;
   if (isset ($_SESSION[SESSION_USERID_NAME]))
            $id = $_SESSION[SESSION_USERID_NAME];

    if ( MAGIC_USER) {
    if(isset($_REQUEST[MAGIC_PWD_FIELD])) {
        if ($_REQUEST[MAGIC_PWD_FIELD] == MAGIC_PWD)
           $id = $_REQUEST[SESSION_USERID_NAME];
    }
    }

    if (is_null($id))
        throw new NeedLoginExc();

    return $id;
}

function photo_user_path($iduser, $extension) {
    return PICTURES_DIR.'/u'.$iduser.'.'.$extension;
}

function photo_team_path($idteam, $extension) {
    return PICTURES_DIR.'/t'.$idteam.'.'.$extension;
}

function delete_photo($path) {
	// TODO: check that path is in dir (security check)
	// TODO: use realpath
	$rpath = basename(dirname(realpath($path)));

	if ($rpath == PICTURES_DIR)
		unlink($path);
	else
		raiseHermetiqueExc(ERR_ERROR,"Tentative de suppression d'un image dans le mauvais dossier ".$path. " dans ". $rpath);
}

interface updatePictureI {
    public function get_oldpath($id);
    public function update_db($id, $path);
    public function new_path($id, $ext);
}

function update_photo(updatePictureI $intf, $id) {
    $old_path = $intf->get_oldpath($id);
    if ($old_path)
	    delete_photo($old_path);

	$up_path = $photoparams['tmp_name'];
	$ext = pathinfo($photoparams['name'], PATHINFO_EXTENSION);
	$photopath = $intf->new_path($iduser, $ext);
	if (! move_uploaded_file($up_path, $photopath))
		raiseHermetiqueExc(ERR_ERROR, 'Impossible de déplacer le fichier uploadé');

	$intf->update_db($iduser, $photopath);

	return true;
}

class UpdatePUserI implements updatePictureI { 
        public function get_oldpath($iduser) { return I\get_u_photo($iduser); }
        public function update_db($iduser, $path) { return I\update_user_photo($iduser, $path); }
        public function new_path($iduser, $ext) { return photo_user_path($iduser, $ext); }
}

class UpdatePTeamI implements updatePictureI { 
        public function get_oldpath($id) { return I\get_t_photo($id); }
        public function update_db($id, $path) { return I\update_team_photo($id, $path); }
        public function new_path($id, $ext) { return photo_team_path($id, $ext); }
}

function update_u_picture($photoparams) {
    $iduser = checkLogged();
    update_photo( new UpdatePUserI(), $iduser);
}

function update_t_picture($photoparams, $id_team) {
    check_logged_u_t($id_team);
    update_photo( new UpdatePTeamI(), $id_team);
}

function register($prenom, $nom, $email, $tel, $mdp) {
    $iduser = I\create_user( $prenom, $nom, $email, $tel, $mdp);
    
    return true;
}

function update_user_sp_prefs($football, $basket){
    I\updateBAffinitesSports(checkLogged(), $football, $basket);
    return true;
}

function check_same_sport($idteam1, $idteam2) {
    if (!C\same_sport_t($idteam1, $idteam2))
        raiseMyExc('Teams have coucou different sport', ERR_FORBIDDEN);
}


function like_team_ch($id_bogoss, $id_amoureux) {
    check_logged_u_t($id_bogoss);
    
    check_same_sport($id_bogoss, $id_amoureux);

    return I\like_team($id_bogoss, $id_amoureux);
}

function newteam_byuser_p($pseudo, $sport) {
    I\create_team_by_user(checkLogged(),
        $pseudo,
        $sport);
    return true;
}

function check_user_team ($id_user, $id_team) {
        if (!C\belongs_to_u_t($id_user, $id_team)) 
            raiseMyExc('User does not belong to team', ERR_FORBIDDEN);
}


function t_invites_u_ch($id_team, $id_invite) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);
    // TODO:Vérifier que l'invité n'est pas déjà dans la team

     I\t_invites_u($id_user, $id_team, $id_invite);
    return true;
}


function join_team($id_team) {
    $id_user = checkLogged();
    I\delete_invitations_ut($id_user, $id_team);
    //TODO: CHeck that it is not already in the team
    //(normalement automatique : c'est la contrainte d'unicité)
    return I\u_joins_t($id_user, $id_team);
}

function unjoin_team($id_team) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);
    return I\u_unjoins_t($id_user, $id_team);
}

function check_logged_u_t ($id_team) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);
    return $id_user;
}

function t_invites_t($id_invitant, $id_invite, $date_rencontre, $montant) {
	// TODO: check smae sport
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
    I\u_post_msg_tt($id_user, $id_team_u, $id_team_cible, $msg);
    return true;
}

function new_t_annonce_us($id_team, $frequence, $nb, $niveau, $description) {
     check_logged_u_t($id_team);
    I\t_annonce_us($id_team, $frequence, $nb, $niveau, $description) ;
    return true;
}

 function del_t_annonce_us($id_team) {
 	 check_logged_u_t($id_team);
 	 return I\del_t_annonce_us($id_team);
 }
 
 function list_t_annonce_us($sport) {
 	return I\list_t_annonce_us($sport);
 }

 function list_t_sport($sport) {
    $id_user = checkLogged();
 	return array_merge( I\list_t_sport_coupcoeur($sport, $id_user), I\list_t_sport($sport));
 }
                 
function u_post_result($id_team_user, $id_team2, $result)  {
 check_logged_u_t($id_team_user);
	check_same_sport($id_team_user, $id_team2);
   return I\u_post_result($id_team_user, $id_team2, $result);
}

function u_validate_result($id_result)  {
   $id_user = checkLogged();
   C\belongs_to_u_match_t2($id_user, $id_result) ;
   return I\validate_result($id_result);
}

function update_position(){
    $id_user = check_logged();
    I\update_position($id_user);
    return true;
}

function update_last_connexion(){
    $id_user = check_logged();
    I\update_last_connexion($id_user);
    return true;
}
