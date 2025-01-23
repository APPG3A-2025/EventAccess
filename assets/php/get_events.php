<?php
header('Content-Type: application/json');
require_once 'connexion.php';

try {
    $query = $bdd->query('SELECT id, nom, date, ville, categorie FROM evenement ORDER BY date DESC');
    $events = $query->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 