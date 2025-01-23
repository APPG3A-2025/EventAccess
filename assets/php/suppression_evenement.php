<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'connexion.php';

try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    if (empty($_POST['id'])) {
        throw new Exception('Identifiant de l\'événement manquant');
    }

    // Récupération de l'ID
    $eventId = intval($_POST['id']);

    // Vérification si l'événement existe
    $checkQuery = $bdd->prepare('SELECT image FROM evenement WHERE id = ?');
    $checkQuery->execute([$eventId]);
    $event = $checkQuery->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement introuvable');
    }

    // Suppression du fichier image si présent
    if (!empty($event['image']) && file_exists('../../' . $event['image'])) {
        if (!unlink('../../' . $event['image'])) {
            error_log("Échec de la suppression de l'image : " . $event['image']);
        } else {
            error_log("Image supprimée : " . $event['image']);
        }
    }

    // Suppression de l'événement dans la base de données
    $deleteQuery = $bdd->prepare('DELETE FROM evenement WHERE id = ?');
    $success = $deleteQuery->execute([$eventId]);

    if (!$success) {
        throw new Exception('Erreur lors de la suppression de l\'événement');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Événement supprimé avec succès !'
    ]);

} catch (Exception $e) {
    error_log("Erreur dans suppression_evenement.php : " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression de l\'événement : ' . $e->getMessage()
    ]);
}
?>