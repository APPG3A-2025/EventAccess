<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

try {
    if (!isset($_POST['event_id'])) {
        throw new Exception('ID de l\'événement manquant');
    }

    $event_id = (int)$_POST['event_id'];
    $user_id = $_SESSION['user']['id'];

    // Supprimer l'inscription
    $stmt = $bdd->prepare('
        DELETE FROM participants_evenements 
        WHERE evenement_id = ? AND utilisateur_id = ?
    ');
    $stmt->execute([$event_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Vous n\'êtes pas inscrit à cet événement');
    }

    echo json_encode(['success' => true, 'message' => 'Désinscription réussie']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 