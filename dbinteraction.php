<?php
namespace dbinteraction;

require_once 'db.php';
require_once 'fieldsbdd.php';
require_once 'sports.php';
require_once 'geolocalisation.php';

require_once 'lib.php';




function addAffinitesSports($iduser, $id_sports) {
    $vals = array();
    $valeurs_str = '';

    foreach ($id_sports as $id_sport) {
       $valeurs_str .= ", (?, ?)";
       $vals[] = $iduser;
       $vals[] = $id_sport;
    }

    if (!$valeurs_str)
	    return true;

    $valeurs_str = substr($valeurs_str,1);


    $req = sprintf ("INSERT INTO %s (%s, %s) VALUES %s",
	    		TBL_LIEN_PREFS_SPORTS_USER,
			LIEN_TEAM_USERS_ID_USER,
			LIEN_PREFS_SPORTS_USER_ID_SPORT,
			$valeurs_str);

    return execCheck($req, $vals);
}

function removeAffinitesSports($iduser, $id_sports) {
    $vals = $id_sports;
    $vals[] = $iduser;
    $marks = substr( str_repeat(', ?', count($id_sports)),1);
    return deleteDbWhStr(TBL_LIEN_PREFS_SPORTS_USER, LIEN_PREFS_SPORTS_USER_ID_SPORT." IN ($marks) AND ".LIEN_TEAM_USERS_ID_USER.' = ?', $vals) ;
}

function list_prefs_sport($id_user)  {
       $stmt= oselect()->addCol(REF_SPORTS_NOM, 'R')
                ->from(TBL_LIEN_PREFS_SPORTS_USER, 'L')
		->joinp(TBL_REF_SPORTS, 'R', 'R',
			REF_SPORTS_ID, 'L', LIEN_PREFS_SPORTS_USER_ID_SPORT)
		->andWhereEqp('L', LIEN_PREFS_SPORTS_USER_ID_USER, $id_user)
		->execute();
    return $stmt->fetchall(\PDO::FETCH_COLUMN);
}


function create_team($pseudo, $sport,$latitude,$longitude,$city,$country) {
    return insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>$pseudo, TEAMS_SPORT => $sport,TEAMS_LATITUDE=>$latitude,TEAMS_LONGITUDE=>$longitude,TEAMS_CITY=>$city,TEAMS_COUNTRY =>$country));
}

function create_user($prenom, $nom, $email, $tel, $mdp,$cle,$latitude,$longitude,$city,$country) {
    return insertDb(TBL_USERS, array(USERS_NOM => $nom, USERS_MAIL => $email, USERS_PASSWORD => $mdp, USERS_PRENOM => $prenom, USERS_TEL => $tel,USERS_CLE =>$cle,USERS_LONGITUDE =>$longitude,USERS_LATITUDE =>$latitude,USERS_COUNTRY =>$country,USERS_CITY =>$city));
}


function isregistered($email) {
	$db=getDb();
	$requete ='SELECT COUNT(*) as isregistered FROM '.TBL_USERS.'  WHERE '.  USERS_MAIL.'= ?';
   //var_dump($requete);
   $stmt= $db->prepare($requete);

    if ($stmt->execute(array($email)))
	return $stmt->fetch(\PDO::FETCH_ASSOC);
}

function create_team_by_user($id_user, $pseudo, $sport,$latitude,$longitude,$city,$country) {
    if (! create_team($pseudo, $sport,$latitude,$longitude,$city,$country))
	return false;

    $id_team = getDb()->lastInsertId();
    //echo'Nouvelle team : '.$id_team."\n";

    u_joins_t($id_user, $id_team);
    return array('id_team'=>$id_team,'id_sport'=>$sport,"name"=>$pseudo);
}

function like_team($id_bogoss, $id_amoureux) {
    return insertDb(TBL_COUP_COEURS_EQUIPES, array(COUP_COEURS_EQUIPES_ID_BOGOSS => $id_bogoss, COUP_COEURS_EQUIPES_ID_AMOUREUX => $id_amoureux));
}

function t_invites_u($id_user, $id_team, $id_invite) {
    return insertDb(TBL_INVITATIONS_EQUIPE_JOUEUR,
	array(INVITATIONS_EQUIPE_JOUEUR_ID_INVITANT => $id_user,
	      INVITATIONS_EQUIPE_JOUEUR_ID_INVITE   => $id_invite,
	      INVITATIONS_EQUIPE_JOUEUR_ID_EQUIPE   => $id_team));
}

function u_joins_t($id_user, $id_team) {
    return insertDb(TBL_LIEN_TEAM_USERS, array(LIEN_TEAM_USERS_ID_TEAM => $id_team, LIEN_TEAM_USERS_ID_USER => $id_user));
}

function u_unjoins_t($id_user, $id_team) {
    return deleteDbArr(TBL_LIEN_TEAM_USERS, array(LIEN_TEAM_USERS_ID_TEAM => $id_team, LIEN_TEAM_USERS_ID_USER => $id_user));
}

