<?php

Ce répertoire contient des outils/constantes à ne pas uploader sur le serveur
(exemple : variable env qui dit si on est en local ou sur le serveur)

config.php
	constantes
test.php
	Fichier générant des tests automatiquement (les tests sont des formulaires)
sessiontest.php
	Fichier testant les requêtes (les tests sont écrits en PHP)
gendbcst.php
	Génère des noms de constantes PHP correspondants aux champs/bdd de la base 
genfuns.php
	Génère des squelettes de fonctions PHP correspondant aux routes définies dans ../requete.php
newlistcmd.php
	Génère des squelettes de fonctions PHP pour lister une table et ajoute la requete dans le fichier des requetes
structure.sql
	Code SQL pour générer la structure de la base de données (sans données). ATTENTION !!! Supprime la base de données

routes.php
	Affiche la liste des requêtes possibles avec leur paramètres


