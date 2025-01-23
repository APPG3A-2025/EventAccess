<?php
session_start();
require_once '../connexion.php';

try {
    $organisateur_id = $_SESSION['user']['id'];
    
    $stmt = $bdd->prepare('
        SELECT * FROM evenement 
        WHERE organisateur_id = ? 
        ORDER BY date DESC
    ');
    $stmt->execute([$organisateur_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'events' => $events
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 