function delete_invitations_ut($id_user, $id_team) {
    return deleteDbArr(TBL_INVITATIONS_EQUIPE_JOUEUR,
        array(INVITATIONS_INTER_EQUIPES_ID_INVITE => $id_user,
        INVITATIONS_EQUIPE_JOUEUR_ID_EQUIPE => $id_team));
}
function t_invites_t($id_invitant, $id_invite, $date_rencontre, $montant) {
    return insertDb(TBL_INVITATIONS_INTER_EQUIPES, 
	array(INVITATIONS_INTER_EQUIPES_ID_INVITE => $id_invite,
	INVITATIONS_INTER_EQUIPES_ID_INVITANT => $id_invitant,
	INVITATIONS_INTER_EQUIPES_DATE_RENCONTRE => $date_rencontre,
	INVITATIONS_INTER_EQUIPES_MONTANT => $montant));
}

function u_post_msg_t ($id_user, $id_team, $msg) {
    return insertDb(TBL_CHAT_INTERNE_EQUIPE, array(
	CHAT_INTERNE_EQUIPE_CONTENT => $msg,
	CHAT_INTERNE_EQUIPE_ID_USER => $id_user,
	CHAT_INTERNE_EQUIPE_ID_EQUIPE => $id_team));
}

function u_post_msg_user_team ($id_user, $id_team, $msg) {
    return insertDb(TBL_CHAT_USER_TEAM, array(
	CHAT_USER_TEAM_CONTENT => $msg,
	CHAT_USER_TEAM_ID_USER_CIBLE => $id_user,
	CHAT_USER_TEAM_ID_USER_MSG => $id_user,
	CHAT_USER_TEAM_ID_EQUIPE => $id_team));
}

function u_post_msg_team_user ($id_user, $id_user_cible, $id_team, $msg) {
    return insertDb(TBL_CHAT_USER_TEAM, array(
	CHAT_USER_TEAM_CONTENT => $msg,
	CHAT_USER_TEAM_ID_USER_CIBLE => $id_user_cible,
	CHAT_USER_TEAM_ID_USER_MSG => $id_user,
	CHAT_USER_TEAM_ID_EQUIPE => $id_team));
}


function u_post_msg_tt ($id_user, $id_team1, $id_team2, $msg) {
    return insertDb(TBL_CHAT_INTER_EQUIPE, array(
	CHAT_INTER_EQUIPE_CONTENT => $msg,
	CHAT_INTER_EQUIPE_ID_USER => $id_user,
	CHAT_INTER_EQUIPE_ID_EQUIPE_U => $id_team1,
	CHAT_INTER_EQUIPE_ID_EQUIPE2 => $id_team2));
}

function t_annonce_us($id_team, $frequence, $nb, $niveau, $description) {
    return insertDb(TBL_OFFRE_TEAM_USERS, array(
	OFFRE_TEAM_USERS_ID_TEAM => $id_team,
	OFFRE_TEAM_USERS_DESCRIPTION => $description,
	OFFRE_TEAM_USERS_NB => $nb,
    OFFRE_TEAM_USERS_NIVEAU => $niveau,
    OFFRE_TEAM_USERS_FREQUENCE => $frequence));
}

function list_t_annonce_us($sport) {
   $db = getDb();

   $requete ='SELECT o.* FROM '.TBL_OFFRE_TEAM_USERS.' as o JOIN '.TBL_TEAMS. ' as t ON t.'.  TEAMS_ID.'= o.'.OFFRE_TEAM_USERS_ID_TEAM.' WHERE t.sport=?';
   //var_dump($requete);
   $stmt= $db->prepare($requete);

    if ($stmt->execute(array($sport)))
	return $stmt->fetchall(\PDO::FETCH_ASSOC);
    else
	return false;
}

function list_t_sport_coupcoeur($sport, $iduser) {
   $db = getDb();

   $requete ='SELECT t.* FROM '.TBL_TEAMS.' as t JOIN '.TBL_COUP_COEURS_EQUIPES. ' as c ON t.'.  TEAMS_ID.'= c.'.COUP_COEURS_EQUIPES_ID_BOGOSS.
   ' JOIN '.TBL_LIEN_TEAM_USERS. ' AS l ON l.'.LIEN_TEAM_USERS_ID_TEAM.' = c.'. COUP_COEURS_EQUIPES_ID_AMOUREUX.' WHERE t.'.TEAMS_SPORT.' =? '.
   ' AND l.'.LIEN_TEAM_USERS_ID_USER.' = ? LIMIT 10';
   //var_dump($requete);
   $stmt= $db->prepare($requete);

    if ($stmt->execute(array($sport, $iduser)))
	return $stmt->fetchall(\PDO::FETCH_ASSOC);
    else
	return false;
}

function list_t_sport($sport) {
   $db = getDb();

   $requete ='SELECT t.* FROM '.TBL_TEAMS.' as t WHERE t.'. TEAMS_SPORT. ' =? LIMIT 10 ';
   //var_dump($requete);
   $stmt= $db->prepare($requete);

    if ($stmt->execute(array($sport)))
	return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    else
	return false;
}


function del_t_annonce_us($idteam) {
   return deleteDbArr(TBL_OFFRE_TEAM_USERS, array(OFFRE_TEAM_USERS_ID_TEAM => $idteam));
}

function get_u_photo($id_user) {
	return selectId(TBL_USERS, array(USERS_PICTURE_FILE), $id_user)->fetchColumn();
}

function get_t_photo($id_team) {
	return selectId(TBL_TEAMS, array(TEAMS_PICTURE_FILE), $id_team)->fetchColumn();
}

