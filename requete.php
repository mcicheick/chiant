<?php

session_start();


require_once 'lib.php';
require_once 'exceptions.php';
require_once 'config.php';
require_once 'dbinteraction.php';
require_once 'check.php';
require_once 'envoyer_mail.php';
require_once 'routes.php';

use dbcheck as C;
use dbinteraction as I;
use envoyer_mail as E;



/*
 * Pour crÃ©er un nouvel utilisateur
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
   unset($params['requete']);
unset($params[MAGIC_PWD_FIELD]);
unset($params[MAGIC_USERID_NAME]);
   return dispatchParams($req, $params);
}

function array_values_from_keys($arr, $keys) {
    return array_map(function($x) use ($arr) { return $arr[$x]; }, $keys);
}

function dispatchParams($req, $params){  
    $routes = routage();

    if (!isset($routes[$req]))
        return error("unknow request : " . $routes[$req]);

    $route = $routes[$req];

    try {
        $fun = $route['fun'];
        $args = $route['params'];

	$keys = array_keys($params);
	sort($keys);
	sort($args);
	//var_dump($keys);
	//var_dump($args);
	//var_dump(array_values($args) == array_values($keys));

	if ($args != $keys)
		raiseHermetiqueExc(
			"Request $req : given arguments ".join($keys,', ').
			" ; expected arguments ". join(($args),', '),
				ERR_REQ_WRONG_ARGS);


	if (isset($route['args_as_array'])) {

		// Si upload de fichier
		if (isset($route['file']))
			$params['file'] = $_FILES[$route['file']];
		return bret($fun( $params));
	}
	else {
		$args_fun = array_values_from_keys($params,$route['params']);
		// Si upload de fichier
		if (isset($route['file'])) {
			array_unshift($args_fun, $_FILES[$route['file']]);

		}
	     $val = call_user_func_array($fun, $args_fun);
	   $fval = bret($val);
		return $fval;
	}

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
    if (is_array($b)) {
	    if (isset($b['msg']))
		    echo "COUCOU BOLOSS";
        return array('answer' => 'OK', 'contents' => $b);
    }

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

    $_SESSION[SESSION_USERID_NAME] = $id_user;
    return true;
}

function loginFB($mail,$accesstoken){
  $ch=curl_init('http://graph.facebook.com/v2.2/debug_token?input_token='.urlencode($accesstoken));
  $output=curl_exec($ch);
  curl_close($ch);
  $output=json_decode($output);
	$appid=$output->data->app_id;
	$email=$output->data->email;
	if($appid==APP_ID && $email==$mail){
		$id_user = C\check_credentials($email, null);
	}

  $_SESSION[SESSION_USERID_NAME] = $id_user;
	return(true);

}

function checkLogged() {
    $id = null;
   if (isset ($_SESSION[SESSION_USERID_NAME]))
            $id = $_SESSION[SESSION_USERID_NAME];

    if ( MAGIC_USER) {
    if(isset($_REQUEST[MAGIC_PWD_FIELD])) {
        if ($_REQUEST[MAGIC_PWD_FIELD] == MAGIC_PWD)
           $id = $_REQUEST[MAGIC_USERID_NAME];
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
	if (!file_exists($path))
	   return;

	$rpath = basename(dirname(realpath($path)));

	if ($rpath == PICTURES_DIR)
		unlink($path);
	else
		raiseHermetiqueExc("Tentative de suppression d'un image dans le mauvais dossier ".$path. " dans ". $rpath, ERR_ERROR);
}

abstract class updatePictureI {
    var $id;
    public function __construct($id) {
    $this -> id = $id;
    }
    abstract public function get_oldpath();
    abstract public function update_db($path);
    abstract public function new_path($ext);
}

function update_photo($photoparams, updatePictureI $intf) {
    $old_path = $intf->get_oldpath();
    if ($old_path)
	    delete_photo($old_path);

	$up_path = $photoparams['tmp_name'];
	$ext = pathinfo($photoparams['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($ext), explode(' ',IMAGES_EXT)))
		raiseHermetiqueExc("Le fichier a l'extension ".$ext. " : ce n'est pas une image", ERR_ERROR);

	$photopath = $intf->new_path($ext);
	if (! move_uploaded_file($up_path, $photopath))
		raiseHermetiqueExc('Impossible de dÃ©placer le fichier uploadÃ©', ERR_ERROR);

	$intf->update_db($photopath);

	return true;
}


class UpdatePUserI extends updatePictureI { 
        public function get_oldpath() { return I\get_u_photo($this->id); }
        public function update_db($path) { return I\update_user_photo($this->id, $path); }
        public function new_path($ext) { return photo_user_path($this->id, $ext); }
}

class UpdatePTeamI extends updatePictureI { 
        public function get_oldpath() { return I\get_t_photo($this->id); }
        public function update_db($path) { return I\update_team_photo($this->id, $path); }
        public function new_path($ext) { return photo_team_path($this->id, $ext); }
}

function update_u_picture($photoparams) {
    $iduser = checkLogged();
    return update_photo($photoparams, new UpdatePUserI($iduser));
}

function update_t_picture($photoparams, $id_team) {
    check_logged_u_t($id_team);
    return update_photo($photoparams, new UpdatePTeamI($id_team));
}

function register($prenom, $nom, $email, $tel, $mdp,$latitude,$longitude,$city,$country,$accesstoken) {
    if ($mdp!=null){
    $cle = md5(microtime(TRUE)*100000);

    $iduser = I\create_user_inactif( $prenom, $nom, $email, $tel, $mdp,$cle,$latitude,$longitude,$city,$country);
    E\send_mail_inscription($email,$iduser,$cle);
}
else{
    $cle=null;
    $ch=curl_init('http://graph.facebook.com/v2.2/debug_token?input_token='.urlencode($accesstoken));
    $output=curl_exec($ch);
    curl_close($ch);
    $output=json_decode($output);
    $appid=$output->data->app_id;
    $email=$output->data->email;
    if($appid==APP_ID && $email==$mail){
      $iduser = I\create_user( $prenom, $nom, $email, $tel, $mdp,$cle,$latitude,$longitude,$city,$country);
    }

    
}

    return true;
}

function isregistered($email){
  $a=I\isRegistered($email);
  $a['isregistered']=($a['isregistered']==1);
  return(array($a['isregistered']));
}

function update_user_sp_prefs($prefs){

	$sports = listSports();
	$liste_total = array_keys($sports);
	$liste_positive = array();

	foreach ($sports as $id_sport => $sport) {
		if ($prefs[$sport])
			$liste_positive[] = $id_sport;
	}
	$id_user = checkLogged();

	// enlève les préférences de basket  et de football
	I\removeAffinitesSports($id_user, $liste_total);
	// mets les bonnes à la place
	I\addAffinitesSports($id_user, $liste_positive);
    return true;
}

function check_same_sport($idteam1, $idteam2) {
    if (!C\same_sport_t($idteam1, $idteam2))
        raiseMyExc('Teams have different sport', ERR_FORBIDDEN);
}


// Returns if the other one has liked the team
function like_team_ch($id_bogoss, $id_amoureux) {
    check_logged_u_t($id_bogoss);
    
    check_same_sport($id_bogoss, $id_amoureux);

    I\like_team($id_bogoss, $id_amoureux);
    return array('match' => C\like_team($id_amoureux, $id_bogoss));
}

function newteam_byuser_p($pseudo, $sport,$latitude,$longitude,$city,$country) {
    return (I\create_team_by_user(checkLogged(),
        $pseudo,
        $sport,$latitude,$longitude,$city,$country));
}

function check_user_team ($id_user, $id_team) {
        if (!C\belongs_to_u_t($id_user, $id_team)) 
            raiseMyExc('User does not belong to team', ERR_FORBIDDEN);
}


function t_invites_u_ch($id_team, $id_invite) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);
    // TODO:VÃ©rifier que l'invitÃ© n'est pas dÃ©jÃ  dans la team

     I\t_invites_u($id_user, $id_team, $id_invite);
    return true;
}


function join_team($id_team) {
    $id_user = checkLogged();
    if (C\belongs_to_u_t_same_sport($id_user, $id_team))
      raiseMyExc('User already belongs to a team with the same sport', ERR_ERROR);
    I\delete_invitations_ut($id_user, $id_team);
    //TODO: CHeck that it is not already in the team
    //(normalement automatique : c'est la contrainte d'unicitÃ©)
    I\u_joins_t($id_user, $id_team);
    return true;
}

function unjoin_team($id_team) {
    $id_user = checkLogged();
    check_user_team($id_user, $id_team);

    I\u_unjoins_t($id_user, $id_team);
    return true;
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

function u_post_msg_user_team ($id_team, $msg) {
	// TODO : check that $id_team a posté une annonce (pour éviter le flood)
    $id_user = checkLogged();
    return I\u_post_msg_user_team($id_user, $id_team, $msg);
}

function u_post_msg_team_user ($id_team, $id_user_cible, $msg) {
	// TODO : check that $id_team a posté une annonce (pour éviter le flood)
    $id_user = check_logged_u_t($id_team);
    return I\u_post_msg_team_user($id_user, $id_user_cible, $id_team, $msg);
}

function u_post_msg_tt ($id_team_u, $id_team_cible, $msg) {
    $id_user = check_logged_u_t($id_team_u);
    //TODO: vÃ©rifier quoi d'autres ? (
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
 	 I\del_t_annonce_us($id_team);
     return true;
 }
 
 function list_t_annonce_us($sport) {
 	return I\list_t_annonce_us($sport);
    
 }

 function list_t_sport($sport) {
    $id_user = checkLogged();
 	return array_merge( I\list_t_sport_coupcoeur($sport, $id_user), I\list_t_sport($sport));
 }
                 
function u_post_result($id_team_user, $id_team2, $result, $fairplay, $avis)  {
 check_logged_u_t($id_team_user);
	check_same_sport($id_team_user, $id_team2);
   I\u_post_result($id_team_user, $id_team2, $result, $fairplay, $avis);
   return true;
}

function u_validate_result($id_result, $fairplay, $avis)  {
   $id_user = checkLogged();
   if (!C\belongs_to_u_match_t2($id_user, $id_result) )
      raiseHermetiqueExc('User does not belongs to target team', ERR_ERROR);
   I\validate_result($id_result, $fairplay, $avis);
   return true;
}

function update_position($latitude,$longitude,$city,$country){
    $id_user = checkLogged();
    I\update_position($id_user,$latitude,$longitude,$city,$country);
    return true;
}

function update_last_connexion(){
    $id_user = checkLogged();
    I\update_last_connexion($id_user);
    return true;
}


function update_password($email,$password){
    return(I\update_password($email,sha1($password)));
}

function send_mail_change_password($email){
    $cle=I\get_cle_email($email);
    E\send_mail_change_password($email,$cle);
    return(true);
    }

function send_mail_inscription($email,$iduser,$cle){
    E\send_mail_inscription($email,$iduser,$cle);
    return(true);
}


function get_historique_team($id_team, $limit)  {
   $list = I\list_historique_team($id_team, $limit);
   $stats = I\get_stat_team($id_team);
   $stats['historique'] = $list;
   return $stats;
}

function list_t_classements($limit)  {
   $sportsa = listSports();
   $list_sports = array();
   foreach ($sportsa as $idsport => $sport) {
	$list_sports[$sport] = I\list_t_classement_s($limit, $idsport);
   }
   return $list_sports;
}

function list_waiting_results($id_team)  {
   check_logged_u_t($id_team);
   return I\list_waiting_results($id_team);
}

function list_prefs_sport()  {
   return I\list_prefs_sport(checkLogged());
}

function signale_t_team($id_team_signalante, $id_team_signale)  {
    check_logged_u_t($id_team_signalante);
   I\signale_t_team($id_team_signalante, $id_team_signale);
    return true;
}


function list_msg_chat_interne($id_team, $date_last)  {
    check_logged_u_t($id_team);
   return I\list_msg_chat_interne($id_team, $date_last);
}

function list_msg_chat_inter($id_team_user, $id_team2, $date_last)  {
    check_logged_u_t($id_team_user);
   $ret= I\list_msg_chat_inter($id_team_user, $id_team2, $date_last);
    return $ret;
}

function list_msg_chat_user_team($id_team, $date_last)  {
   $id_user = checkLogged();
   return I\list_msg_chat_user_team($id_user, $id_team, $date_last);
}
function list_msg_chat_team_user($id_team_user, $id_user, $date_last)  {
    check_logged_u_t($id_team_user);
   return I\list_msg_chat_user_team($id_user, $id_team_user, $date_last);
}


function last_chat_msg()  {
   $id_user = checkLogged();
   return ch_list_chat_msg($id_user);
}

function ch_list_chat_msg($id_user) {
   $ret = array();
   $ret['interne'] = I\last_chat_interne_msg($id_user);
   $ret['inter']     = I\last_chat_inter_msg($id_user);
   $ret['user_team'] = I\last_chat_user_team($id_user);
   $ret['team_user'] = I\last_chat_team_user($id_user);
   return $ret;
}
