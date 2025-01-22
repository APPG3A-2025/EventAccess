<?php
class Auth {
    private $bdd;

    public function __construct($bdd) {
        $this->bdd = $bdd;
    }

    // Vérifier si un token est valide et retourner les données utilisateur
    public function verifyTokenAndGetUser($token) {
        if (empty($token)) {
            return false;
        }

        try {
            // Récupérer toutes les informations nécessaires de l'utilisateur
            $stmt = $this->bdd->prepare('
                SELECT id, email, prenom, nom, type_compte, civilite, 
                       date_naissance, telephone, code_postal 
                FROM utilisateur 
                WHERE token = ?
            ');
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            // Formater la date si elle existe
            if ($user['date_naissance']) {
                $date = new DateTime($user['date_naissance']);
                $user['date_naissance'] = $date->format('d/m/Y');
            }

            return $user;
        } catch (Exception $e) {
            error_log("Erreur de vérification du token : " . $e->getMessage());
            return false;
        }
    }

    // Générer un nouveau token JWT
    public function generateToken($userId) {
        $token = bin2hex(random_bytes(32));
        
        try {
            $stmt = $this->bdd->prepare('UPDATE utilisateur SET token = ? WHERE id = ?');
            if ($stmt->execute([$token, $userId])) {
                return $token;
            }
        } catch (Exception $e) {
            error_log("Erreur de génération du token : " . $e->getMessage());
        }
        return false;
    }

    // Invalider un token (déconnexion)
    public function invalidateToken($token) {
        $stmt = $this->bdd->prepare('UPDATE utilisateur SET token = NULL WHERE token = ?');
        return $stmt->execute([$token]);
    }
}
?> 