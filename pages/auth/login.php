<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventAccess - Connexion</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/pages/login.css">
</head>
<body>
    <?php
    session_start();
    ?>
    <nav class="vintage-nav">
        <div class="nav-brand">EventAccess</div>
        <div class="nav-links">
            <a href="../../index.html">Découvrir</a>
            <a href="../../pages/create.php">Organiser</a>
            <a href="../../pages/faq.html">FAQ</a>
        </div>
        <div class="nav-auth">
            <a href="../auth/login.php" class="auth-button login active">Connexion</a>
            <a href="../auth/register.html" class="auth-button signup">Inscription</a>
        </div>
        <div class="menu-icon">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <div class="menu-overlay"></div>

    <section class="auth-section">
        <div class="auth-container login-container">
            <h1>Connexion</h1>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo '<p style="color: red;">'.$_SESSION['error'].'</p>';
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" action="../../assets/php/auth/login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>
                    <a href="forgot-password.html" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="auth-button submit-btn">Se connecter</button>
            </form>

            <p class="auth-switch">
                Pas encore de compte ? <a href="register.html">S'inscrire</a>
            </p>
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
                    <a href="../legal/terms.html">CGU</a>
                    <a href="../legal/cgv.html">CGV</a>
                    <a href="../legal/privacy.html">Politique de confidentialité</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 EventAccess - Tous droits réservés</p>
        </div>
    </footer>

    <script src="../../assets/js/main.js"></script>
</body>
</html> 