function update_user_photo($iduser, $path) {
    $params = array(USERS_PICTURE_FILE => $path);
    return updateDb(TBL_USERS, $params,$iduser);
}

function update_team_photo($idteam, $path) {
    $params = array(TEAMS_PICTURE_FILE => $path);
    return updateDb(TBL_TEAMS, $params,$idteam);
}

function validate_result($id_result, $fairplay, $avis) {
   return updateDb(TBL_MATCHES, array(MATCHES_VALIDE => 1,
    MATCHES_AVIS_SUR1 => $avis,
    MATCHES_FAIRPLAY_SUR1 => $fairplay
   ),$id_result);
}

function u_post_result($id_team_user, $id_team2, $result, $fairplay, $avis)  {
    return insertDb(TBL_MATCHES, array(
	MATCHES_ID_TEAM1 => $id_team_user,
	MATCHES_ID_TEAM2 => $id_team2,
	MATCHES_RESULTAT => $result,
    MATCHES_AVIS_SUR2 => $avis,
    MATCHES_FAIRPLAY_SUR2 => $fairplay
    
    )
    
    );
}

/*pour updater les positions de l'Ã©quipe toutes les variations de 1 km*/

function update_position($id_user,$latitude,$longitude,$city,$country){
    updateDb(TBL_USERS, array(USERS_LATITUDE=> $latitude,USERS_LONGITUDE=>$longitude,USERS_CITY=>$city,USERS_COUNTRY=>$country ),$id_user );
    $req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_TEAM), array(LIEN_TEAM_USERS_ID_USER =>$id_user ));
    while($donnees=$req->fetch()) update_positionTeam($donnees[strtolower(LIEN_TEAM_USERS_ID_TEAM)]);
    $req->closeCursor();
    return true;
}

function update_positionTeam($idteam){   

$req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_USER), array(LIEN_TEAM_USERS_ID_TEAM =>$idteam ));
$reponse=$req->fetchall();

$reponse=array_column($reponse, strtolower(LIEN_TEAM_USERS_ID_USER));


$marks = substr( str_repeat(', ?', count($reponse)),1);
$req2=selectDbWhStr(TBL_USERS, array(USERS_LATITUDE,USERS_LONGITUDE), USERS_ID." IN ($marks) ", $reponse);
$reponse=$req2->fetchall();

$solution=calcule_barycentre($reponse);

$geo=getCityCountry($solution[strtolower(TEAMS_LATITUDE)],$solution[strtolower(TEAMS_LONGITUDE)]);

updatepositionDB($idteam,$solution[strtolower(TEAMS_LATITUDE)],$solution[strtolower(TEAMS_LONGITUDE)],$geo[strtolower(TEAMS_CITY)],$geo[strtolower(TEAMS_COUNTRY)]);

}


function updatepositionDB($id_team,$latitude,$longitude,$city,$country){

return(updateDb(TBL_TEAMS, array(TEAMS_LATITUDE => $latitude,TEAMS_LONGITUDE=>$longitude,TEAMS_CITY=>$city, TEAMS_COUNTRY=>$country ),$id_team ));

}



/*fonction qui actualise la date de derniÃ¨re connexion des Ã©quipes*/
function update_last_connexion($id_user){
    $req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_TEAM), array(LIEN_TEAM_USERS_ID_USER =>$id_user ));
    while($donnees=$req->fetch()) updateDb(TABLE_TEAMS,array(TEAM_LAST_CONNEXION => date('d/m/Y')), $donnees[strtolower(LIEN_TEAM_USERS_ID_TEAM)]);
    $req->closeCursor();
    return(true);
}


function confirmation_inscription($email,$cle){
  $req=selectDbArr(TBL_USERS_INACTIF, array('*'), array(USERS_INACTIF_MAIL => $email ,USERS_INACTIF_CLE => $cle));
  $donnees = $req->fetch();
  $nom=$donnees[strtolower(USERS_INACTIF_NOM)];
  $email=$donnees[strtolower(USERS_INACTIF_MAIL)];
  $mdp=$donnees[strtolower(USERS_INACTIF_PASSWORD)];
  $prenom=$donnees[strtolower(USERS_INACTIF_PRENOM)];
  $tel=$donnees[strtolower(USERS_INACTIF_TEL)];
  $cle=$donnees[strtolower(USERS_INACTIF_CLE)];
  $latitude=$donnees[strtolower(USERS_INACTIF_LATITUDE)];
  $longitude=$donnees[strtolower(USERS_INACTIF_LONGITUDE)];
  $country=$donnees[strtolower(USERS_INACTIF_COUNTRY)];
  $city=$donnees[strtolower(USERS_INACTIF_CITY)];
  return(insertDb(TBL_USERS, array(USERS_NOM => $nom, USERS_MAIL => $email, USERS_PASSWORD => $mdp, USERS_PRENOM => $prenom, USERS_TEL => $tel,USERS_CLE =>$cle,USERS_LONGITUDE =>$longitude,USERS_LATITUDE =>$latitude,USERS_CITY =>$city,USERS_COUNTRY =>$country)));

}

function get_cle_email($email){
  $req=selectDbArr(TBL_USERS, array(USERS_CLE), array(USERS_MAIL => $email ));
  $donnees=$req->fetch();
  return($donnees[strtolower(USERS_CLE)]);
}

