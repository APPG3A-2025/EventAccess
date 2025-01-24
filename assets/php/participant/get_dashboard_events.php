<?php
session_start();
require_once '../connexion.php';

try {
    $user_id = $_SESSION['user']['id'];
    $response = [];

    // Récupérer les événements recommandés (basés sur la ville de l'utilisateur)
    $stmt = $bdd->prepare('
        SELECT e.*, u.prenom as organisateur_prenom, u.nom as organisateur_nom,
        (SELECT COUNT(*) FROM participants_evenements WHERE evenement_id = e.id AND utilisateur_id = ?) as is_registered
        FROM evenement e
        LEFT JOIN utilisateur u ON e.organisateur_id = u.id
        WHERE e.date >= CURRENT_DATE
        ORDER BY e.date ASC
        LIMIT 6
    ');
    $stmt->execute([$user_id]);
    $response['recommended'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les événements du jour auxquels l'utilisateur est inscrit
    $stmt = $bdd->prepare('
        SELECT e.*, u.prenom as organisateur_prenom, u.nom as organisateur_nom
        FROM evenement e
        JOIN participants_evenements pe ON e.id = pe.evenement_id
        LEFT JOIN utilisateur u ON e.organisateur_id = u.id
        WHERE pe.utilisateur_id = ?
        AND DATE(e.date) = CURRENT_DATE
        ORDER BY e.date ASC
    ');
    $stmt->execute([$user_id]);
    $response['today'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 