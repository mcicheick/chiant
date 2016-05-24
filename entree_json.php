<?php

require_once "requete.php";
try{
echo (json_encode(dispatchJson($_REQUEST['q'])));

} catch (Exception $e) {
    echo 'Exception reçue : ',  $e->getMessage(), "\n";
}
