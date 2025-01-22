<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'connexion.php';
require_once 'auth.php';

try {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        throw new Exception('Token non fourni');
    }

    $auth = new Auth($bdd);
    if ($auth->invalidateToken($token)) {
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    } else {
        throw new Exception('Erreur lors de la déconnexion');
    }

} catch (Exception $e) {
    error_log("Erreur dans logout.php : " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 