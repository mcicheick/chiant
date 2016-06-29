<?php

session_start();
require_once 'requete.php';


$email=$_SESSION['mail'] ;

include('header.html');

$mdp1=$_POST['mdp1'];
$mdp2=$_POST['mdp2'];

if($mdp1==$mdp2){
$a=update_password($email,$mdp1)

?>

<div style="width:100%;align:center;border:10px;">
 
 
<div style="color:#FF23;font-size: 450%;text-align:left;margin-top:20px;padding-left:40px;">Votre mot de passe a été modifié !  </div>
<div style="color:#FF23;font-size: 100%;text-align:justify;margin-top:20px;padding-left:50px;width:500px;">
 Si vous ne parvenez toujours pas à vous connecter, contacter l'équipe à l'adresse fauxmail@SportGreed.com  </div>
</div>
<?php
}
else{
	?>
	<div style="width:100%;align:center;border:10px;">
 
 
<div style="color:#FF23;font-size: 450%;text-align:left;margin-top:20px;padding-left:40px;">Les mots de passes ne correspondent, réessayer.  </div>
<div style="color:#FF23;font-size: 100%;text-align:justify;margin-top:20px;padding-left:50px;width:500px;">
 Si vous rencontrez toujours des difficultés, contacter l'équipe à l'adresse fauxmail@SportGreed.com  </div>
</div>
<?php
}
