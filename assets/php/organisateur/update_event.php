<?php
session_start();
require_once '../connexion.php';
require_once '../utils/mailer.php';

try {
    if (!isset($_POST['event_id'])) {
        throw new Exception('ID de l\'événement non spécifié');
    }

    // Vérifier que l'événement appartient à l'organisateur
    $stmt = $bdd->prepare('
        SELECT * FROM evenement 
        WHERE id = ? AND organisateur_id = ?
    ');
    $stmt->execute([$_POST['event_id'], $_SESSION['user']['id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Événement non trouvé ou non autorisé');
    }

    // Traitement de l'image si une nouvelle est uploadée
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../../uploads/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Format d\'image non autorisé');
        }

        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        $image_path = $new_filename;
    }

    // Construction de la requête de mise à jour
    $sql = 'UPDATE evenement SET 
            nom = ?, 
            categorie = ?, 
            date = ?, 
            ville = ?, 
            code_postal = ?, 
            adresse = ?, 
            prix = ?, 
            description = ?';
    
    $params = [
        $_POST['nom'],
        $_POST['categorie'],
        $_POST['date'] . ' ' . $_POST['time'],
        $_POST['ville'],
        $_POST['code_postal'],
        $_POST['adresse'],
        $_POST['prix'],
        $_POST['description']
    ];

    if ($image_path) {
        $sql .= ', image = ?';
        $params[] = $image_path;
    }

    $sql .= ' WHERE id = ? AND organisateur_id = ?';
    $params[] = $_POST['event_id'];
    $params[] = $_SESSION['user']['id'];

    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);

    // Notifier les participants
    $stmt = $bdd->prepare('
        SELECT u.email 
        FROM utilisateur u 
        JOIN participants_evenements pe ON u.id = pe.utilisateur_id 
        WHERE pe.evenement_id = ?
    ');
    $stmt->execute([$_POST['event_id']]);
    $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($participants as $email) {
        $htmlContent = "
            <h2>Modification d'un événement</h2>
            <p>L'événement {$_POST['nom']} auquel vous êtes inscrit a été modifié.</p>
            <p>Nouveaux détails :</p>
            <ul>
                <li>Date : " . date('d/m/Y H:i', strtotime($_POST['date'] . ' ' . $_POST['time'])) . "</li>
                <li>Lieu : {$_POST['ville']} ({$_POST['code_postal']})</li>
                <li>Adresse : {$_POST['adresse']}</li>
                <li>Prix : {$_POST['prix']}€</li>
                <li>Catégorie : {$_POST['categorie']}</li>
                <li>Description : {$_POST['description']}</li>
            </ul>
            <p>Si ces modifications ne vous conviennent pas, vous pouvez vous désinscrire de l'événement depuis votre espace personnel.</p>
        ";
        
        sendEventEmail($email, "Modification de l'événement - {$_POST['nom']}", $htmlContent);
    }

    header('Location:../../../pages/organisateur/home_organisateur.php');

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 