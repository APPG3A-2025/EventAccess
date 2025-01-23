<?php
session_start();
require_once '../connexion.php';


try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user']['id'];
    $event_id = $data['event_id'];

    // Vérifier si l'événement est aujourd'hui
    $stmt = $bdd->prepare('
        SELECT e.* 
        FROM evenement e
        JOIN participants_evenements pe ON e.id = pe.evenement_id
        WHERE e.id = ? 
        AND pe.utilisateur_id = ?
        AND DATE(e.date) = CURDATE()
        AND e.date >= NOW()
    ');
    $stmt->execute([$event_id, $user_id]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception('Événement non trouvé ou annulation impossible');
    }

    // Annuler l'inscription
    $stmt = $bdd->prepare('
        DELETE FROM participants_evenements 
        WHERE utilisateur_id = ? AND evenement_id = ?
    ');
    $stmt->execute([$user_id, $event_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Participation annulée avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 