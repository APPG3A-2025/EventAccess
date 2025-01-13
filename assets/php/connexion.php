
<?php

session_start();

try {
    $bdd = new PDO('mysql:host=localhost;dbname=event_access;charset=utf8', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Connexion à la base de données réussie");
} catch(Exception $e) {
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
    ]));
}
// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vérifier que les champs ne sont pas vides
    if (empty($email) || empty($password)) {
        echo "Veuillez remplir tous les champs.";
        exit;
    }

    // Rechercher l'utilisateur par email
    $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if ($user) {
        // Vérifier le mot de passe
        if (password_verify($password, $user['mot_de_passe'])) {
            echo "Connexion réussie ! Bienvenue, " . htmlspecialchars($user['email']) . ".";
            $_SESSION['user_id'] = $user['id'];        // Stocke l'ID de l'utilisateur
            $_SESSION['user_email'] = $user['email'];  // Stocke l'email de l'utilisateur
            $_SESSION['is_logged_in'] = true;          // Indique que l'utilisateur est connecté
        
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Aucun utilisateur trouvé avec cet email.";
    }
} else {
    echo "Méthode de requête non autorisée.";
}

?>