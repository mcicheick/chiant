<?php

require_once 'config.php';
require_once "requete.php";

try {
echo (json_encode(dispatchReq($_REQUEST))); 
} catch (Exception $e) {
    if (!HERMETIQUE)
        echo 'Exception re�ue : ',  $e->getMessage(), "\n";
}

