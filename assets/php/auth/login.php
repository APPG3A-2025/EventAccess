<?php
session_start();

// Connexion à la base de données
require_once '../connexion.php';

try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    // Récupération et validation des données
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception('Veuillez remplir tous les champs');
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Recherche de l'utilisateur dans la base de données
    $stmt = $bdd->prepare('SELECT id, email, mot_de_passe, role, prenom FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($_POST['password'], $user['mot_de_passe'])) {
        throw new Exception('Email ou mot de passe incorrect');
    }

    // Création de la session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'prenom' => $user['prenom']
    ];

    // Gestion du "Se souvenir de moi"
    try {
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $stmt = $bdd->prepare('INSERT INTO remember_tokens (user_id, token, expiry) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $token, $expiry]);
            
            setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
        }
    } catch (Exception $e) {
        error_log("Erreur remember_tokens : " . $e->getMessage());
        // On continue même si le "Se souvenir de moi" échoue
    }

    // Après une connexion réussie, avant la redirection
    if (isset($_SESSION['redirectAfterLogin'])) {
        $redirect = $_SESSION['redirectAfterLogin'];
        unset($_SESSION['redirectAfterLogin']);
        header('Location: ' . $redirect);
        exit();
    }

    // Sinon, redirection normale selon le rôle
    switch($user['role']) {
        case 'organisateur':
            header('Location: ../../../pages/organisateur/home_organisateur.php');
            break;
        case 'utilisateur':
            header('Location: ../../../pages/participant/home_participant.php');
            break;
        default:
            throw new Exception('Type de compte non reconnu');
    }
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../../../pages/auth/login.php');
    exit();
} 