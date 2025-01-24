<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'event_access');

// Configuration de la session
define('SESSION_DURATION', 3600); // 1 heure en secondes
define('SESSION_NAME', 'admin_session');

// URLs de base
define('BASE_URL', '/admin');
define('LOGIN_URL', 'login.php');

// Messages d'erreur
define('ERROR_NOT_AUTHORIZED', 'Vous n\'êtes pas autorisé à accéder à cette page.');
define('ERROR_INVALID_CREDENTIALS', 'Identifiants invalides.'); 