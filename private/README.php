<?php

/*

Ce répertoire contient des fichiers qui doivent être sur le serveur mais pas
accessible aux utilisateurs (seulement par l'interpréteur PHP lui-même)

Pour plus de sécurité, il vaut mieux utiliser le répertoire noserver

routes : contient la liste des routes au format json
  clé : nom de la requête
  fun : fonction à appeler depuis requete.php
  params : les noms des paramètres de la fonction
  file : éventuellement : le nom associé au fichier uploadé dans $_FILES
*/
