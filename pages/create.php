<?php
session_start();
require_once '../assets/php/middleware/check_organisateur.php';
checkOrganisateur();
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>EventAccess - Créer un événement</title>
		<link rel="stylesheet" href="../assets/css/styles.css" />
		<link rel="stylesheet" href="../assets/css/pages/create.css" />
	</head>
	<body>
		<nav class="vintage-nav">
			<div class="nav-brand">EventAccess</div>
			<div class="nav-links">
				<a href="../index.html">Découvrir</a>
				<a href="create.php" class="active">Organiser</a>
				<a href="faq.html">FAQ</a>
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

		<section class="create-event-section">
			<div class="container">
				<h1>Organiser un événement</h1>
				<form id="create-event-form" class="create-event-form" method="POST">
					<!-- Informations de base -->
					<div class="form-group">
						<label for="event-name">Nom de l'événement *</label>
						<input type="text" id="event-name" name="name" required />
					</div>

					<div class="form-group">
						<label for="event-category">Catégorie *</label>
						<select id="event-category" name="category" required>
							<option value="">Sélectionnez une catégorie</option>
							<option value="concert">Concert</option>
							<option value="theatre">Théâtre</option>
							<option value="festival">Festival</option>
							<option value="sport">Sport</option>
							<option value="comedy">Comedy Show</option>
							<option value="nightlife">Vie Nocturne</option>
						</select>
					</div>

					<!-- Date et heure -->
					<div class="form-row">
						<div class="form-group">
							<label for="event-date">Date *</label>
							<input
								type="date"
								id="event-date"
								name="date"
								required
								min="<?php echo date('Y-m-d'); ?>"
							/>
						</div>
						<div class="form-group">
							<label for="event-time">Heure *</label>
							<input type="time" id="event-time" name="time" required />
						</div>
					</div>

					<!-- Lieu -->
					<div class="location-section">
						<h3>Lieu de l'événement</h3>
						<div class="form-row">
							<div class="form-group">
								<label for="event-city">Ville *</label>
								<div class="city-search-container">
									<input
										type="text"
										id="event-city"
										name="city"
										autocomplete="off"
										required
									/>
									<div id="city-suggestions" class="city-suggestions"></div>
								</div>
							</div>
							<div class="form-group">
								<label for="event-postal">Code postal *</label>
								<input
									type="text"
									id="event-postal"
									name="postal"
									pattern="[0-9]{5}"
									required
								/>
							</div>
						</div>
						<div class="form-group">
							<label for="event-address">Adresse *</label>
							<input type="text" id="event-address" name="address" required />
						</div>
					</div>

					<!-- Prix -->
					<div class="form-group">
						<label for="event-price">Prix (€) *</label>
						<input
							type="number"
							id="event-price"
							name="price"
							min="0"
							step="0.01"
							placeholder="0 pour gratuit"
							required
						/>
					</div>

					<!-- Description -->
					<div class="form-group">
						<label for="event-description">Description *</label>
						<textarea
							id="event-description"
							name="description"
							rows="5"
							required
						></textarea>
					</div>

					<!-- Images -->
					<div class="form-group">
						<label for="event-image">Image</label>
						<input type="file" id="event-image" name="image" accept="image/*" />
						<div class="image-preview" id="image-preview"></div>
					</div>

					<button type="submit" class="submit-button">Créer l'événement</button>
				</form>
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

		<script src="../assets/js/main.js"></script>
		<script src="../assets/js/utils/cityAutocomplete.js"></script>
		<script src="../assets/js/create-event.js"></script>
	</body>
</html>
