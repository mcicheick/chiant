<?php

require_once 'config.php';
require_once "requete.php";

try {
echo (json_encode(dispatchReq($_REQUEST))); 
} catch (Exception $e) {
    if (!HERMETIQUE)
    {
    echo "<pre>Exception recue :\n";
        throw $e;
    }
        //echo 'Exception recue : ',  $e->getMessage(), "\n";
}

