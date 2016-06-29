<?php
require_once 'requete.php';
require_once 'fieldsbdd.php';
 ?>
<div style="margin-top:0px;position:fixed;top:0;left:0;width:100%;" >
<div style="color:white;font-weight:bold;font-size: 150%;text-align:left;background-color:#3B90AF;margin-top:0px;padding-left:30px;padding-bottom:2px;padding-top:2px;border-bottom:1px double black;">SportGreed</div>

 
</div>


<?php
// Récupération des variables nécessaires à l'activation
$iduser = $_GET['iduser'];
$cle = $_GET['cle'];
 
// Récupération de la clé correspondant au $login dans la base de données
check=check_cle_actif($iduser,$cle);

$actif=check[USERS_ACTIF];

if($actif == true) // Si le compte est déjà actif on prévient
  {?>
<div style="width:100%;align:center;border:10px;">
 
 
<div style="color:#FF23;font-size: 450%;text-align:left;margin-top:20px;padding-left:40px;">Votre compte à déjà été activé !  </div>
<div style="color:#FF23;font-size: 100%;text-align:justify;margin-top:20px;padding-left:50px;width:500px;">En cas de problème de connexion, vous pouvez réinitialiser votre mot de passe en vous rendant sur l'application. Si vous vous souvenez de votre mot de passe mais que vous ne parvenez cependant pas à vous connecter, contacter l'équipe à l'adresse fauxmail@SportGreed.com  </div>
</div>

<?php
  }
else // Si ce n'est pas le cas on passe aux comparaisons
  {
     if($actif == false) 
       { 
        confirmation_inscription($iduser)
        ?>
<div style="width:100%;align:center;border:10px;">
 
 
<div style="color:#FF23;font-size: 450%;text-align:left;margin-top:20px;padding-left:40px;">Votre compte est maintenant actif !  </div>
<div style="color:#FF23;font-size: 100%;text-align:justify;margin-top:20px;padding-left:50px;width:500px;">Pour vous connecter, utilisez votre adresse électronique et votre mot de passe.  </div>
</div>

<?php
       }
  }
 
 
