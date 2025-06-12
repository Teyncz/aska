<?php

include_once 'config.php'; 

if (!function_exists('__main_conn')) { // Vérifie si la fonction est déjà définie
    function __main_conn() {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
            DB_LOGIN,
            DB_PASSWORD
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        return $pdo;
    }
}
?>