<?php
session_start();
require_once '../connexion.php';

try {
    $user_id = $_SESSION['user']['id'];
    
    // Récupérer le code postal de l'utilisateur
    $stmt = $bdd->prepare('SELECT TRIM(code_postal) as code_postal FROM utilisateur WHERE id = ?');
    $stmt->execute([$user_id]);
    $userPostalCode = $stmt->fetchColumn();
    
    // Debug log
    error_log("Code postal utilisateur: " . $userPostalCode);

    // Récupérer les événements recommandés avec debug
    $stmt = $bdd->prepare('
        SELECT e.*, 
               CASE WHEN pe.utilisateur_id IS NOT NULL THEN 1 ELSE 0 END as is_registered
        FROM evenement e
        LEFT JOIN participants_evenements pe ON e.id = pe.evenement_id AND pe.utilisateur_id = ?
        WHERE TRIM(e.code_postal) = ? 
        AND e.date >= NOW() 
        AND e.date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
        ORDER BY e.date ASC
    ');
    $stmt->execute([$user_id, $userPostalCode]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug log
    error_log("Nombre d'événements trouvés: " . count($events));
    error_log("Requête SQL: " . $stmt->queryString);
    error_log("Paramètres: user_id=" . $user_id . ", code_postal=" . $userPostalCode);

    echo json_encode([
        'success' => true,
        'events' => $events,
        'debug' => [
            'userPostalCode' => $userPostalCode,
            'eventCount' => count($events)
        ]
    ]);

} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 