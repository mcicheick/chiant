<?php
namespace dbinteraction;

require_once 'db.php';
require_once 'fieldsbdd.php';
require_once 'sports.php';

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

function update_user_photo($iduser, $path) {
    // TODO: suppriemr l'ancienne photo
    $params = array(USERS_PICTURE_FILE => $path);
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

function create_user($prenom, $nom, $email, $mdp) {
    return insertDb(TBL_USERS, array(USERS_NOM => $nom, USERS_MAIL => $email, USERS_PASSWORD => $mdp, USERS_PRENOM => $prenom));
}

function create_team_by_user($id_user, $pseudo, $sport) {
    if (! create_team($pseudo, $sport))
	return false;

    $id_team = getDb()->lastInsertId();

    return u_joins_t($id_user, $id_team);
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
	CHAT_INTER_EQUIPE_ID_EQUIPE_U => $id1,
	CHAT_INTER_EQUIPE_ID_EQUIPE2 => $id2));
}

function get_photo($id_user) {
	return selectId(TBL_USERS, array(USERS_PICTURE_FILE), $id_user)->fetchColumn();
}

