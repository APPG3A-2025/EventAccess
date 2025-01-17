<?php
require 'connexion.php'; // Remplace par ton fichier de connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $code = random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Vérifie si l'email existe
    $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Mets à jour le code de réinitialisation
        $stmt = $pdo->prepare('UPDATE utilisateur SET reset_code = ?, reset_code_expiry = ? WHERE email = ?');
        $stmt->execute([$code, $expiry, $email]);

        // Envoie l'email
        mail($email, 'Réinitialisation de votre mot de passe', "Votre code de réinitialisation est : $code");
        echo 'Un email de réinitialisation a été envoyé.';
    } else {
        echo 'Email non trouvé.';
    }
}
?>
