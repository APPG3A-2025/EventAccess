<?php
session_start();
require_once '../connexion.php';

try {
    $user_id = $_SESSION['user']['id'];
    
    // Récupérer tous les événements auxquels l'utilisateur est inscrit
    $stmt = $bdd->prepare('
        SELECT e.*, u.prenom as organisateur_prenom, u.nom as organisateur_nom,
        pe.date_inscription, pe.statut,
        1 as is_registered
        FROM evenement e
        JOIN participants_evenements pe ON e.id = pe.evenement_id
        LEFT JOIN utilisateur u ON e.organisateur_id = u.id
        WHERE pe.utilisateur_id = ?
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