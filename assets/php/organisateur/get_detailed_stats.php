<?php
session_start();
require_once '../connexion.php';

try {
    $organisateur_id = $_SESSION['user']['id'];
    
    // Total des événements
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM evenement WHERE organisateur_id = ?');
    $stmt->execute([$organisateur_id]);
    $totalEvents = $stmt->fetchColumn();

    // Événements de la dernière semaine
    $stmt = $bdd->prepare('
        SELECT COUNT(*) 
        FROM evenement 
        WHERE organisateur_id = ? 
        AND date BETWEEN DATE_SUB(NOW(), INTERVAL 1 WEEK) AND NOW()
    ');
    $stmt->execute([$organisateur_id]);
    $lastWeekEvents = $stmt->fetchColumn();

    // Événements du dernier mois
    $stmt = $bdd->prepare('
        SELECT COUNT(*) 
        FROM evenement 
        WHERE organisateur_id = ? 
        AND date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()
    ');
    $stmt->execute([$organisateur_id]);
    $lastMonthEvents = $stmt->fetchColumn();

    // Événements à venir dans la semaine
    $stmt = $bdd->prepare('SELECT COUNT(*) FROM evenement WHERE organisateur_id = ? AND date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 WEEK)');
    $stmt->execute([$organisateur_id]);
    $nextWeekEvents = $stmt->fetchColumn();

    // Prix moyen des événements
    $stmt = $bdd->prepare('SELECT AVG(prix) FROM evenement WHERE organisateur_id = ?');
    $stmt->execute([$organisateur_id]);
    $avgPrice = round($stmt->fetchColumn(), 2);

    // Statistiques des participants
    $stmt = $bdd->prepare('
        SELECT 
    COUNT(pe.utilisateur_id) as total_participants,
    COUNT(DISTINCT pe.evenement_id) as nb_events,
    AVG(YEAR(CURDATE()) - YEAR(u.date_naissance)) as age_moyen,
    COUNT(pe.utilisateur_id) / COUNT(DISTINCT pe.evenement_id) as avg_participants_per_event
FROM participants_evenements pe
JOIN utilisateur u ON pe.utilisateur_id = u.id
JOIN evenement e ON pe.evenement_id = e.id
WHERE e.organisateur_id = ?
    ');
    $stmt->execute([$organisateur_id]);
    $participantsStats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'totalEvents' => $totalEvents,
        'lastWeekEvents' => $lastWeekEvents,
        'lastMonthEvents' => $lastMonthEvents,
        'nextWeekEvents' => $nextWeekEvents,
        'avgPrice' => $avgPrice,
        'totalParticipants' => $participantsStats['total_participants'] ?? 0,
        'avgParticipants' => round($participantsStats['avg_participants_per_event'] ?? 0, 1),
        'avgAge' => round($participantsStats['age_moyen'] ?? 0, 1)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 