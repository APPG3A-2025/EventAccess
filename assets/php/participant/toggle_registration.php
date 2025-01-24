<?php
session_start();
require_once '../connexion.php';
require_once '../utils/mailer.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user']['id'];
    $event_id = $data['event_id'];

    // Vérifier si l'événement existe et n'est pas passé
    $stmt = $bdd->prepare('SELECT * FROM evenement WHERE id = ? AND date >= NOW()');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception('Événement non trouvé ou déjà passé');
    }

    // Vérifier si l'utilisateur est déjà inscrit
    $stmt = $bdd->prepare('
        SELECT * FROM participants_evenements 
        WHERE utilisateur_id = ? AND evenement_id = ?
    ');
    $stmt->execute([$user_id, $event_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Désinscrire
        $stmt = $bdd->prepare('
            DELETE FROM participants_evenements 
            WHERE utilisateur_id = ? AND evenement_id = ?
        ');
        $stmt->execute([$user_id, $event_id]);
        $message = 'Désinscription réussie';
    } else {
        // Inscrire
        $stmt = $bdd->prepare('
            INSERT INTO participants_evenements (utilisateur_id, evenement_id) 
            VALUES (?, ?)
        ');
        $stmt->execute([$user_id, $event_id]);
        
        // Récupérer l'email de l'utilisateur et ses informations
        $stmt = $bdd->prepare('SELECT email, nom, prenom FROM utilisateur WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Récupérer l'email de l'organisateur
        $stmt = $bdd->prepare('
            SELECT u.email 
            FROM utilisateur u 
            JOIN evenement e ON u.id = e.organisateur_id 
            WHERE e.id = ?
        ');
        $stmt->execute([$event_id]);
        $organisateurEmail = $stmt->fetchColumn();

        // Email pour le participant
        $htmlContent = "
            <h2>Confirmation d'inscription à l'événement</h2>
            <p>Vous êtes maintenant inscrit à l'événement : {$event['nom']}</p>
            <p>Date : " . date('d/m/Y H:i', strtotime($event['date'])) . "</p>
            <p>Lieu : {$event['ville']} ({$event['code_postal']})</p>
            <p>Adresse : {$event['adresse']}</p>
            <p>Prix : {$event['prix']}€</p>
            <p>Description : {$event['description']}</p>
        ";
        
        sendEventEmail($user['email'], "Confirmation d'inscription - {$event['nom']}", $htmlContent);

        // Email pour l'organisateur
        $htmlContentOrganisateur = "
            <h2>Nouveau participant à votre événement</h2>
            <p>Un nouveau participant s'est inscrit à votre événement : {$event['nom']}</p>
            <p>Participant : {$user['prenom']} {$user['nom']}</p>
            <p>Email du participant : {$user['email']}</p>
            <p>Date de l'événement : " . date('d/m/Y H:i', strtotime($event['date'])) . "</p>
        ";

        sendEventEmail($organisateurEmail, "Nouveau participant - {$event['nom']}", $htmlContentOrganisateur);
        $message = 'Inscription réussie';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'is_registered' => !$existing
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 