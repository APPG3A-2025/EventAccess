document.addEventListener('DOMContentLoaded', () => {
    // Menu mobile
    const menuIcon = document.querySelector('.menu-icon');
    const menuOverlay = document.querySelector('.menu-overlay');
    const nav = document.querySelector('.vintage-nav');

    if (menuIcon && menuOverlay && nav) {
        menuIcon.addEventListener('click', () => {
            nav.classList.toggle('menu-open');
            menuOverlay.classList.toggle('active');
        });

        menuOverlay.addEventListener('click', () => {
            nav.classList.remove('menu-open');
            menuOverlay.classList.remove('active');
        });
    }

    // Mise à jour de l'interface selon le token
    const token = localStorage.getItem('userToken');
    const authButtons = document.querySelector('.nav-auth');

    if (token && authButtons) {
        authButtons.innerHTML = `
            <a href="pages/profile.html" class="auth-button profile">Mon Profil</a>
            <button id="logout-btn" class="auth-button logout">Déconnexion</button>
        `;

        // Ajouter l'événement de déconnexion
        document.getElementById('logout-btn')?.addEventListener('click', () => {
            localStorage.removeItem('userToken');
            window.location.reload();
        });
    } else if (authButtons) {
        authButtons.innerHTML = `
            <a href="../../app/pages/auth/login.html" class="auth-button login">Connexion</a>
            <a href="../../app/pages/auth/register.html" class="auth-button signup">Inscription</a>
        `;
    }
}); 