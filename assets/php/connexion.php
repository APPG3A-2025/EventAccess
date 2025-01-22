<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=event_access;charset=utf8', 'root', 'root');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Connexion à la base de données réussie");
} catch(Exception $e) {
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
    ]));
}
?> 