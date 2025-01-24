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
        throw new Exception('ID événement non spécifié');
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('
        SELECT id, nom, categorie, date, ville, code_postal, adresse, prix, description 
        FROM evenement 
        WHERE id = ?
    ');
    $stmt->execute([$_GET['id']]);
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        throw new Exception('Événement non trouvé');
    }
    
    echo json_encode($event);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 