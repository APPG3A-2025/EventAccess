<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM administrateurs WHERE email = ?');
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['mot_de_passe'])) {
                // Démarrer la session
                session_start();
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_nom'] = $admin['nom'];
                $_SESSION['last_activity'] = time();
                
                // Mettre à jour la date de dernière connexion
                $updateStmt = $this->db->prepare('
                    UPDATE administrateurs 
                    SET derniere_connexion = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ');
                $updateStmt->execute([$admin['id']]);
                
                return true;
            }
            return false;
            
        } catch(PDOException $e) {
            error_log("Erreur d'authentification : " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }

        // Vérifier si la session n'a pas expiré
        if (time() - $_SESSION['last_activity'] > SESSION_DURATION) {
            $this->logout();
            return false;
        }

        // Mettre à jour le timestamp de dernière activité
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout() {
        session_start();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
    }

    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . LOGIN_URL);
            exit();
        }
    }
} 