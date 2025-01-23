<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organisateur') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventAccess - Tableau de bord Organisateur</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/pages/home_organisateur.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-section">
                <div class="profile-image">
                    <img src="../../assets/images/default-avatar.png" 
                         alt="Profile" 
                         onerror="this.src='../../assets/images/default-profile.png'">
                </div>
                <h3><?php echo htmlspecialchars($_SESSION['user']['prenom']); ?></h3>
                <p>Organisateur</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-section="dashboard"><i class="fas fa-home"></i> Tableau de bord</a>
                <a href="#stats" class="nav-item" data-section="stats"><i class="fas fa-chart-bar"></i> Statistiques</a>
                <a href="../../assets/php/auth/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Section Dashboard -->
            <div id="dashboard-section">
                <div class="welcome-section">
                    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user']['prenom']); ?> !</h1>
                    <p>Gérez vos événements et suivez vos statistiques</p>
                </div>

                <!-- Quick Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-calendar-check"></i>
                        <h3>Événements actifs</h3>
                        <p id="active-events">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Total participants</h3>
                        <p id="total-participants">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3>Prochain événement</h3>
                        <p id="next-event">Chargement...</p>
                    </div>
                </div>

                <!-- Events List -->
                <section class="events-section">
                    <div class="section-header">
                        <h2>Mes événements</h2>
                        <button class="create-event-btn" onclick="showCreateEventForm()">
                            <i class="fas fa-plus"></i> Nouvel événement
                        </button>
                    </div>
                    <div class="events-grid" id="events-list">
                        <!-- Les événements seront chargés dynamiquement ici -->
                    </div>
                </section>
            </div>

            <!-- Section Statistiques Détaillées -->
            <div id="stats-section" style="display: none;">
                <h2>Statistiques détaillées</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Total des événements</h3>
                        <p id="total-events">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3>Événements dernière semaine</h3>
                        <p id="last-week-events">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-calendar-week"></i>
                        <h3>Événements dernier mois</h3>
                        <p id="last-month-events">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-forward"></i>
                        <h3>Événements semaine prochaine</h3>
                        <p id="next-week-events">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-euro-sign"></i>
                        <h3>Prix moyen des événements</h3>
                        <p id="avg-price">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Total des participants</h3>
                        <p id="total-participants-stats">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-friends"></i>
                        <h3>Moyenne participants/événement</h3>
                        <p id="avg-participants">Chargement...</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-clock"></i>
                        <h3>Âge moyen des participants</h3>
                        <p id="avg-age">Chargement...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour créer/modifier un événement -->
    <div id="eventModal" class="modal">
        <!-- Le contenu du modal sera chargé dynamiquement -->
    </div>

    <script src="../../assets/js/organisateur/dashboard.js"></script>
</body>
</html> 