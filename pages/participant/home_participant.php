<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'utilisateur') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventAccess - Espace Participant</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/pages/home_participant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>EventAccess</h2>
                <p>Espace Participant</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-section="dashboard">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="#my-events" class="nav-item" data-section="my-events">
                    <i class="fas fa-calendar-check"></i> Mes événements
                </a>
                <a href="#search" class="nav-item" data-section="search">
                    <i class="fas fa-search"></i> Rechercher
                </a>
            </nav>

            <!-- Profil utilisateur -->
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($_SESSION['user']['prenom']); ?></p>
                        <p class="user-role">Participant</p>
                    </div>
                </div>
                <a href="../../assets/php/auth/logout.php" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard-section">
                <div class="welcome-section">
                    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user']['prenom']); ?> !</h1>
                    <p>Découvrez les événements qui vous correspondent</p>
                </div>

                <!-- Événements recommandés -->
                <section class="recommended-events">
                    <h2>Recommandés pour vous</h2>
                    <div class="events-grid" id="recommended-events">
                        <!-- Chargé dynamiquement -->
                    </div>
                </section>

                <!-- Événements du jour -->
                <section class="today-events">
                    <h2>Vos événements aujourd'hui</h2>
                    <div class="events-grid" id="today-events">
                        <!-- Chargé dynamiquement -->
                    </div>
                </section>
            </div>

            <!-- My Events Section -->
            <div id="my-events-section" style="display: none;">
                <div class="welcome-section">
                    <h1>Mes événements</h1>
                    <p>Retrouvez tous les événements auxquels vous êtes inscrit</p>
                </div>
                <div class="events-grid" id="my-events-list">
                    <!-- Chargé dynamiquement -->
                </div>
            </div>

            <!-- Search Section -->
            <div id="search-section" style="display: none;">
                <div class="welcome-section">
                    <h1>Rechercher un événement</h1>
                    <p>Trouvez les événements qui vous intéressent</p>
                </div>
                
                <div class="search-filters">
                    <form id="search-form">
                        <div class="filter-group">
                            <input 
                                type="text" 
                                id="search-input" 
                                placeholder="Rechercher un événement..."
                                class="search-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <select id="category-filter" class="filter-select">
                                <option value="">Toutes les catégories</option>
                                <option value="concert">Concert</option>
                                <option value="theatre">Théâtre</option>
                                <option value="sport">Sport</option>
                                <option value="festival">Festival</option>
                                <option value="conference">Conférence</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <input 
                                type="date" 
                                id="date-filter" 
                                class="filter-date"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <input 
                                type="text" 
                                id="city-filter" 
                                placeholder="Code postal"
                                pattern="[0-9]{5}"
                                maxlength="5"
                                class="filter-input"
                            >
                        </div>
                        
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </form>
                </div>

                <div class="events-grid" id="search-results">
                    <!-- Les résultats seront affichés ici -->
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/participant/dashboard.js"></script>
</body>
</html> 