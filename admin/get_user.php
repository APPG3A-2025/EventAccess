<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

session_start();

$auth = new Auth();
$auth->requireAuth();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID utilisateur non spécifié');
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, email, prenom, nom, role FROM utilisateur WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Utilisateur non trouvé');
    }
    
    echo json_encode($user);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 