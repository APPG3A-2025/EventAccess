<?php
session_start();
require_once '../connexion.php';

try {
    $user_id = $_SESSION['user']['id'];
    
    // Récupérer les événements du jour
    $stmt = $bdd->prepare('
        SELECT e.*, 
               1 as is_registered
        FROM evenement e
        JOIN participants_evenements pe ON e.id = pe.evenement_id
        WHERE pe.utilisateur_id = ?
        AND DATE(e.date) = CURDATE()
        ORDER BY e.date ASC
    ');
    $stmt->execute([$user_id]);
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