function get_cle_user($id_user){
  $req=selectDbArr(TBL_USERS_INACTIF, array(USERS_INACTIF_CLE), array(USERS_INACTIF_ID => $id_user ));
  $donnees = $req->fetch();
  return($donnees[strtolower(USERS_CLE)]);
  }

function update_password($email,$password){

  return(updateDbEmail(TBL_USERS,array(USERS_PASSWORD=>$password),$email));
}


function create_user_inactif($prenom, $nom, $email, $tel, $mdp,$cle,$latitude,$longitude,$city,$country) {
    return(insertDb(TBL_USERS_INACTIF, array(USERS_INACTIF_NOM => $nom, USERS_INACTIF_MAIL => $email, USERS_INACTIF_PASSWORD => $mdp, USERS_INACTIF_PRENOM => $prenom, USERS_INACTIF_TEL => $tel,USERS_INACTIF_CLE =>$cle,USERS_INACTIF_LATITUDE =>$latitude,USERS_INACTIF_LONGITUDE =>$longitude,USERS_INACTIF_COUNTRY =>$country,USERS_INACTIF_CITY =>$city)));

}
function rankReq() {
    static $var = null;

    if (!$var)
      $var = 
       	oselect()->from(TBL_TEAMS, 'L')
	   ->setColStr("COUNT(*)+1")
	   ->setWhere('L.'.TEAMS_SCORE.' > T.'.TEAMS_SCORE)->sqlrequete();
   return $var;
}

//nb victoire/defaite/points/classement
//
function stat_team_req() {
	return oselect()->from(TBL_MATCHES, 'M')
		->addColStr('COUNT(*)')
	        ->andWhereStr(MATCHES_VALIDE. ' = 1');
}

function get_stat_team($id_team)  {
	$rank1 = rankReq();


	$scorerank = oselect()->from(TBL_TEAMS, 'T')
		->addCola('score', TEAMS_SCORE, 'T')
		->addColStr("($rank1) AS rang")
		->andWhereEqp('T', TEAMS_ID, $id_team)
		->execute()
		->fetch(\PDO::FETCH_ASSOC);

	$nbvictoires = 
		stat_team_req()
		->andWhereStr(sprintf("(M.%s = 1 AND M.%s = ?) || (M.%s = 2 AND M.%s = ?)",
			MATCHES_VICTOIRE, MATCHES_ID_TEAM1, MATCHES_VICTOIRE, MATCHES_ID_TEAM2),
			array($id_team, $id_team)) 
		->execute()
		->fetchColumn();

	$nbdefaites = 
		stat_team_req()
		->andWhereStr(sprintf("(M.%s = 2 AND M.%s = ?) || (M.%s = 1 AND M.%s = ?)",
			MATCHES_VICTOIRE, MATCHES_ID_TEAM1, MATCHES_VICTOIRE, MATCHES_ID_TEAM2),
		array($id_team, $id_team)) 
		->execute()
		->fetchColumn();

	$nbnuls = 
		stat_team_req()
		->andWhereStr(sprintf("(M.%s = ? || M.%s = ?)",
			MATCHES_ID_TEAM1, MATCHES_ID_TEAM2), array($id_team, $id_team)) 
		->andWhereStr(MATCHES_VICTOIRE.' = 0')
		->execute()
		->fetchColumn();

	$scorerank['nb_nuls'] = $nbnuls;
	$scorerank['nb_victoires'] = $nbvictoires;
        $scorerank['nb_defaites'] = $nbdefaites;

	/*
	list($nbtotal, $nb_nonnuls, $nb_victoires) =
		oselect()->from(TBL_MATCHES, 'M')
		->joinp(TBL_VIEW_MATCHES_VAINQUEUR, 'V', 'V', VIEW_MATCHES_VAINQUEUR_ID, 'M', MATCHES_ID)
		// Compte le nombre total
		->addColStr('COUNT(*)')
		// Compte le nombre de non nuls
		->addColStr(sprintf('COUNT(V.%s)', VIEW_MATCHES_VAINQUEUR_ID_VAINQUEUR))
		->addColStr(sprintf('COUNT(CASE WHEN V.%s = ? THEN 1 END)', VIEW_MATCHES_VAINQUEUR_ID_VAINQUEUR))
		->addVal($id_team)
		//->addColStr(sprintf("COUNT(26SUM(CASE WHEN %s IS NULL THEN 1 ELSE 0 END), SUM(%s = ?)")
		->andWhereStr(sprintf("(M.%s = ? || M.%s = ?)", MATCHES_ID_TEAM1, MATCHES_ID_TEAM2), array($id_team, $id_team)) 
	        ->andWhereStr(MATCHES_VALIDE. ' = 1')
		->execute()
		->fetch(\PDO::FETCH_NUM);


	$scorerank['nb_nuls'] = $nbtotal - $nb_nonnuls;
	$scorerank['nb_victoires'] = $nb_victoires;
        $scorerank['nb_defaites'] = $nb_nonnuls - $nb_victoires;
	 */
	return $scorerank;
}

function historique_req() {
	return oselect()->from(TBL_MATCHES, 'M')
		->addCola('date', MATCHES_DATE, 'M')
		->addCola('id_team', MATCHES_ID_TEAM1, 'M')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->addColStr(sprintf("(%s) AS rang_team", rankReq()))
		->addCola('resultat',  MATCHES_RESULTAT, 'M')
	        ->andWhereStr('M.'.MATCHES_VALIDE. ' = 1');
}

