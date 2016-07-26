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
if($longueur==1) {return(array(strtolower(USERS_LATITUDE)=>$reponse[0][strtolower(USERS_LATITUDE)],strtolower(USERS_LONGITUDE)=>$reponse[0][strtolower(USERS_LONGITUDE)]));}
elseif ($longueur==2) {
    return(array(strtolower(USERS_LONGITUDE)=>($reponse[0][strtolower(USERS_LONGITUDE)]+$reponse[1][strtolower(USERS_LONGITUDE)])/2,strtolower(USERS_LATITUDE)=>($reponse[0][strtolower(USERS_LATITUDE)]+$reponse[1][strtolower(USERS_LATITUDE)]))/2);
}
else{for($i1=0;$i1<$longueur-2;$i1++)
    for($i2=$i1+1;$i2<$longueur-1;$i2++)
        for ($i3=$i2+1; $i3 <=$longueur-1 ; $i3++) { 
            $latitude_grav=($reponse[$i1][strtolower(USERS_LATITUDE)]+$reponse[$i2][strtolower(USERS_LATITUDE)]+$reponse[$i3][strtolower(USERS_LATITUDE)])/3;
            $longitude_grav=($reponse[$i1][strtolower(USERS_LONGITUDE)]+$reponse[$i2][strtolower(USERS_LONGITUDE)]+$reponse[$i3][strtolower(USERS_LONGITUDE)])/3;
            $positions_barycentre_possibles[$i1][$i2][$i3]= array(strtolower(TEAMS_LATITUDE) =>$latitude_grav,strtolower(TEAMS_LONGITUDE)=>$longitude_grav  );
            $sum=0;
            foreach(array($i1,$i2,$i3 ) as $t){
                $sum=$sum+calcule_distance2($latitude_grav,$longitude_grav,$reponse[$t][strtolower(USERS_LATITUDE)],$reponse[$t][strtolower(USERS_LONGITUDE)]);
            }
            if ($min==-1){
                $positions_barycentre_solution=array(strtolower(TEAMS_LATITUDE)=>$latitude_grav,strtolower(TEAMS_LONGITUDE)=>$longitude_grav);
                $triplet_solution = array($i1,$i2,$i3 );
                $min=$sum;
            }
            elseif($sum<$min){
                $min=$sum;
                $positions_barycentre_solution=array(strtolower(TEAMS_LATITUDE)=>$latitude_grav,strtolower(TEAMS_LONGITUDE)=>$longitude_grav);
                $triplet_solution = array($i1,$i2,$i3 );
            }
        }
    }

    return($positions_barycentre_solution);


}

function getCityCountry($latitude,$longitude){

$geocode=file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.$latitude.','.$longitude.'&sensor=false');
$output= json_decode($geocode);
$country="not known";
$city="not known";
for($j=0;$j<count($output->results[0]->address_components);$j++){

    $cn=array($output->results[0]->address_components[$j]->types[0]);

    if(in_array("country", $cn)){
        $country= $output->results[0]->address_components[$j]->long_name;
    }
    if(in_array("city", $cn)){
        $city= $output->results[0]->address_components[$j]->long_name;
    }
}

return( array(strtolower(TEAMS_CITY) => $city, strtolower(TEAMS_COUNTRY) => $country ));
}