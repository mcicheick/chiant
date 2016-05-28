<?php
require_once '../config.php';

$tests =
array(
    'newuser'=>
	array('prenom' => 'mart', 'nom'=> 'ogog', 'email' => 's@f.fr',
	'hashmdp' => '%mdp'),
    'update_user_sport_prefs' =>
	  array('football' => 1, 'basket' => 0),
  'team_likes_team' =>
	   array('id_bogoss' => 1, 'id_amoureux' => 2),
              'join_team' =>
       	               array('id_team' => 1),
              'new_team' =>
       	         array('pseudo' => 'pseudo1', 'sport' => 1),
              'invites_team_team' =>
                 array('id_invitant' => 1, 'id_invite' => 2, 'date' => 'now', 'montant' => 100),
              'invites_in_team' =>
                 array('id_team' => 1, 'id_invite' => 2),
              'post_chat_interne' =>
                 array('id_team' => 1, 'msg' => 'hellow world team'),
              'post_chat_inter_teams' =>
                 array('id_team_user' => 1,'id_team_cible' => 2, 'msg' => 'hello other team'),
              'login' =>
                 array('email' => 's@f.fr' , 'hashmdp' => '%mdp'),
		 'login' =>
                 array('email' => EMAIL_SUPERUSER, 'hashmdp' =>PWD_SUPERUSER),

            );

/*
function route_to_link ($requete, $params) {
    $params['requete'] = $requete;
    return '../entree_post.php?'. http_build_query($params);
}

function print_link($lien, $str) {
    echo "<a href='".$lien."'>".$str."</a>\n\n";
}

function route_to_string($req, $params) {
    $str = 'requete: '.$req."\nparamètres : ";
    foreach ($params as $key => $param) 
	$str .= "\t".$key. ':  <input type="text" name="'.$key.'" value="'.$param.'" />\n<br/>';
    return $str;
}
*/
/*
    print_link(route_to_link($req,$params), route_to_string($req,$params));
 */

function print_route($req, $params) {
    echo '<form action="../entree_post.php" method="post"><br/>';
    $str = 'requete: '.$req."\n<br/>paramètres :<br/> ";
    foreach ($params as $key => $param) 
	$str .= "\t".$key. ':  <input type="text" name="'.$key.'" value="'.$param.'" />\n<br/>';
   
    $str .= '<input type="submit" value="submit" /></form><p/>';
	echo $str;
    return ;
}


echo "<html>";
foreach($tests as $req => $params) {
    //print_link(route_to_link($req,$params), route_to_string($req,$params));
    $params[MAGIC_PWD_FIELD] = MAGIC_PWD;
    $params[SESSION_USERID_NAME] = 2;
    print_route($req, $params);
}

