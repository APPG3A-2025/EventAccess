<nav class="vintage-nav">
    <div class="nav-brand">EventAccess</div>
    <div class="nav-links">
        <a href="../../index.html">Découvrir</a>
        <a href="../../pages/create.php">Organiser</a>
        <a href="../../pages/faq.html">FAQ</a>
    </div>
    <div class="nav-auth">
        <?php if (isset($_SESSION['user'])): ?>
            <?php if ($_SESSION['user']['role'] === 'utilisateur'): ?>
                <a href="../../pages/participant/home_participant.php" class="auth-button">Mon Espace</a>
            <?php elseif ($_SESSION['user']['role'] === 'organisateur'): ?>
                <a href="../../pages/organisateur/home_organisateur.php" class="auth-button">Mon Espace</a>
            <?php endif; ?>
            <a href="../../assets/php/auth/logout.php" class="auth-button">Déconnexion</a>
        <?php else: ?>
            <a href="../../pages/auth/login.php" class="auth-button login">Connexion</a>
            <a href="../../pages/auth/register.html" class="auth-button signup">Inscription</a>
        <?php endif; ?>
    </div>
    <div class="menu-icon">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<div class="menu-overlay"></div> 