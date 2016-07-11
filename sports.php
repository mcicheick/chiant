<?php

// CE fichier doit être en accord avec la table SQL REF_SPORTS
// il faut aussi modifier update_sports_pref en consequence dans requete.php

define('NB_SPORTS', 2);


// Cf structures.sql pour accord (données de la table sql)
function listSports() {
	// ID_SPORT => Nom du sport
	return array(0 => 'football', 1 => 'basketball');
}
// test
