<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la mise en tampon de sortie
ob_start();

header('Content-Type: application/json');
require_once 'connexion.php';
require_once 'auth.php';

try {
    // Log dans un fichier plutôt que dans la sortie
    error_log("Début du traitement de login.php");
    error_log("Méthode HTTP : " . $_SERVER['REQUEST_METHOD']);
    error_log("Données POST reçues : " . print_r($_POST, true));

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Email et mot de passe requis');
    }

    $stmt = $bdd->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('Email ou mot de passe incorrect');
    }

    // Générer un nouveau token
    $auth = new Auth($bdd);
    $token = $auth->generateToken($user['id']);

    if ($token) {
        // Nettoyer toute sortie précédente
        ob_clean();

        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'prenom' => $user['prenom'],
                'nom' => $user['nom'],
                'type_compte' => $user['type_compte']
            ]
        ]);
    } else {
        throw new Exception('Erreur lors de la génération du token');
    }

} catch (Exception $e) {
    error_log("Erreur dans login.php : " . $e->getMessage());
    http_response_code(401);
    
    // Nettoyer toute sortie précédente
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Envoyer et terminer la sortie
ob_end_flush();
?> 