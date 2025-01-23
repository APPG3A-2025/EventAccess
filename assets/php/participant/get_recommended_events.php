<?php
session_start();
require_once '../connexion.php';

try {
    $user_id = $_SESSION['user']['id'];
    
    // Récupérer le code postal de l'utilisateur
    $stmt = $bdd->prepare('SELECT code_postal FROM utilisateur WHERE id = ?');
    $stmt->execute([$user_id]);
    $userPostalCode = $stmt->fetchColumn();
    
    // Extraire les 3 premiers chiffres du code postal
    $postalPrefix = substr($userPostalCode, 0, 3);

    // Récupérer les événements recommandés
    $stmt = $bdd->prepare('
        SELECT e.*, 
               CASE WHEN pe.utilisateur_id IS NOT NULL THEN 1 ELSE 0 END as is_registered
        FROM evenement e
        LEFT JOIN participants_evenements pe ON e.id = pe.evenement_id AND pe.utilisateur_id = ?
        WHERE LEFT(e.code_postal, 3) = ? 
        AND e.date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 WEEK)
        ORDER BY e.date ASC
    ');
    $stmt->execute([$user_id, $postalPrefix]);
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