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

    $data = $_POST;
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email et mot de passe requis');
    }

    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Vérifier les identifiants
    $stmt = $bdd->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Email non trouvé');
    }

    if (!password_verify($data['password'], $user['mot_de_passe'])) {
        throw new Exception('Mot de passe incorrect');
    }

    // Générer un nouveau token
    $auth = new Auth($bdd);
    $token = $auth->generateToken($user['id']);

    if (!$token) {
        throw new Exception('Erreur lors de la génération du token');
    }

    // Nettoyer toute sortie précédente
    ob_clean();

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie !',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'prenom' => $user['prenom'],
            'nom' => $user['nom'],
            'type_compte' => $user['type_compte']
        ]
    ]);

} catch (Exception $e) {
    error_log("Erreur dans login.php : " . $e->getMessage());
    http_response_code(400);
    
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