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

    // Vérifier si l'événement existe et n'est pas passé
    $stmt = $bdd->prepare('SELECT date FROM evenement WHERE id = ?');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception('Événement non trouvé');
    }

    if (strtotime($event['date']) < time()) {
        throw new Exception('Cet événement est déjà passé');
    }

    // Vérifier si l'utilisateur n'est pas déjà inscrit
    $stmt = $bdd->prepare('
        SELECT id FROM participants_evenements 
        WHERE evenement_id = ? AND utilisateur_id = ? AND statut = "confirmé"
    ');
    $stmt->execute([$event_id, $user_id]);
    
    if ($stmt->fetch()) {
        throw new Exception('Vous êtes déjà inscrit à cet événement');
    }

    // Inscrire l'utilisateur
    $stmt = $bdd->prepare('
        INSERT INTO participants_evenements (evenement_id, utilisateur_id) 
        VALUES (?, ?)
    ');
    $stmt->execute([$event_id, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Inscription réussie']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 