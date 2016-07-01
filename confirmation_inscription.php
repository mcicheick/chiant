<?php
require_once 'dbinteraction.php';
require_once 'fieldsbdd.php';
include('header.html');


// Récupération des variables nécessaires à l'activation
$iduser = $_GET['iduser'];
$cle = $_GET['cle'];
$email=$_GET['email'];
 
// Récupération de la clé correspondant au $login dans la base de données
$true_key=dbinteraction\get_cle_user($iduser);
$isactif=dbinteraction\get_cle_email($email);
if($cle == $true_key & $isactif!=null) // Si le compte est déjà actif on prévient
  {?>
<div style="width:100%;align:center;border:10px;">
 
 
<div style="color:#FF23;font-size: 450%;text-align:left;margin-top:20px;padding-left:40px;">Votre compte à déjà été activé !  </div>
<div style="color:#FF23;font-size: 100%;text-align:justify;margin-top:20px;padding-left:50px;width:500px;">En cas de problème de connexion, vous pouvez réinitialiser votre mot de passe en vous rendant sur l'application. Si vous vous souvenez de votre mot de passe mais que vous ne parvenez cependant pas à vous connecter, contacter l'équipe à l'adresse fauxmail@SportGreed.com  </div>
</div>

<?php
  }
else // Si ce n'est pas le cas on passe aux comparaisons
  {
  	if($cle==$true_key){
        dbinteraction\confirmation_inscription($email,$cle);
        ?>
<div style="width:100%;align:center;border:10px;">
 
 
<div style="color:#FF23;font-size: 450%;text-align:left;margin-top:20px;padding-left:40px;">Votre compte est maintenant actif !  </div>
<div style="color:#FF23;font-size: 100%;text-align:justify;margin-top:20px;padding-left:50px;width:500px;">Pour vous connecter, utilisez votre adresse électronique et votre mot de passe.  </div>
</div>

<?php
 }
  }
 
 
