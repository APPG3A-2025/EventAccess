<?php
session_start();
require_once '../connexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Validation des données
    $required_fields = ['nom', 'categorie', 'date', 'time', 'ville', 'code_postal', 'adresse', 'prix', 'description'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }

    // Traitement de l'image
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../../uploads/events/';
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

        $image_path = 'uploads/events/' . $new_filename;
    }

    // Formatage de la date et l'heure
    $datetime = date('Y-m-d H:i:s', strtotime($_POST['date'] . ' ' . $_POST['time']));

    // Insertion dans la base de données
    $stmt = $bdd->prepare('
        INSERT INTO evenement (
            nom, 
            categorie, 
            date, 
            ville, 
            code_postal, 
            adresse, 
            prix, 
            description, 
            image,
            organisateur_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $_POST['nom'],
        $_POST['categorie'],
        $datetime,
        $_POST['ville'],
        $_POST['code_postal'],
        $_POST['adresse'],
        $_POST['prix'],
        $_POST['description'],
        $image_path,
        $_SESSION['user']['id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Événement créé avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 