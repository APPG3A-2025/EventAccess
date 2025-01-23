<?php
header('Content-Type: application/json');
require_once 'connexion.php';
require_once 'auth.php';

try {
    // Vérification du token
    $headers = getallheaders();
    $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

    if (empty($token)) {
        throw new Exception('Token non fourni');
    }

    // Vérifier l'utilisateur
    $stmt = $bdd->prepare('SELECT id FROM utilisateur WHERE token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Utilisateur non trouvé');
    }

    // Récupération et validation des données
    $nom = filter_var($_POST['titre'] ?? '', FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
    $date = $_POST['date'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $lieu = filter_var($_POST['lieu'] ?? '', FILTER_SANITIZE_STRING);
    $places = filter_var($_POST['places'] ?? 0, FILTER_VALIDATE_INT);
    $categorie = filter_var($_POST['categorie'] ?? '', FILTER_SANITIZE_STRING);
    $prix = filter_var($_POST['prix'] ?? 0, FILTER_VALIDATE_FLOAT);

    // Validation des données obligatoires
    if (empty($nom) || empty($description) || empty($date) || empty($heure) || empty($lieu) || empty($categorie)) {
        throw new Exception('Tous les champs obligatoires doivent être remplis');
    }

    // Validation supplémentaire
    if ($places <= 0) {
        throw new Exception('Le nombre de places doit être supérieur à 0');
    }
    if ($prix < 0) {
        throw new Exception('Le prix ne peut pas être négatif');
    }

    // Traitement de l'image
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            throw new Exception('Format d\'image non autorisé (jpg, jpeg, png, gif uniquement)');
        }

        // Création du dossier uploads s'il n'existe pas
        $upload_dir = "../uploads/events";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_name = uniqid() . "." . $ext;
        $upload_path = $upload_dir . "/" . $upload_name;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        $image_path = $upload_name;
    }

    // Insertion dans la base de données
    $stmt = $bdd->prepare("
        INSERT INTO evenement (
            id_organisateur,
            nom_evenement,
            description_evenement,
            date_evenement,
            heure_evenement,
            lieu_evenement,
            places_totales,
            places_restantes,
            categorie,
            image_evenement,
            prix,
            statut
        ) VALUES (
            :id_organisateur,
            :nom,
            :description,
            :date,
            :heure,
            :lieu,
            :places,
            :places,
            :categorie,
            :image,
            :prix,
            'actif'
        )
    ");

    $success = $stmt->execute([
        'id_organisateur' => $user['id'],
        'nom' => $nom,
        'description' => $description,
        'date' => $date,
        'heure' => $heure,
        'lieu' => $lieu,
        'places' => $places,
        'categorie' => $categorie,
        'image' => $image_path,
        'prix' => $prix
    ]);

    if (!$success) {
        throw new Exception('Erreur lors de l\'insertion dans la base de données');
    }

    $eventId = $bdd->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Événement créé avec succès',
        'eventId' => $eventId
    ]);

} catch (Exception $e) {
    error_log("Erreur création événement : " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 