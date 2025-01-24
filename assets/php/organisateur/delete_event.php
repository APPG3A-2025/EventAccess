<?php
session_start();
require_once '../connexion.php';
require_once '../utils/mailer.php';

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

    // Récupérer les participants avant la suppression
    $stmt = $bdd->prepare('
        SELECT u.email, e.nom 
        FROM utilisateur u 
        JOIN participants_evenements pe ON u.id = pe.utilisateur_id 
        JOIN evenement e ON pe.evenement_id = e.id 
        WHERE e.id = ?
    ');
    $stmt->execute([$_POST['event_id']]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Commencer une transaction
    $bdd->beginTransaction();

    // Supprimer d'abord les inscriptions à l'événement
    $stmt = $bdd->prepare('
        DELETE FROM participants_evenements 
        WHERE evenement_id = ?
    ');
    $stmt->execute([$_POST['event_id']]);

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

    // Valider la transaction
    $bdd->commit();

    // Notifier les participants après la suppression
    foreach ($participants as $participant) {
        $htmlContent = "
            <h2>Annulation d'événement</h2>
            <p>L'événement {$participant['nom']} auquel vous étiez inscrit a été annulé.</p>
            <p>Nous vous prions de nous excuser pour ce désagrément.</p>
        ";
        
        sendEventEmail($participant['email'], "Annulation de l'événement - {$participant['nom']}", $htmlContent);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Événement supprimé avec succès'
    ]);

} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    if ($bdd->inTransaction()) {
        $bdd->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 