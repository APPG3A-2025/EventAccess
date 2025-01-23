<?php


// Connexion à la base de données
require_once '../connexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }


    // Validation des données
    if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email invalide');
    }

    if (!isset($_POST['password']) || strlen($_POST['password']) < 8) {
        throw new Exception('Mot de passe invalide');
    }

    // Vérification si l'email existe déjà
    $stmt = $bdd->prepare('SELECT id FROM utilisateur WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Hashage du mot de passe
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insertion dans la base de données
    $stmt = $bdd->prepare('
        INSERT INTO utilisateur (
            email, 
            mot_de_passe, 
            role,
            prenom,
            nom,
            date_naissance,
            telephone,
            code_postal,
            date_inscription
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');

    $stmt->execute([
        $_POST['email'],
        $hashedPassword,
        $_POST['account_type'],
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['birthdate'],
        $_POST['phone'],
        $_POST['postal_code'],
    ]);

   header('Location: ../../../pages/auth/login.php');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}