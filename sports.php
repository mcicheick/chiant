<?php

// CE fichier doit �tre en accord avec la table SQL REF_SPORTS
// il faut aussi modifier update_sports_pref en consequence dans requete.php

define('NB_SPORTS', 2);

// Num�ro du bit indiquant le sport dans l'entier de pr�f�rence dans la bdd
// TODO : etre en accord avec la table SQL REF_SPORTS
define ('SPORT_BIT_FOOTBALL', 0);
define ('SPORT_BIT_BASKETBALL', 1);

define('FOOTBALL', 'football');
define('BASKETBALL', 'basketball');
// test
