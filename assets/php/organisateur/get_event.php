<?php
session_start();
require_once '../connexion.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de l\'événement non spécifié');
    }

    $stmt = $bdd->prepare('
        SELECT * FROM evenement 
        WHERE id = ? AND organisateur_id = ?
    ');
    $stmt->execute([$_GET['id'], $_SESSION['user']['id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement non trouvé');
    }

    echo json_encode([
        'success' => true,
        'event' => $event
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 