function list_historique_team($id_team, $limit)  {


	/*
	$select1 = oselect()->from(TBL_MATCHES, 'M')
		->addCola('date', MATCHES_DATE, 'M')
		->addCola('score',  MATCHES_RESULTAT, 'M')
		->addColStr(sprintf('(CASE WHEN V.%s IS NULL THEN "victoire" WHEN V.%s = ? THEN "victoire" ELSE "defaite")', 
		'victoire',  MATCHES_VICTOIRE, 'M')
		->addVal($id_team)
		->addCola('id_team', MATCHES_ID_TEAM2, 'M')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->addColStr("($rank1) AS rang_team")
		->joinp(TBL_VIEW_MATCHES_VAINQUEUR, 'V', 'V', VIEW_MATCHES_VAINQUEUR_ID, 'M', MATCHES_ID)
		->joinp(TBL_TEAMS, 'T', 'T', TEAMS_ID, 'M', MATCHES_ID_TEAM2)
		->andWhereEqp('M', MATCHES_ID_TEAM1)
	        ->andWhereStr('M.'.MATCHES_VALIDE. ' = 1', array()) ;
		->andWhereStr(sprintf("(M.%s = ? || M.%s = ?)", MATCHES_ID_TEAM1, MATCHES_ID_TEAM2), array($id_team, $id_team)) 
	 */

	$select1 = historique_req()
		->andWhereEqp('M', MATCHES_ID_TEAM1)
		->joinp(TBL_TEAMS, 'T', 'T', TEAMS_ID, 'M', MATCHES_ID_TEAM2)
		->addColStr(sprintf('(CASE M.%s WHEN 1 THEN "victoire" WHEN 2 THEN "defaite" ELSE "nul" END) AS victoire', MATCHES_VICTOIRE));

	$select2 = historique_req()
		->andWhereEqp('M', MATCHES_ID_TEAM2)
		->joinp(TBL_TEAMS, 'T', 'T', TEAMS_ID, 'M', MATCHES_ID_TEAM1)
		->addColStr(sprintf('(CASE M.%s WHEN 2 THEN "victoire" WHEN 1 THEN "defaite" ELSE "nul" END) AS victoire', MATCHES_VICTOIRE));
	
	$requete = "$select1 UNION ALL $select2 ORDER BY date DESC LIMIT $limit";
	$stmt = execCheck($requete, array($id_team, $id_team));
	return ($stmt) ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : false;
	/*
       $stmt= oselect()->addCol('alias', ?, 'R')->
                ->from(?, 'M')
		->joinp(?, 'L', 'L',
			?, 'M', ?)
		->andWhereEqp('M', ?, $?)
		->execute();
    return $stmt->fetchall();
	 */
}

