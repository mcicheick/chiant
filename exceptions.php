<?php
class MyExc extends Exception {
}

class HermetiqueExc extends Exception {
}


 define( 'ERR_DUPLICATE_ENTRY', 1);
 define( 'ERR_ERROR', 2);
 define('ERR_NOLOGIN', 3);
 define('ERR_FORBIDDEN', 4);
 define('ERR_BAD_CREDENTIALS',5);

 function raiseMyExc($msg, $code)
 {
     throw new MyExc($msg, $code);
 }

 function raiseHermetiqueExc($code, $msg) {
     throw new HermetiqueExc($msg, $code);
 }

function raiseDbInsertUnique() {
    raiseMyExc('Duplicate entry', ERR_DUPLICATE_ENTRY);
}

function raiseForbidden() {
    raiseMyExc('Forbidden', ERR_FORBIDDEN);
}

function raiseLoginNeeded() {
    raiseMyExc('Login needed', ERR_NOLOGIN);
}

function raiseBadCredentials() {
    raiseMyExc('Wrong email/password', ERR_BAD_CREDENTIALS);
}

class DbInsertUniqueExc extends Exception {
}
class ForbiddenExc extends Exception {
}
class NeedLoginExc extends Exception {
}
