<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'connexion.php';

try {
    // Log pour débugger
    error_log("Début du traitement de l'inscription");
    error_log("Données reçues : " . print_r($_POST, true));

    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Vérification des champs requis
    $required_fields = ['email', 'civilite', 'prenom', 'nom', 'telephone', 'date_naissance', 'code_postal', 'password', 'account-type'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }

    // Récupération et validation des données
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Email invalide');
    }

    // Vérification si l'email existe déjà
    $stmt = $bdd->prepare('SELECT id FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Validation et nettoyage des autres champs
    $civilite = $_POST['civilite'];
    $prenom = strip_tags(trim($_POST['prenom']));
    $nom = strip_tags(trim($_POST['nom']));
    $telephone = preg_replace('/[^0-9]/', '', $_POST['telephone']);
    $date_naissance = $_POST['date_naissance'];
    $code_postal = strip_tags(trim($_POST['code_postal']));
    $password = $_POST['password'];
    $type_compte = $_POST['account-type'];

    // Validation du numéro de téléphone (9 chiffres)
    if (strlen($telephone) !== 9) {
        throw new Exception('Le numéro de téléphone doit contenir 9 chiffres');
    }

    // Validation de la date de naissance
    $date_naissance_obj = new DateTime($date_naissance);
    $age = $date_naissance_obj->diff(new DateTime())->y;
    if ($age < 18) {
        throw new Exception('Vous devez avoir au moins 18 ans pour vous inscrire');
    }

    // Validation du code postal (5 chiffres)
    if (!preg_match('/^[0-9]{5}$/', $code_postal)) {
        throw new Exception('Code postal invalide');
    }

    // Hashage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Génération d'un token unique
    $token = bin2hex(random_bytes(32)); // Crée un token de 64 caractères

    // Préparation de la requête d'insertion avec le token
    $sql = "INSERT INTO utilisateur (
        civilite, prenom, nom, email, telephone, 
        date_naissance, code_postal, mot_de_passe, 
        type_compte, token, date_inscription
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    error_log("Requête SQL : " . $sql);

    $stmt = $bdd->prepare($sql);

    // Exécution de la requête avec le token
    $success = $stmt->execute([
        $civilite,
        $prenom,
        $nom,
        $email,
        $telephone,
        $date_naissance,
        $code_postal,
        $hashed_password,
        $type_compte,
        $token
    ]);

    if (!$success) {
        error_log("Erreur lors de l'exécution de la requête : " . print_r($stmt->errorInfo(), true));
        throw new Exception('Erreur lors de l\'enregistrement dans la base de données');
    }

    // Réponse avec le token
    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie !',
        'token' => $token,
        'redirect' => '../../index.html'
    ]);

} catch (Exception $e) {
    error_log("Erreur dans register.php : " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 