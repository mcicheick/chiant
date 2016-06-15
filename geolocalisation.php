<?php
DEFINE(RAYON_TERRE_KM,6371);
function calcule_distance2($latitude1,$longitude1,$latitude2,$longitude2){
    $resultat=pow(sin($latitude1)-sin($latitude2), 2)+pow(cos($latitude1)*sin($longitude1)-cos($latitude2)*sin($longitude2)
        , 2) +pow(cos($latitude2)*sin($longitude2)-cos($latitude1)*sin($longitude1), 2) ;
    return RAYON_TERRE_KM*RAYON_TERRE_KM*$resultat ;
}

