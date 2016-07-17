<?php

// CE fichier doit être en accord avec la table SQL REF_SPORTS
// il faut aussi modifier update_sports_pref en consequence dans requete.php

define('NB_SPORTS', 10);


// Cf structures.sql pour accord (données de la table sql)
function listSports() {
	// ID_SPORT => Nom du sport
	return array(0 => 'football', 1 => 'basketball', 2 => 'tennis', 3 => 'course', 4 => 'billard', 5 => 'volley'
		, 6 => 'frisbie', 7 => 'pingpong', 8 => 'badminton', 9 => 'petanque');
}
// test
