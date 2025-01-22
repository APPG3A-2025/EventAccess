<?php
require_once 'connexion.php';
require_once 'verify_token.php';

header('Content-Type: application/json');

try {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $token);

    if (!verifyToken($bdd, $token)) {
        throw new Exception('Token invalide ou expiré');
    }

    // Code de la route protégée...

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 