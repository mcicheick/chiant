<?php
define('RAYON_TERRE_KM',6371);
function calcule_distance2($latitude1,$longitude1,$latitude2,$longitude2){
    $resultat=pow(sin($latitude1)-sin($latitude2), 2)+pow(cos($latitude1)*sin($longitude1)-cos($latitude2)*sin($longitude2)
        , 2) +pow(cos($latitude2)*sin($longitude2)-cos($latitude1)*sin($longitude1), 2) ;
    return RAYON_TERRE_KM*RAYON_TERRE_KM*$resultat ;
}

function calcule_barycentre($reponse){
	$positions_barycentre_solution=array();
    $variance=array();
    $triplet_solution=array();
    $min=-1;
    $longueur=count($reponse);
    if($longueur==1) {return(array(TEAM_LATITUDE=>$reponse[0][TEAM_LATITUDE],TEAM_LONGITUDE=>$reponse[0][TEAM_LONGITUDE]));}
   elseif ($longueur==2) {
    return(array(TEAM_LONGITUDE=>($reponse[0][TEAM_LONGITUDE]+$reponse[1][TEAM_LONGITUDE])/2,TEAM_LATITUDE=>($reponse[0][TEAM_LATITUDE]+$reponse[1][TEAM_LATITUDE]))/2);
}
    else{for($i1=0;$i1<$longueur-2;$i1++)
        for($i2=$i1+1;$i2<$longueur-1;$i2++)
            for ($i3=$i2+1; $i3 <=$longueur-1 ; $i3++) { 
                $latitude_grav=($reponse[$i1][TEAM_LATITUDE]+$reponse[$i2][TEAM_LATITUDE]+$reponse[$i3][TEAM_LATITUDE])/3;
                $longitude_grav=($reponse[$i1][TEAM_LONGITUDE]+$reponse[$i2][TEAM_LONGITUDE]+$reponse[$i3][TEAM_LONGITUDE])/3;
                $positions_barycentre_possibles[$i1][$i2][$i3]= array(TEAM_LATITUDE =>$latitude_grav,TEAM_LONGITUDE=>$longitude_grav  );
                 $sum=0;
                 for($t=0;$t<$longueur;$t++){
                    $sum=$sum+calcule_distance2($latitude_grav,$longitude_grav,$reponse[$t][TEAM_LATITUDE],$reponse[$t][TEAM_LONGITUDE]);
                 }
                 if ($min==-1){
                    $positions_barycentre_solution=array(TEAM_LATITUDE=>$latitude_grav,TEAM_LONGITUDE=>$longitude_grav);
                    $triplet_solution = array($i1,$i2,$i3 );
                    $min=$sum;
                }
                 elseif($sum<$min){
                    $min=$sum;
                    $positions_barycentre_solution=array(TEAM_LATITUDE=>$latitude_grav,TEAM_LONGITUDE=>$longitude_grav);
                    $triplet_solution = array($i1,$i2,$i3 );
                 }
             }
         }

                 return($positions_barycentre_solution);


}