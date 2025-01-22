<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Si c'est une requête OPTIONS, on arrête ici
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'connexion.php';
require_once 'auth.php';

try {
    // Récupérer le token du header Authorization
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        throw new Exception('Token non fourni');
    }

    // Vérifier le token
    $auth = new Auth($bdd);
    $userData = $auth->verifyTokenAndGetUser($token);

    if ($userData) {
        // Token valide
        echo json_encode([
            'success' => true,
            'user' => $userData
        ]);
    } else {
        // Token invalide
        echo json_encode([
            'success' => false,
            'message' => 'Token invalide'
        ]);
    }

} catch (Exception $e) {
    error_log("Erreur dans check_token.php : " . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 