<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>EventAccess - Résultats de recherche</title>
		<link rel="stylesheet" href="../../assets/css/styles.css" />
		<link rel="stylesheet" href="../../assets/css/pages/search-results.css" />
	</head>
	<body>
    <?php 
        require_once './connexion.php';

        $recherche = isset($_GET['query']) ? trim($_GET['query']) : '';
        $filtre_date = isset($_GET['date']) ? $_GET['date'] : '';
        $filtre_categories = isset($_GET['categorie']) ? (array)$_GET['categorie'] : [];

        $categories = ['concert', 'theatre', 'festival', 'sport', 'comedy', 'nightlife'];

       $villes = [
        'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 
        'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille', 'Rennes',
        'Reims', 'Toulon', 'Grenoble', 'Dijon'
        ];


        $sql = "SELECT * FROM evenement WHERE 1=1";
        $params = [];

        // Recherche principale
        if (!empty($recherche)) {
            if (in_array(strtolower($recherche), $categories)) {
                $sql .= " AND LOWER(categorie) = ?";
                $params[] = strtolower($recherche);
            } elseif (in_array(ucfirst(strtolower($recherche)), $villes)) {
                $sql .= " AND LOWER(ville) = ?";
                $params[] = strtolower($recherche);
            } else {
                $sql .= " AND (SOUNDEX(nom) = SOUNDEX(?) OR nom LIKE ?)";
                $params[] = $recherche;
                $params[] = "%$recherche%";
            }
        }

        // Filtre par date
        if (!empty($filtre_date)) {
            switch($filtre_date) {
                case 'today':
                    $sql .= " AND DATE(date) = CURDATE()";
                    break;
                case 'week':
                    $sql .= " AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'month':
                    $sql .= " AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
                    break;
            }
        }

        // Filtre par catégories
        if (!empty($filtre_categories)) {
            $placeholders = str_repeat('?,', count($filtre_categories) - 1) . '?';
            $sql .= " AND LOWER(categorie) IN ($placeholders)";
            foreach ($filtre_categories as $cat) {
                $params[] = strtolower($cat);
            }
        }

        try {
            $stmt = $bdd->prepare($sql);
            $stmt->execute($params);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            $error = $e->getMessage();
        }
?>

		<nav class="vintage-nav">
			<div class="nav-brand">EventAccess</div>
			<div class="nav-links">
				<a href="../../index.html">Découvrir</a>
				<a href="../../pages/create.php">Organiser</a>
				<a href="../../pages/faq.html">Contact</a>
			</div>
			<div class="nav-auth">
				<a href="auth/login.php" class="auth-button login">Connexion</a>
				<a href="auth/register.html" class="auth-button signup">Inscription</a>
			</div>
			<div class="menu-icon">
				<span></span>
				<span></span>
				<span></span>
			</div>
		</nav>

		<div class="menu-overlay"></div>

		<section class="search-results-section">
			<div class="search-header">
				<h1>Résultats de recherche</h1>
				<div class="search-controls">
					<div class="search-bar">
						<form method="GET" action="search-results.php">
							<div class="search-inputs">
								<div class="search-input-group">
									<input
										type="text"
										name="query"
										value="<?php echo htmlspecialchars($recherche); ?>"
										placeholder="Concert, théâtre, festival..."
										required
									/>
								</div>
							</div>
							<button type="submit">Rechercher</button>
						</form>
					</div>
				</div>
				<p class="search-info">
					Résultats pour :
					<span><?php echo htmlspecialchars($recherche); ?></span>
				</p>
			</div>

			<div class="results-container">
				<div class="filters">
					<h3>Filtres</h3>
					<form method="GET" action="search-results.php">
						<input type="hidden" name="query" value="<?php echo htmlspecialchars($recherche); ?>">
						<div class="filter-group">
							<h4>Date</h4>
							<label><input type="radio" name="date" value="all" <?php echo $filtre_date == 'all' ? 'checked' : ''; ?>> Toutes les dates</label>
							<label><input type="radio" name="date" value="today" <?php echo $filtre_date == 'today' ? 'checked' : ''; ?>> Aujourd'hui</label>
							<label><input type="radio" name="date" value="week" <?php echo $filtre_date == 'week' ? 'checked' : ''; ?>> Cette semaine</label>
							<label><input type="radio" name="date" value="month" <?php echo $filtre_date == 'month' ? 'checked' : ''; ?>> Ce mois</label>
						</div>
						<div class="filter-group">
							<h4>Catégorie</h4>
							<?php foreach ($categories as $cat): ?>
							<label>
								<input type="checkbox" name="categorie[]" value="<?php echo $cat; ?>" 
									<?php echo in_array($cat, $filtre_categories) ? 'checked' : ''; ?>>
								<?php echo ucfirst($cat); ?>
							</label>
							<?php endforeach; ?>
						</div>
						<button type="submit">Appliquer les filtres</button>
					</form>
				</div>

				<div class="results-grid">
					<?php if (isset($error)): ?>
					<p class="error-message">
						Une erreur est survenue :
						<?php echo htmlspecialchars($error); ?>
					</p>
					<?php elseif (empty($resultats)): ?>
					<p class="no-results">Aucun résultat trouvé.</p>
					<?php else: 
                    foreach ($resultats as $event): 
                        $image_path = !empty($event['image']) && file_exists("../.../" . $event['image']) 
                            ? "../" . $event['image'] 
                            : "../images/default-event.jpg";
                ?>
					<div class="result-card">
						<img
							src="<?php echo htmlspecialchars($image_path); ?>"
							alt="<?php echo htmlspecialchars($event['nom']); ?>"
							class="result-image"
						/>
						<div class="result-content">
							<div class="result-date">
								<?php echo date('d F Y H:i', strtotime($event['date'])); ?>
							</div>
							<h3 class="result-title">
								<?php echo htmlspecialchars($event['nom']); ?>
							</h3>
                            <div class="result-category">
                                <h3>
                                <?php echo htmlspecialchars($event['categorie']); ?>
                                </h3>
                            </div>
							<div class="result-location">
								<?php echo htmlspecialchars($event['ville']); ?>
							</div>
							<div class="result-price">
								<?php echo htmlspecialchars($event['prix']); ?>€
							</div>
						</div>
					</div>
					<?php 
                    endforeach;
                endif; 
                ?>
				</div>
			</div>
		</section>

	<footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>EventAccess est votre plateforme de gestion d'événements</p>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: contact@eventaccess.com</p>
                <p>Tél: +33 1 23 45 67 89</p>
            </div>
            <div class="footer-section">
                <h3>Suivez-nous</h3>
                <div class="social-links">
                    <a href="#">Facebook</a>
                    <a href="#">Twitter</a>
                    <a href="#">Instagram</a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Mentions légales</h3>
                <div class="legal-links">
                    <a href="legal/terms.html">CGU</a>
                    <a href="legal/cgv.html">CGV</a>
                    <a href="legal/privacy.html">Politique de confidentialité</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 EventAccess - Tous droits réservés</p>
        </div>
    </footer>

    <script src="../assets/js/utils/cityAutocomplete.js"></script>
    <script src="../assets/js/search.js"></script>
	</body>
</html>
