<?php
session_start();
require_once '../connexion.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user']['id'];
    
    $query = "
        SELECT e.*, 
               CASE WHEN pe.utilisateur_id IS NOT NULL THEN 1 ELSE 0 END as is_registered
        FROM evenement e
        LEFT JOIN participants_evenements pe ON e.id = pe.evenement_id AND pe.utilisateur_id = ?
        WHERE e.date >= NOW()
    ";
    $params = [$user_id];

    // Ajouter les filtres
    if (!empty($data['query'])) {
        $query .= " AND (e.nom LIKE ? OR e.description LIKE ?)";
        $params[] = "%{$data['query']}%";
        $params[] = "%{$data['query']}%";
    }

    if (!empty($data['category'])) {
        $query .= " AND e.categorie = ?";
        $params[] = $data['category'];
    }

    if (!empty($data['date'])) {
        $query .= " AND DATE(e.date) = ?";
        $params[] = $data['date'];
    }

    if (!empty($data['city'])) {
        $query .= " AND TRIM(e.code_postal) = ?";
        $params[] = trim($data['city']);
    }

    $query .= " ORDER BY e.date ASC";

    $stmt = $bdd->prepare($query);
    $stmt->execute($params);
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