<?php
// Version de la bdd : 0.0.2.0.0.5(+1) 

define('TBL_CHAT_INTERNE_EQUIPE', 'chat_interne_equipe');

define('CHAT_INTERNE_EQUIPE_ID','ID');
define('CHAT_INTERNE_EQUIPE_CONTENT','CONTENT');
define('CHAT_INTERNE_EQUIPE_ID_USER','ID_USER');
define('CHAT_INTERNE_EQUIPE_ID_EQUIPE','ID_EQUIPE');
define('CHAT_INTERNE_EQUIPE_DATE','DATE');

define('TBL_CHAT_INTER_EQUIPE', 'chat_inter_equipe');

define('CHAT_INTER_EQUIPE_CONTENT','CONTENT');
define('CHAT_INTER_EQUIPE_ID_USER','ID_USER');
define('CHAT_INTER_EQUIPE_ID_EQUIPE_U','ID_EQUIPE_USER');
define('CHAT_INTER_EQUIPE_ID_EQUIPE2','ID_EQUIPE2');
define('CHAT_INTER_EQUIPE_DATE','DATE');

define('TBL_COUP_COEURS_EQUIPES', 'coup_coeurs_equipes');

define('COUP_COEURS_EQUIPES_ID_AMOUREUX','ID_AMOUREUX');
define('COUP_COEURS_EQUIPES_ID_BOGOSS','ID_BOGOSS');
define('COUP_COEURS_EQUIPES_DATE','DATE');
define('COUP_COEURS_EQUIPES_ID','ID');

define('TBL_INVITATIONS_EQUIPE_JOUEUR', 'invitations_equipe_joueur');

define('INVITATIONS_EQUIPE_JOUEUR_ID_INVITANT','ID_INVITANT');
define('INVITATIONS_EQUIPE_JOUEUR_ID_INVITE','ID_INVITE');
define('INVITATIONS_EQUIPE_JOUEUR_ID_EQUIPE','ID_EQUIPE');

define('TBL_INVITATIONS_INTER_EQUIPES', 'invitations_inter_equipes');

define('INVITATIONS_INTER_EQUIPES_ID','ID');
define('INVITATIONS_INTER_EQUIPES_ID_INVITANT','ID_INVITANT');
define('INVITATIONS_INTER_EQUIPES_ID_INVITE','ID_INVITE');
define('INVITATIONS_INTER_EQUIPES_DATE_RENCONTRE','DATE_RENCONTRE');
define('INVITATIONS_INTER_EQUIPES_MONTANT','MONTANT');

define('TBL_LIEN_TEAM_USERS', 'lien_team_users');

define('LIEN_TEAM_USERS_ID_TEAM','ID_TEAM');
define('LIEN_TEAM_USERS_ID_USER','ID_USER');

define('TBL_TEAMS', 'teams');

define('TEAMS_ID','ID');
define('TEAMS_PSEUDO','PSEUDO');
define('TEAMS_PHOTO','PHOTO');
define('TEAMS_NB_PLAYED_M','NB_PLAYED_M');
define('TEAMS_NB_VICTORIES','NB_VICTORIES');
define('TEAMS_SCORE','SCORE');
define('TEAMS_RANK','RANK');
define('TEAMS_SPORT','SPORT');
define('TEAMS_PICTURE_FILE','PICTURE_FILE');

define('TBL_USERS', 'users');

define('USERS_ID','ID');
define('USERS_PRENOM','PRENOM');
define('USERS_NOM','NOM');
define('USERS_MAIL','MAIL');
define('USERS_PASSWORD','PASSWORD');
define('USERS_PSEUDO','PSEUDO');
define('USERS_PICTURE_FILE','PICTURE_FILE');
define('USERS_PREFS_SPORT','PREFS_SPORT');

define('TBL_OFFRE_TEAM_USERS', 'offre_team_users');

define('OFFRE_TEAM_USERS_ID_TEAM','ID_TEAM');
define('OFFRE_TEAM_USERS_DESCRIPTION','DESCRIPTION');
define('OFFRE_TEAM_USERS_NB','NB');
define('OFFRE_TEAM_USERS_FREQUENCE','FREQUENCE');
define('OFFRE_TEAM_USERS_NIVEAU','NIVEAU');
