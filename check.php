<?php
namespace dbcheck;

require_once 'db.php';
require_once 'fieldsbdd.php';


function belongs_to_u_t($id_user, $id_team) {
   $stmt = selectDbArr(TBL_LIEN_TEAM_USERS, array('1'), array(LIEN_TEAM_USERS_ID_TEAM => $id_team, LIEN_TEAM_USERS_ID_USER => $id_user));
   return $stmt->rowCount() > 0;
}

function belongs_to_u_match_t2($id_user, $id_match) {
    $db = getDb();
    $stmt = $db->prepare('SELECT 1 FROM '.TBL_MATCHES.' AS M JOIN '.
	    TBL_LIEN_TEAM_USERS.' AS L ON L.'.LIEN_TEAM_USERS_ID_TEAM.' = M.'.
	    MATCHES_ID_TEAM2.' WHERE L.'.LIEN_TEAM_USERS_ID_USER.' = ? AND M.ID = ?' );
    $stmt->execute(array($id_user, $id_match));
    return $stmt->rowCount() > 0;
}

/*
function same_team_sport($id_user, $id_team_u, $id_team2) {
    $db = getDb();
    $stmt = $db->prepare('SELECT COUNT(*) FROM TBL_TEAMS AS T WHERE ID=? || ID = ? GROUP BY TEAMS_SPORT  HAVING COUNT(*)=1 INNER JOIN TBL_LIEN_TEAM_USERS AS U ON U.LIEN_TEAM_USERS_ID_TEAM= ?')

}
 */

function same_sport_t($id_team1, $id_team2) {
    $db = getDb();
    $stmt = $db->prepare('SELECT COUNT(*) FROM '.TBL_TEAMS.' AS T WHERE ID=? || ID = ? GROUP BY '.TEAMS_SPORT);
    $stmt->execute(array($id_team1, $id_team2));
    return $stmt->fetchColumn() == 2;
}


// retourne l'id utilisateur si possible
function check_credentials($email, $hashmdp) {
    $stmt = selectDbArr(TBL_USERS, array(USERS_ID), array(USERS_MAIL => $email, USERS_PASSWORD => $hashmdp));
    // inutile de vérifier $stmt == false car on est en mode ERRMODE_EXCEPTION (les erreurs lancent des excceptions)

    if ($stmt->rowCount()>0)
	return $stmt->fetchColumn();
    return false;

}
