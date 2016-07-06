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


function create_team($pseudo, $sport) {
    return insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>$pseudo, TEAMS_SPORT => $sport));
}

function create_user($prenom, $nom, $email, $tel, $mdp,$cle,$latitude,$longitude,$city,$country) {
    return insertDb(TBL_USERS, array(USERS_NOM => $nom, USERS_MAIL => $email, USERS_PASSWORD => $mdp, USERS_PRENOM => $prenom, USERS_TEL => $tel,USERS_CLE =>$cle,USERS_LONGITUDE =>$longitude,USERS_LATITUDE =>$latitude,USERS_COUNTRY =>$country,USERS_CITY =>$city));

}

function create_team_by_user($id_user, $pseudo, $sport) {
    if (! create_team($pseudo, $sport))
	return false;

    $id_team = getDb()->lastInsertId();
    //echo'Nouvelle team : '.$id_team."\n";

    u_joins_t($id_user, $id_team);
    return true;
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
	return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
	return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

/*pour updater les positions de l'équipe toutes les variations de 1 km*/

function update_position($id_user,$latitude,$longitude,$city,$country){
    updateDb(TBL_USERS, array(USERS_LATITUDE=> $latitude,USERS_LONGITUDE=>$longitude,USERS_CITY=>$city,USERS_COUNTRY=>$country ),$id_user );
    $req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_TEAM), array(LIEN_TEAM_USERS_ID_USER =>$id_user ));
    while($donnees=$req->fetch()) update_positionTeam($donnees[LIEN_TEAM_USERS_ID_TEAM]);
    $req->closeCursor();
    return true;
}

function update_positionTeam($idteam){   

$req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_USER), array(TEAMS_ID =>$idteam ));
$reponse=$req->fetchall();
$solution=calcule_barycentre($reponse);
updatepositionDB($id_team,$solution[TEAM_LATITUDE],$solution[TEAM_LONGITUDE]);

}


function updatepositionDB($latitude,$longitude,$id_team){

return(updateDb(TBL_TEAMS, array(TEAM_LATITUDE => $latitude,TEAM_LONGITUDE=>$longitude ),$id_team ));
}



/*fonction qui actualise la date de dernière connexion des équipes*/
function update_last_connexion($id_user){
    $req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_TEAM), array(LIEN_TEAM_USERS_ID_USER =>$id_user ));
    while($donnees=$req->fetch()) updateDb(TABLE_TEAMS,array(TEAM_LAST_CONNEXION => date('d/m/Y')), $donnees[LIEN_TEAM_USERS_ID_TEAM]);
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
		->andWhereEqp('T', TEAMS_SPORT, $idsport)
                ->from(TBL_TEAMS, 'T')
		->limit($limit)
		->order('T', TEAMS_SCORE, 'DESC')
		->execute();
    return $stmt->fetchall(\PDO::FETCH_ASSOC);
}

// renvoie ID => nomdusport
function list_sports() {
    $stmt = selectDbWhStr(TBL_REF_SPORTS,array(REF_SPORTS_ID, REF_SPORTS_NOM), 1, array());
     return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
}

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

