<?php
session_start();
require_once '../connexion.php';

try {
    $organisateur_id = $_SESSION['user']['id'];
    
    // Nombre d'événements actifs
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM evenement WHERE organisateur_id = ? AND date >= NOW()');
    $stmt->execute([$organisateur_id]);
    $activeEvents = $stmt->fetchColumn();

    // Prochain événement
    $stmt = $bdd->prepare('SELECT nom, date FROM evenement WHERE organisateur_id = ? AND date >= NOW() ORDER BY date ASC LIMIT 1');
    $stmt->execute([$organisateur_id]);
    $nextEvent = $stmt->fetch(PDO::FETCH_ASSOC);

    // Total des participants pour tous les événements de l'organisateur
    $stmt = $bdd->prepare('
        SELECT COUNT(DISTINCT pe.utilisateur_id) 
        FROM evenement e
        JOIN participants_evenements pe ON e.id = pe.evenement_id
        WHERE e.organisateur_id = ?
    ');
    $stmt->execute([$organisateur_id]);
    $totalParticipants = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'activeEvents' => $activeEvents,
        'nextEvent' => $nextEvent ? $nextEvent['nom'] . ' - ' . date('d/m/Y H:i', strtotime($nextEvent['date'])) : null,
        'totalParticipants' => $totalParticipants
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 