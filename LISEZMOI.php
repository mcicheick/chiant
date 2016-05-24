<?php
/*

noserver/ répertoire à ne pas uploader sur le serveur
nogit/ répertoire à ne pas uploader sur git

lib.php
	diverses fonctions utiles

fieldsbdd.php
	Liste des constantes PHP correspondant aux noms des champs/table SQL
	(Cf noserver\gendbcst.php pour possibilité de génération automatique)

exceptions.php
	Définition des exceptions et des fonctions permettant de lever
	une erreur (automatiquement converti en json)

sports.php
	Constantes qui associe un numéro à chaque sport

config.php
	Définition de constantes
	Appels de toutes config.php des sous-dossiers


entree_json.php
	point d'entrée des requêtes en json
entree_post.php
	point d'entrée des reque^tes en post
requete.php
	Fonctions gérant les requêtes reçus
dbinteraction.php
	Fonctions qui dictent ce qu'il faut créer/sélectionner/modifier dans la base de donnée

db.php
	Fonctions utilitaires d'interaction directe avec la bdd

La relation de dépendance est la suivante :

entree_post --> requete --> dbinteraction --> db




*** A supprimer ***
db_maintenance\
	pour maintenir la synchronisation de la structure de la bdd entre serveur et local
	idée :
	- on modifie la structure en local (phpmyadmin)
	- appel de extract.php en local -> crée un fichier schema.php
	- upload de schema.php
	- appel de update_db.php sur le serveur

	Il y a un système de versioning. Mais là extract incrémente la version à chaque fois sans vérifier qu'il y a des changements




*/
