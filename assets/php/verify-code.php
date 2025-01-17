<?php
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($password !== $confirm_password) {
        die('Les mots de passe ne correspondent pas.');
    }

    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Vérifie le code et l'expiration
    $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE reset_code = ? AND reset_code_expiry > NOW()');
    $stmt->execute([$code]);
    $user = $stmt->fetch();

    if ($user) {
        // Mets à jour le mot de passe
        $stmt = $pdo->prepare('UPDATE utilisateur SET mot_de_passe = ?, reset_code = NULL, reset_code_expiry = NULL WHERE reset_code = ?');
        $stmt->execute([$hashed_password, $code]);
        echo 'Mot de passe changé avec succès.';
    } else {
        echo 'Code invalide ou expiré.';
    }
}
?>
