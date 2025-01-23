<?php
session_start();
require_once '../connexion.php';

try {
    if (!isset($_POST['event_id'])) {
        throw new Exception('ID de l\'événement non spécifié');
    }

    // Récupérer l'image de l'événement avant la suppression
    $stmt = $bdd->prepare('
        SELECT image FROM evenement 
        WHERE id = ? AND organisateur_id = ?
    ');
    $stmt->execute([$_POST['event_id'], $_SESSION['user']['id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement non trouvé ou non autorisé');
    }

    // Supprimer l'image si elle existe
    if ($event['image']) {
        $image_path = '../../../uploads/images/' . $event['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Supprimer l'événement
    $stmt = $bdd->prepare('
        DELETE FROM evenement 
        WHERE id = ? AND organisateur_id = ?
    ');
    $stmt->execute([$_POST['event_id'], $_SESSION['user']['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Événement supprimé avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 