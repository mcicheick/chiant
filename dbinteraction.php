<?php
namespace dbinteraction;

require_once 'db.php';
require_once 'fieldsbdd.php';
require_once 'sports.php';
require_once 'geolocalisation.php';

require_once 'lib.php';

class PreferencesSport {
    public $football = false;
    public $basket = false;


    public function fromInt($n) {
        $arr= bools_of_int($n, array(SPORT_BIT_FOOTBALL,
                                     SPORT_BIT_BASKETBALL));
        $this->football = $arr(0);
        $this->basket   = $arr(1);
        return $this;
    }

    public function toInt() {
        return int_of_bools (array($this->football, $this->basket),
                   array(SPORT_BIT_FOOTBALL, SPORT_BIT_BASKETBALL));
    }
}




function updateAffinitesSports($iduser, $affinite) {
    $params = array(USERS_PREFS_SPORT => $affinite->toInt());
    return updateDb(TBL_USERS, $params,$iduser);
}


function updateBAffinitesSports($iduser, $football, $basketball) {
    $prefs = new PreferencesSport();
    $prefs->basket = $basketball;
    $prefs->football = $football;
    return updateAffinitesSports($iduser, $prefs);
}

function create_team($pseudo, $sport) {
    return insertDb(TBL_TEAMS, array(TEAMS_PSEUDO=>$pseudo, TEAMS_SPORT => $sport));
}

function create_user($prenom, $nom, $email, $tel, $mdp) {
    return insertDb(TBL_USERS, array(USERS_NOM => $nom, USERS_MAIL => $email, USERS_PASSWORD => $mdp, USERS_PRENOM => $prenom, USERS_TEL => $tel));
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

function validate_result($id_result) {
   return updateDb(TBL_MATCHES, array(MATCHES_VALIDE => 1),$id_result);
}

function u_post_result($id_team_user, $id_team2, $result)  {
    return insertDb(TBL_MATCHES, array(
	MATCHES_ID_TEAM1 => $id_team_user,
	MATCHES_ID_TEAM2 => $id_team2,
	MATCHES_RESULTAT => $result));
}

/*pour updater les positions de l'équipe toutes les variations de 1 km*/

function update_position($id_user){
    $req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_TEAM), array(LIEN_TEAM_USERS_ID_USER =>$iduser ));
    while($donnees=$req->fetch()) update_positionTeam($donnees[LIEN_TEAM_USERS_ID_TEAM]);
    $req->closeCursor();
}
function update_positionTeam($idteam){
    

$req=selectDbArr(TBL_TEAMS,array(TEAM_LONGITUDE,TEAM_LATITUDE), array(TEAMS_ID =>$idteam ));
$reponse=$req->fetchall();
$solution=calcule_barycentre($reponse);
updatepositionDB($id_team,$solution[TEAM_LATITUDE],$solution[TEAM_LONGITUDE]);

}


function updatepositionDB($latitude,$longitude,$id_team){

    return updateDb(TBL_TEAMS, array(TEAM_LATITUDE => $latitude,TEAM_LONGITUDE=>$longitude ),$id_team );
}



/*fonction qui actualise la date de dernière connexion des équipes*/
function update_last_connexion($id_user){
    $req=selectDbArr(TBL_LIEN_TEAM_USERS,array(LIEN_TEAM_USERS_ID_TEAM), array(LIEN_TEAM_USERS_ID_USER =>$iduser ));
    while($donnees=$req->fetch()) updateDb(TABLE_TEAMS,array(TEAM_LAST_CONNEXION => date('d/m/Y'), $donnees[LIEN_TEAM_USERS_ID_TEAM]));
    $req->closeCursor();
    return(true);
}
