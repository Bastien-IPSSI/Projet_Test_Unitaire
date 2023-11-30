<?php

require_once("./config.php");

try {
    $host = DBHOST;
    $user = DBUSER;
    $pwd = DBPWD;
    $db_name = DBNAME;

    $connexion = new PDO("mysql:host=$host;dbname=$db_name", $user, $pwd);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Erreur lors de la connexion à la database : " . $e->getMessage();
    die();
}



?>