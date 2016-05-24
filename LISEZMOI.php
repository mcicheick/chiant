<?php
/*

noserver/ r�pertoire � ne pas uploader sur le serveur
nogit/ r�pertoire � ne pas uploader sur git

lib.php
	diverses fonctions utiles

fieldsbdd.php
	Liste des constantes PHP correspondant aux noms des champs/table SQL
	(Cf noserver\gendbcst.php pour possibilit� de g�n�ration automatique)

exceptions.php
	D�finition des exceptions et des fonctions permettant de lever
	une erreur (automatiquement converti en json)

sports.php
	Constantes qui associe un num�ro � chaque sport

config.php
	D�finition de constantes
	Appels de toutes config.php des sous-dossiers


entree_json.php
	point d'entr�e des requ�tes en json
entree_post.php
	point d'entr�e des reque^tes en post
requete.php
	Fonctions g�rant les requ�tes re�us
dbinteraction.php
	Fonctions qui dictent ce qu'il faut cr�er/s�lectionner/modifier dans la base de donn�e

db.php
	Fonctions utilitaires d'interaction directe avec la bdd

La relation de d�pendance est la suivante :

entree_post --> requete --> dbinteraction --> db




*** A supprimer ***
db_maintenance\
	pour maintenir la synchronisation de la structure de la bdd entre serveur et local
	id�e :
	- on modifie la structure en local (phpmyadmin)
	- appel de extract.php en local -> cr�e un fichier schema.php
	- upload de schema.php
	- appel de update_db.php sur le serveur

	Il y a un syst�me de versioning. Mais l� extract incr�mente la version � chaque fois sans v�rifier qu'il y a des changements




*/
