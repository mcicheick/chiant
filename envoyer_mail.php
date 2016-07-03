<?php
namespace envoyer_mail;
function send_mail_inscription($email,$iduser,$cle){
	// Préparation du mail contenant le lien d'activation
$destinataire = $email;
$sujet = "Activer votre compte" ;
$entete = "From: inscription@SportGreed.com" ;
 
// Le lien d'activation est composé du login(log) et de la clé(cle)
$message = 'Bienvenue sur SportGreed,
 
Pour activer votre compte, veuillez cliquer sur le lien ci dessous
ou copier/coller dans votre navigateur internet.
  
http://www.jh-vehicules.fr/near2u/confirmation_inscription.php?iduser='.urlencode($iduser).'&cle='.urlencode($cle).'
 
 
---------------
Ceci est un mail automatique, Merci de ne pas y répondre.';
 
 
mail($destinataire, $sujet, $message, $entete) ; // Envoi du mail
}

function send_mail_change_password($email,$cle){
	// Préparation du mail contenant le lien d'activation
$destinataire = $email;
$sujet = "Activer votre compte" ;
$entete = "From: inscription@SportGreed.com" ;
 
// Le lien d'activation est composé du login(log) et de la clé(cle)
$message = 'Bienvenue sur SportGreed,
 
Pour activer votre compte, veuillez cliquer sur le lien ci dessous
ou copier/coller dans votre navigateur internet.
  
http://www.jh-vehicules.fr/near2u/change_password.php?email='.urlencode($email).'&cle='.urlencode($cle).'
 
 
---------------
Ceci est un mail automatique, Merci de ne pas y répondre.';
 
 
mail($destinataire, $sujet, $message, $entete) ; // Envoi du mail
}