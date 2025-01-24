<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

session_start();

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance()->getConnection();

// Récupération des statistiques
try {
    // Nombre total d'utilisateurs
    $stmt = $db->query('SELECT COUNT(*) FROM utilisateur');
    $totalUsers = $stmt->fetchColumn();

    // Nombre total d'événements
    $stmt = $db->query('SELECT COUNT(*) FROM evenement');
    $totalEvents = $stmt->fetchColumn();

    // Événements du jour
    $stmt = $db->query('SELECT COUNT(*) FROM evenement WHERE DATE(date) = CURDATE()');
    $todayEvents = $stmt->fetchColumn();

    // Derniers utilisateurs inscrits
    $stmt = $db->query('SELECT * FROM utilisateur ORDER BY date_inscription DESC LIMIT 5');
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Derniers événements créés
    $stmt = $db->query('SELECT e.*, u.prenom, u.nom FROM evenement e 
                       JOIN utilisateur u ON e.organisateur_id = u.id 
                       ORDER BY e.date DESC LIMIT 5');
    $recentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Erreur dashboard : " . $e->getMessage());
    $error = "Une erreur est survenue lors du chargement des données.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Tableau de bord</title>
    <link rel="stylesheet" href="./assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>EventAccess</h2>
                <p>Administration</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="events.php">
                    <i class="fas fa-calendar"></i> Événements
                </a>
                <a href="admins.php">
                    <i class="fas fa-user-shield"></i> Administrateurs
                </a>
            </nav>

            <!-- Profil utilisateur -->
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($_SESSION['admin_nom']); ?></p>
                        <p class="user-role">Administrateur</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="content-header">
                <h1>Tableau de bord</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_nom']); ?></p>
            </header>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users icon"></i>
                    <h3>Utilisateurs totaux</h3>
                    <div class="value"><?php echo $totalUsers; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-calendar icon"></i>
                    <h3>Événements totaux</h3>
                    <div class="value"><?php echo $totalEvents; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-calendar-day icon"></i>
                    <h3>Événements aujourd'hui</h3>
                    <div class="value"><?php echo $todayEvents; ?></div>
                </div>
            </div>

            <!-- Derniers utilisateurs -->
            <section class="dashboard-section">
                <h2>Derniers utilisateurs inscrits</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                    <td>
                                        <a href="users.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Derniers événements -->
            <section class="dashboard-section">
                <h2>Derniers événements</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Organisateur</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvents as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($event['prenom'] . ' ' . $event['nom']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($event['date'])); ?></td>
                                    <td>
                                        <a href="events.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html> 