function list_t_classement_s($limit, $idsport)  {
	$rank1 = rankReq();

       $stmt= oselect()->addCola('score', TEAMS_SCORE)
		->addColStr("($rank1) AS rang")
		->addCola('id_team', TEAMS_ID, 'T')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->andWhereEqp('T', TEAMS_SPORT, $idsport)
                ->from(TBL_TEAMS, 'T')
		->limit($limit)
		->order('T', TEAMS_SCORE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

// renvoie ID => nomdusport

function list_waiting_results($id_team)  {
    $stmt= oselect()
                ->from(TBL_MATCHES, 'M')
		->addCola('id', MATCHES_ID, 'M')
		->addCola('date', MATCHES_DATE, 'M')
		->addCola('victoire', MATCHES_VICTOIRE, 'M')
		->addCola('resultat', MATCHES_RESULTAT, 'M')
		->addCola('id_team', MATCHES_ID_TEAM1, 'M')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->joinp(TBL_TEAMS, 'T', 'T', TEAMS_ID, 'M', MATCHES_ID_TEAM1)
		->andWhereEqp('M', MATCHES_VALIDE, 0)
		->andWhereEqp('M', MATCHES_ID_TEAM2, $id_team)
		->order('M', MATCHES_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

function signale_t_team($id_team_user, $id_team2)  {
    return insertDb(TBL_SIGNALS_TEAMS, array(
	SIGNALS_TEAMS_ID_TEAM1 => $id_team_user,
	SIGNALS_TEAMS_ID_TEAM2 => $id_team2,
    
    ));
}


function list_msg_chat_interne($id_team, $date_last)  {
   $stmt= oselect()
                ->from(TBL_CHAT_INTERNE_EQUIPE, 'C')
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_INTERNE_EQUIPE_ID_USER)
		->addCola('date', CHAT_INTERNE_EQUIPE_DATE, 'C')
		->addCola('id_user', CHAT_INTERNE_EQUIPE_ID_USER, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->addCola('msg', CHAT_INTERNE_EQUIPE_CONTENT, 'C')
		->andWhereEqp('C', CHAT_INTERNE_EQUIPE_ID, $id_team)
		->andWhereStr('C.'.CHAT_INTERNE_EQUIPE_DATE.' >= ?',
			array($date_last))
		->order('C', CHAT_INTERNE_EQUIPE_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

function list_msg_chat_inter($id_team_user, $id_team2, $date_last)  {
   $stmt= oselect()
                ->from(TBL_CHAT_INTER_EQUIPE, 'C')
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_INTER_EQUIPE_ID_USER)
		->addCola('date', CHAT_INTER_EQUIPE_DATE, 'C')
		->addCola('id_user', CHAT_INTER_EQUIPE_ID_USER, 'C')
		->addCola('id_team', CHAT_INTER_EQUIPE_ID_EQUIPE_U, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->addCola('msg', CHAT_INTER_EQUIPE_CONTENT, 'C')
		->andWhereStr(
			sprintf('(C.%s = ? AND C.%s = ?) || (C.%s = ? AND C.%s = ?)',
			CHAT_INTER_EQUIPE_ID_EQUIPE_U, CHAT_INTER_EQUIPE_ID_EQUIPE2,
			CHAT_INTER_EQUIPE_ID_EQUIPE_U, CHAT_INTER_EQUIPE_ID_EQUIPE2),
			array($id_team_user, $id_team2, $id_team2, $id_team_user))
		->andWhereStr('C.'.CHAT_INTER_EQUIPE_DATE.' >= ?',
			array($date_last))
		->order('C', CHAT_INTERNE_EQUIPE_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

function list_msg_chat_user_team($id_user, $id_team, $date_last)  {
   $stmt= oselect()
                ->from(TBL_CHAT_USER_TEAM, 'C')
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_USER_TEAM_ID_USER_MSG)
		->addCola('date', CHAT_USER_TEAM_DATE, 'C')
		->addCola('id_user', CHAT_USER_TEAM_ID_USER_MSG, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->andWhereEqp('C', CHAT_USER_TEAM_ID_EQUIPE, $id_team)
		->andWhereEqp('C', CHAT_USER_TEAM_ID_USER_CIBLE, $id_user)
		->andWhereStr('C.'.CHAT_USER_TEAM_DATE.' >= ?',
			array($date_last))
		->order('C', CHAT_USER_TEAM_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

/*
 *
 *
 * Bill Karwin, author of "SQL Antipatterns: Avoiding the Pitfalls of Database Programming"
 *
 *  trouvé sur Quora (mySQL):
 * SELECT m.*
FROM mytable AS m
JOIN (SELECT category, MIN(id) AS id 
    FROM mytable GROUP BY category) AS t
  USING (id);
 *
 *
 * renvoie le dernier message par id_team
 *
 * */
function last_chat_interne_msg($id_user) {
   $stmt= oselect()
                ->from(TBL_CHAT_INTERNE_EQUIPE, 'C')
		->joinstr(sprintf('(SELECT %s, MAX(%s) AS date FROM %s GROUP BY %s) AS C2 ON C.%s = C2.date AND C.%s = C2.%s',
			CHAT_INTERNE_EQUIPE_ID_EQUIPE, CHAT_INTERNE_EQUIPE_DATE, TBL_CHAT_INTERNE_EQUIPE, CHAT_INTERNE_EQUIPE_ID_EQUIPE, 
			// ON
			CHAT_INTERNE_EQUIPE_DATE,
			CHAT_INTERNE_EQUIPE_ID_EQUIPE,
			CHAT_INTERNE_EQUIPE_ID_EQUIPE
		      ))
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_INTERNE_EQUIPE_ID_USER)
		->joinp(TBL_LIEN_TEAM_USERS, 'L',
			'L',LIEN_TEAM_USERS_ID_TEAM, 'C', CHAT_INTERNE_EQUIPE_ID_EQUIPE)
		->joinp(TBL_TEAMS, 'T',
			'T',TEAMS_ID, 'C', CHAT_INTERNE_EQUIPE_ID_EQUIPE)
		->joinp(TBL_REF_SPORTS, 'S',
			'S',REF_SPORTS_ID, 'T', TEAMS_SPORT)
		->addCola('id_team', CHAT_INTERNE_EQUIPE_ID_EQUIPE, 'C')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->addCola('sport', REF_SPORTS_NOM, 'S')
		->addCola('date', CHAT_INTERNE_EQUIPE_DATE, 'C')
		->addCola('id_user', CHAT_INTERNE_EQUIPE_ID_USER, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->addCola('msg', CHAT_INTERNE_EQUIPE_CONTENT, 'C')
		->andWhereEqp('L', LIEN_TEAM_USERS_ID_USER, $id_user)
		//->andWhereStr('C.'.CHAT_INTERNE_EQUIPE_DATE.' >= ?',
			////array($date_last))
		//->order('C', CHAT_INTERNE_EQUIPE_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

function condition_chat_inter($stmt, $id_user, $col_id_team) {
	return $stmt
		->joinp(TBL_LIEN_TEAM_USERS, 'L',
			'L',LIEN_TEAM_USERS_ID_TEAM, 'C', $col_id_team)
		->andWhereEqp('L', LIEN_TEAM_USERS_ID_USER, $id_user);
}

function last_chat_inter_msg($id_user) {

   $col_id_team2 = 'id_team2';

   $tbl_chat = TBL_CHAT_INTER_EQUIPE;
   $tbl_lien = TBL_LIEN_TEAM_USERS;
   $col_date = CHAT_INTER_EQUIPE_DATE;
   $lien_team = LIEN_TEAM_USERS_ID_TEAM;
   $lien_user = LIEN_TEAM_USERS_ID_USER;
   $chat_equipeu = CHAT_INTER_EQUIPE_ID_EQUIPE_U;
   $chat_equipe2 = CHAT_INTER_EQUIPE_ID_EQUIPE2;

   $case_col ="(CASE CL.$chat_equipeu WHEN L.$lien_team THEN CL.$chat_equipe2 ELSE CL.$chat_equipeu END)";

   $stmt_lastdate =
	   oselect()
	    ->joinstr("$tbl_lien AS L ON L.$lien_team = CL.$chat_equipeu OR L.$lien_team = CL.$chat_equipe2")
	    ->andWhereEqp('L', LIEN_TEAM_USERS_ID_USER, $id_user)
            ->from(TBL_CHAT_INTER_EQUIPE, 'CL')
	    ->addColStr("MAX($col_date) AS $col_date")
	    ->addColStr("L.$lien_team as $lien_team")
	    ->addColStr("$case_col AS $col_id_team2")
	    ->groupBy("$case_col, L.$lien_team");

	   //condition_chat_inter(oselect(), $id_user, $chat_equipe_u)
   $on_str = "C.$col_date = CL.$col_date AND (CL.$col_id_team2 = C.$chat_equipeu OR CL.$col_id_team2 = C.$chat_equipe2) AND CL.$lien_team = L.$lien_team";

   $stmt= oselect()
                ->from(TBL_CHAT_INTER_EQUIPE, 'C')
	        ->joinstr("$tbl_lien AS L ON L.$lien_team = C.$chat_equipeu OR L.$lien_team = C.$chat_equipe2")
		->joinstr("(\n  ".$stmt_lastdate->sqlrequete(). " )\n AS CL ON $on_str")
		->addVals($stmt_lastdate->getVals())
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_INTERNE_EQUIPE_ID_USER)
		->joinp(TBL_TEAMS, 'TU',
			'TU',TEAMS_ID, 'L', LIEN_TEAM_USERS_ID_TEAM)
		->joinp(TBL_TEAMS, 'T2',
			'T2',TEAMS_ID, 'CL', $col_id_team2)
		->joinp(TBL_TEAMS, 'TM',
			'TM',TEAMS_ID, 'C', CHAT_INTER_EQUIPE_ID_EQUIPE_U)
		->joinp(TBL_REF_SPORTS, 'S',
			'S',REF_SPORTS_ID, 'TU', TEAMS_SPORT)
		->addCola('id_team_user', LIEN_TEAM_USERS_ID_TEAM, 'L')
		->addCola($col_id_team2, $col_id_team2, 'CL')
		->addCola('id_team_msg', CHAT_INTER_EQUIPE_ID_EQUIPE_U, 'C')
		->addCola('pseudo_team_user', TEAMS_PSEUDO, 'TU')
		->addCola('pseudo_team_2', TEAMS_PSEUDO, 'T2')
		->addCola('sport', REF_SPORTS_NOM, 'S')
		->addCola('date', CHAT_INTER_EQUIPE_DATE, 'C')
		->addCola('id_user', CHAT_INTER_EQUIPE_ID_USER, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->addCola('msg', CHAT_INTER_EQUIPE_CONTENT, 'C')
		->andWhereEqp('L', LIEN_TEAM_USERS_ID_USER, $id_user)
		//->andWhereStr('C.'.CHAT_INTERNE_EQUIPE_DATE.' >= ?',
			//array($date_last))
		//->order('C', CHAT_INTERNE_EQUIPE_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}
function last_chat_user_team($id_user) {
   $tbl_lien = TBL_CHAT_USER_TEAM;
   $col_date = CHAT_USER_TEAM_DATE;
   $chat_team2 = CHAT_USER_TEAM_ID_EQUIPE;

   $stmt_lastdate = oselect()
            ->from(TBL_CHAT_USER_TEAM, 'CL')
	    ->addCola($chat_team2, $chat_team2, 'CL')
	    ->addColStr("MAX($col_date) AS $col_date")
	    ->groupBy($chat_team2)
	    ->andWhereEqp('CL',CHAT_USER_TEAM_ID_USER_CIBLE,$id_user);
   $stmt= oselect()
                ->from(TBL_CHAT_USER_TEAM, 'C')
	        ->andWhereEqp('C',CHAT_USER_TEAM_ID_USER_CIBLE,$id_user)
		->joinstr("({$stmt_lastdate->sqlrequete()}) AS CL ON CL.$chat_team2 = C.$chat_team2 AND C.$col_date = CL.$col_date")
		->addVals($stmt_lastdate->getVals())
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_USER_TEAM_ID_USER_MSG)
		->joinp(TBL_TEAMS, 'T',
			'T',TEAMS_ID, 'C', $chat_team2)
		->joinp(TBL_REF_SPORTS, 'S',
			'S',REF_SPORTS_ID, 'T', TEAMS_SPORT)
		->addCola('id_team', $chat_team2, 'C')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->addCola('sport', REF_SPORTS_NOM, 'S')
		->addCola('date', $col_date, 'C')
		->addCola('id_user', CHAT_USER_TEAM_ID_USER_MSG, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->addCola('msg', CHAT_USER_TEAM_CONTENT, 'C')
		->andWhereEqp('C', CHAT_USER_TEAM_ID_USER_CIBLE, $id_user)
		//->andWhereStr('C.'.CHAT_INTERNE_EQUIPE_DATE.' >= ?',
			////array($date_last))
		//->order('C', CHAT_USER_TEAM_DATE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}
function last_chat_team_user($id_user) {
   $tbl_lien = TBL_LIEN_TEAM_USERS;
   $lien_team = LIEN_TEAM_USERS_ID_TEAM;
   $col_date = CHAT_USER_TEAM_DATE;
   $chat_team2 = CHAT_USER_TEAM_ID_EQUIPE;
   $chat_user_cible = CHAT_USER_TEAM_ID_USER_CIBLE;

   $stmt_lastdate = oselect()
            ->from(TBL_CHAT_USER_TEAM, 'CL')
	    ->joinstr("$tbl_lien AS L ON L.$lien_team = CL.$chat_team2")
	    ->addCola($chat_team2, $chat_team2, 'CL')
	    ->addColStr("CL.$chat_user_cible")
	    ->addColStr("MAX(CL.$col_date) AS $col_date")
	    ->groupBy("CL.$chat_team2, CL.$chat_user_cible")
	    ->andWhereEqp('L', LIEN_TEAM_USERS_ID_USER, $id_user);

   $on_str = "C.$col_date = CL.$col_date AND CL.$chat_user_cible = C.$chat_user_cible"; // AND CL.$chat_team2 = L.$lien_team";
   $e = oselect()
                ->from(TBL_CHAT_USER_TEAM, 'C')
		->joinstr("( ".$stmt_lastdate->sqlrequete(). " ) AS CL ON $on_str")
		->joinstr("$tbl_lien AS L ON L.$lien_team = CL.$chat_team2")
		->addVals($stmt_lastdate->getVals())
		->joinp(TBL_USERS, 'U',
			'U',USERS_ID, 'C', CHAT_USER_TEAM_ID_USER_MSG)
		->joinp(TBL_TEAMS, 'T',
			'T',TEAMS_ID, 'C', $chat_team2)
		->joinp(TBL_REF_SPORTS, 'S',
			'S',REF_SPORTS_ID, 'T', TEAMS_SPORT)
		->addCola('id_team', $chat_team2, 'C')
		->addCola('pseudo_team', TEAMS_PSEUDO, 'T')
		->addCola('sport', REF_SPORTS_NOM, 'S')
		->addCola('date', $col_date, 'C')
		->addCola('id_user', CHAT_USER_TEAM_ID_USER_MSG, 'C')
		->addCola('prenom', USERS_PRENOM, 'U')
		->addCola('nom', USERS_NOM, 'U')
		->addCola('msg', CHAT_USER_TEAM_CONTENT, 'C')
		->andWhereEqp('L', LIEN_TEAM_USERS_ID_USER, $id_user)
		//->andWhereStr('C.'.CHAT_INTERNE_EQUIPE_DATE.' >= ?',
			////array($date_last))
		//->order('C', CHAT_USER_TEAM_DATE, 'DESC')
		->execute();
    $ret = $e->fetchAll(\PDO::FETCH_ASSOC);
   return $ret;
}
function update_fcmtoken($id_user, $token)  {
    $params = array(USERS_FCM_TOKEN => $token);
    return updateDb(TBL_USERS, $params, $id_user);
}

// Récupère les tokens des gars dans la team sauf celui de $id_user
function get_tokens_team_except($id_team, $id_user = null) {
       $stmt= oselect()->addCol(USERS_FCM_TOKEN, 'U')
                ->from(TBL_LIEN_TEAM_USERS, 'L')
		->joinp(TBL_USERS, 'U', 'U',
			USERS_ID, 'L', LIEN_TEAM_USERS_ID_USER)
		->andWhereEqp('L', LIEN_TEAM_USERS_ID_TEAM, $id_team)
		->andWhereStr('U.'. USERS_FCM_TOKEN. ' IS NOT NULL');

       if ($id_user)
		$stmt->andWhereStr('L.'. LIEN_TEAM_USERS_ID_USER. ' <> ?',array( $id_user));

return	$stmt->execute()->fetchAll(\PDO::FETCH_COLUMN);
}

function get_token_user($id_user) {
       $stmt= oselect()->addCol(USERS_FCM_TOKEN, 'U')
                ->from(TBL_USERS, 'U')
		->andWhereEqp('U', USERS_ID, $id_user);
		//->andWhereStr('U.'. USERS_FCM_TOKEN. ' IS NOT NULL');

return	$stmt->execute()->fetchColumn();
}

function get_tokens_2teams_except($id_team1, $id_team2, $id_user) {
	$col_idteam = LIEN_TEAM_USERS_ID_TEAM;

       $stmt= oselect()->addCol(USERS_FCM_TOKEN, 'U')
                ->from(TBL_LIEN_TEAM_USERS, 'L')
		->joinp(TBL_USERS, 'U', 'U',
			USERS_ID, 'L', LIEN_TEAM_USERS_ID_USER)
		->andWhereStr("L.$col_idteam = ? OR L.$col_idteam = ?", array($id_team1, $id_team2))
		->andWhereStr('U.'. USERS_FCM_TOKEN. ' IS NOT NULL');

       $stmt->andWhereStr('L.'. LIEN_TEAM_USERS_ID_USER. ' <> ?',array( $id_user));

	
       return $stmt->execute()->fetchall(\PDO::FETCH_COLUMN);
}
