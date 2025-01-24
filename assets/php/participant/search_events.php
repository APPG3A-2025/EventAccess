<?php
session_start();
require_once '../connexion.php';

try {
    $user_id = $_SESSION['user']['id'];
    $query = isset($_POST['query']) ? '%' . $_POST['query'] . '%' : '%';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $city = isset($_POST['city']) ? $_POST['city'] : '';

    $sql = "
        SELECT e.*, u.prenom as organisateur_prenom, u.nom as organisateur_nom,
        (SELECT COUNT(*) FROM participants_evenements 
         WHERE evenement_id = e.id AND utilisateur_id = ?) as is_registered
        FROM evenement e
        LEFT JOIN utilisateur u ON e.organisateur_id = u.id
        WHERE e.date >= CURRENT_DATE
        AND (e.nom LIKE ? OR e.description LIKE ?)
    ";
    $params = [$user_id, $query, $query];

    if ($category) {
        $sql .= " AND e.categorie = ?";
        $params[] = $category;
    }

    if ($date) {
        $sql .= " AND DATE(e.date) = ?";
        $params[] = $date;
    }

    if ($city) {
        $sql .= " AND e.code_postal LIKE ?";
        $params[] = $city . '%';
    }

    $sql .= " ORDER BY e.date ASC";

    $stmt = $bdd->prepare($sql);
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