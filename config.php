<?php
require_once 'nogit/config.php';
require_once 'noserver/config.php';

// Si on génère des messages d'erreurs internes ou pas
define('SESSION_USERID_NAME', 'id_user');
define('MAGIC_USERID_NAME', 'id_magic_user');

define('ID_SUPER_USER', 1);
define('WARNINGS', true);
define('HERMETIQUE', ENV != 'LOCAL');

// Si on affiche chaque requête SQL
define('DEBUG_DUMP_SQL', 0);

define('PICTURES_DIR', 'near2u-pictures');
define('PRIVATE_DIR', 'private');

define('IMAGES_EXT', 'png jpg jpeg gif bmp');

//App ID
define('APP_ID', '956327381095428');
