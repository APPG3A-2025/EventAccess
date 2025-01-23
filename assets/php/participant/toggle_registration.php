<?php
session_start();
require_once '../connexion.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user']['id'];
    $event_id = $data['event_id'];

    // Vérifier si l'événement existe et n'est pas passé
    $stmt = $bdd->prepare('SELECT * FROM evenement WHERE id = ? AND date >= NOW()');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception('Événement non trouvé ou déjà passé');
    }

    // Vérifier si l'utilisateur est déjà inscrit
    $stmt = $bdd->prepare('
        SELECT * FROM participants_evenements 
        WHERE utilisateur_id = ? AND evenement_id = ?
    ');
    $stmt->execute([$user_id, $event_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Désinscrire
        $stmt = $bdd->prepare('
            DELETE FROM participants_evenements 
            WHERE utilisateur_id = ? AND evenement_id = ?
        ');
        $stmt->execute([$user_id, $event_id]);
        $message = 'Désinscription réussie';
    } else {
        // Inscrire
        $stmt = $bdd->prepare('
            INSERT INTO participants_evenements (utilisateur_id, evenement_id) 
            VALUES (?, ?)
        ');
        $stmt->execute([$user_id, $event_id]);
        $message = 'Inscription réussie';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'is_registered' => !$existing
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 