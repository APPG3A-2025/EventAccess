<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'connexion.php';

try {
    // Récupération des données du formulaire
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    // Création des objets DateTime
    $eventDateTime = new DateTime($date . ' ' . $time);
    $now = new DateTime();

    // Vérification si la date est dans le futur
    if ($eventDateTime <= $now) {
        throw new Exception('La date et l\'heure de l\'événement doivent être ultérieures à maintenant');
    }

    // Gestion de l'upload d'image
    $image_path = null;
    if(isset($_FILES['image'])) {
        error_log("Image détectée : " . print_r($_FILES['image'], true));
        
        if($_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            error_log("Extension du fichier : " . $file_ext);

            if(!in_array($file_ext, $allowed)) {
                throw new Exception('Format d\'image non autorisé. Formats acceptés : JPG, JPEG, PNG, GIF');
            }

            // Création d'un nom de fichier unique
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_dir = '../../uploads/images/';

            // Vérifier si le dossier existe, sinon le créer
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    error_log("Échec de la création du dossier : " . $upload_dir);
                    throw new Exception('Erreur lors de la création du dossier d\'upload');
                }
            }

            $target_path = $upload_dir . $new_filename;
            error_log("Tentative d'upload vers : " . $target_path);

            // Upload du fichier
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                error_log("Échec de l'upload. Erreur PHP : " . error_get_last()['message']);
                throw new Exception('Erreur lors de l\'upload de l\'image');
            }

            $image_path = 'uploads/images/' . $new_filename;
            error_log("Upload réussi. Chemin enregistré : " . $image_path);
        } else {
            error_log("Erreur lors de l'upload : " . $_FILES['image']['error']);
        }
    }

    // Vérification des champs requis
    if(empty($_POST['name']) || empty($_POST['category']) || empty($_POST['date'])) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    // Récupération des données du formulaire
    $name = htmlspecialchars($_POST['name']);
    $category = htmlspecialchars($_POST['category']);
    $date = $_POST['date'];
    $time = isset($_POST['time']) ? $_POST['time'] : '00:00';
    $city = htmlspecialchars($_POST['city']);
    $postal = htmlspecialchars($_POST['postal']);
    $address = htmlspecialchars($_POST['address']);
    $price = floatval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);

    // Concaténation de la date et de l'heure
    $datetime = $date . ' ' . $time;

    // Debug: afficher la requête
    error_log("Tentative d'insertion avec les données : " . print_r([
        $name, $category, $datetime, $city, $postal, $address, $price, $description, $image_path
    ], true));

    // Préparation de la requête
    $req = $bdd->prepare('INSERT INTO evenement (nom, categorie, date, ville, code_postal, adresse, prix, description, image) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    
    // Exécution de la requête
    $success = $req->execute([
        $name,
        $category,
        $datetime,
        $city,
        $postal,
        $address,
        $price,
        $description,
        $image_path
    ]);

    if(!$success) {
        throw new Exception('Erreur lors de l\'exécution de la requête SQL');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Événement créé avec succès !'
    ]);

} catch(Exception $e) {
    error_log("Erreur dans creation_evenement.php : " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création de l\'événement : ' . $e->getMessage()
    ]);
}
?> 